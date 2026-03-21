<?php

declare(strict_types=1);

namespace Tests\Integration;

/**
 * Base for integration tests that exercise admin and project-member features.
 * Extends IntegrationTestCase and additionally clears admin_audit_log and project_members.
 */
abstract class AdminIntegrationTestCase extends IntegrationTestCase
{
    /** Inserted in setUp() and re-used across tests */
    protected int $companyId;
    protected int $adminUserId;
    protected int $regularUserId;
    protected int $projectId;

    protected function setUp(): void
    {
        parent::setUp(); // truncates base tables

        // Truncate new tables added by migration 009
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->pdo->exec('TRUNCATE TABLE admin_audit_log');
        $this->pdo->exec('TRUNCATE TABLE project_members');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->seedBaseData();
    }

    private function seedBaseData(): void
    {
        $now = '2026-01-01 00:00:00';

        $this->pdo->exec(
            "INSERT INTO companies (name, status, created_at, updated_at)
             VALUES ('Test Corp', 'active', '$now', '$now')"
        );
        $this->companyId = (int) $this->pdo->lastInsertId();

        // Admin user
        $this->pdo->prepare(
            "INSERT INTO users (company_id, name, email, password, status, is_admin, created_at, updated_at)
             VALUES (?, 'Admin User', 'admin@test.com', ?, 'active', 1, '$now', '$now')"
        )->execute([$this->companyId, password_hash('Admin@123', PASSWORD_BCRYPT)]);
        $this->adminUserId = (int) $this->pdo->lastInsertId();

        // Regular (non-admin) user
        $this->pdo->prepare(
            "INSERT INTO users (company_id, name, email, password, status, is_admin, created_at, updated_at)
             VALUES (?, 'Regular User', 'regular@test.com', ?, 'active', 0, '$now', '$now')"
        )->execute([$this->companyId, password_hash('User@123', PASSWORD_BCRYPT)]);
        $this->regularUserId = (int) $this->pdo->lastInsertId();

        // Project
        $this->pdo->prepare(
            "INSERT INTO projects (company_id, name, description, created_by, created_at, updated_at)
             VALUES (?, 'Test Project', 'Desc', ?, '$now', '$now')"
        )->execute([$this->companyId, $this->adminUserId]);
        $this->projectId = (int) $this->pdo->lastInsertId();
    }
}
