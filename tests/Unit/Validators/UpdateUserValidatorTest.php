<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use App\Validators\UpdateUserValidator;
use PHPUnit\Framework\TestCase;

final class UpdateUserValidatorTest extends TestCase
{
    private UpdateUserValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new UpdateUserValidator();
    }

    public function testEmptyPayloadIsValid(): void
    {
        self::assertEmpty($this->validator->validate([]));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Name
    // ──────────────────────────────────────────────────────────────────────

    public function testValidNameProducesNoError(): void
    {
        $errors = $this->validator->validate(['name' => 'Maria']);
        self::assertArrayNotHasKey('name', $errors);
    }

    public function testEmptyNameProducesError(): void
    {
        $errors = $this->validator->validate(['name' => '']);
        self::assertArrayHasKey('name', $errors);
    }

    public function testWhitespaceOnlyNameProducesError(): void
    {
        $errors = $this->validator->validate(['name' => '   ']);
        self::assertArrayHasKey('name', $errors);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Email
    // ──────────────────────────────────────────────────────────────────────

    public function testValidEmailProducesNoError(): void
    {
        $errors = $this->validator->validate(['email' => 'valid@test.com']);
        self::assertArrayNotHasKey('email', $errors);
    }

    public function testInvalidEmailProducesError(): void
    {
        $errors = $this->validator->validate(['email' => 'not-valid']);
        self::assertArrayHasKey('email', $errors);
    }

    public function testEmptyEmailProducesError(): void
    {
        $errors = $this->validator->validate(['email' => '']);
        self::assertArrayHasKey('email', $errors);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Password (optional field — only validated if present and non-empty)
    // ──────────────────────────────────────────────────────────────────────

    public function testNullPasswordIsIgnored(): void
    {
        $errors = $this->validator->validate(['password' => null]);
        self::assertArrayNotHasKey('password', $errors);
    }

    public function testEmptyStringPasswordIsIgnored(): void
    {
        $errors = $this->validator->validate(['password' => '']);
        self::assertArrayNotHasKey('password', $errors);
    }

    public function testWeakPasswordProducesError(): void
    {
        $errors = $this->validator->validate(['password' => 'fraco']);
        self::assertArrayHasKey('password', $errors);
    }

    public function testStrongPasswordProducesNoError(): void
    {
        $errors = $this->validator->validate(['password' => 'NovaSenha@1']);
        self::assertArrayNotHasKey('password', $errors);
    }
}
