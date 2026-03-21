<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use App\Validators\InviteMemberValidator;
use PHPUnit\Framework\TestCase;

final class InviteMemberValidatorTest extends TestCase
{
    private InviteMemberValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new InviteMemberValidator();
    }

    private function valid(): array
    {
        return ['project_id' => 1, 'user_id' => 5, 'role_in_project' => 'editor'];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Valid payloads
    // ──────────────────────────────────────────────────────────────────────

    public function testValidEditorPayloadProducesNoErrors(): void
    {
        self::assertEmpty($this->validator->validate($this->valid()));
    }

    public function testValidViewerPayloadProducesNoErrors(): void
    {
        $data = $this->valid();
        $data['role_in_project'] = 'viewer';
        self::assertEmpty($this->validator->validate($data));
    }

    // ──────────────────────────────────────────────────────────────────────
    // project_id
    // ──────────────────────────────────────────────────────────────────────

    public function testMissingProjectIdProducesError(): void
    {
        $data = $this->valid();
        unset($data['project_id']);
        self::assertArrayHasKey('project_id', $this->validator->validate($data));
    }

    public function testZeroProjectIdProducesError(): void
    {
        $data = $this->valid();
        $data['project_id'] = 0;
        self::assertArrayHasKey('project_id', $this->validator->validate($data));
    }

    // ──────────────────────────────────────────────────────────────────────
    // user_id
    // ──────────────────────────────────────────────────────────────────────

    public function testMissingUserIdProducesError(): void
    {
        $data = $this->valid();
        unset($data['user_id']);
        self::assertArrayHasKey('user_id', $this->validator->validate($data));
    }

    public function testZeroUserIdProducesError(): void
    {
        $data = $this->valid();
        $data['user_id'] = 0;
        self::assertArrayHasKey('user_id', $this->validator->validate($data));
    }

    // ──────────────────────────────────────────────────────────────────────
    // role_in_project — only 'editor' or 'viewer' allowed (not owner/manager)
    // ──────────────────────────────────────────────────────────────────────

    public function testOwnerRoleIsRejected(): void
    {
        $data = $this->valid();
        $data['role_in_project'] = 'owner';
        self::assertArrayHasKey('role_in_project', $this->validator->validate($data));
    }

    public function testManagerRoleIsRejected(): void
    {
        $data = $this->valid();
        $data['role_in_project'] = 'manager';
        self::assertArrayHasKey('role_in_project', $this->validator->validate($data));
    }

    public function testInvalidRoleStringIsRejected(): void
    {
        $data = $this->valid();
        $data['role_in_project'] = 'superadmin';
        self::assertArrayHasKey('role_in_project', $this->validator->validate($data));
    }

    public function testMissingRoleIsRejected(): void
    {
        $data = $this->valid();
        unset($data['role_in_project']);
        self::assertArrayHasKey('role_in_project', $this->validator->validate($data));
    }
}
