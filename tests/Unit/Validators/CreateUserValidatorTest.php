<?php

declare(strict_types=1);

namespace Tests\Unit\Validators;

use App\Validators\CreateUserValidator;
use PHPUnit\Framework\TestCase;

final class CreateUserValidatorTest extends TestCase
{
    private CreateUserValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CreateUserValidator();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Valid data
    // ──────────────────────────────────────────────────────────────────────

    public function testValidDataProducesNoErrors(): void
    {
        $errors = $this->validator->validate([
            'name'     => 'João Silva',
            'email'    => 'joao@empresa.com',
            'password' => 'Senha@123',
        ]);

        self::assertEmpty($errors);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Name validation
    // ──────────────────────────────────────────────────────────────────────

    public function testMissingNameProducesError(): void
    {
        $errors = $this->validator->validate(['email' => 'a@b.com', 'password' => 'Senha@123']);
        self::assertArrayHasKey('name', $errors);
    }

    public function testEmptyNameProducesError(): void
    {
        $errors = $this->validator->validate(['name' => '  ', 'email' => 'a@b.com', 'password' => 'Senha@123']);
        self::assertArrayHasKey('name', $errors);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Email validation
    // ──────────────────────────────────────────────────────────────────────

    public function testMissingEmailProducesError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'password' => 'Senha@123']);
        self::assertArrayHasKey('email', $errors);
    }

    public function testInvalidEmailFormatProducesError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'not-an-email', 'password' => 'Senha@123']);
        self::assertArrayHasKey('email', $errors);
    }

    public function testValidEmailProducesNoEmailError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'foo@bar.com', 'password' => 'Senha@123']);
        self::assertArrayNotHasKey('email', $errors);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Password policy (S06)
    // ──────────────────────────────────────────────────────────────────────

    public function testMissingPasswordProducesError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'a@b.com']);
        self::assertArrayHasKey('password', $errors);
    }

    public function testPasswordTooShortProducesError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'a@b.com', 'password' => 'Ab1']);
        self::assertArrayHasKey('password', $errors);
    }

    public function testPasswordWithoutUppercaseProducesError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'a@b.com', 'password' => 'senha123']);
        self::assertArrayHasKey('password', $errors);
    }

    public function testPasswordWithoutDigitProducesError(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'a@b.com', 'password' => 'SenhaForte']);
        self::assertArrayHasKey('password', $errors);
    }

    public function testValidPasswordMinimumRequirementsProducesNoError(): void
    {
        // Exactly 8 chars, 1 uppercase, 1 digit
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'a@b.com', 'password' => 'Abcdef1!']);
        self::assertArrayNotHasKey('password', $errors);
    }

    public function testPasswordErrorMessageMatchesSpec(): void
    {
        $errors = $this->validator->validate(['name' => 'Foo', 'email' => 'a@b.com', 'password' => 'fraco']);
        self::assertStringContainsString('8 caracteres', $errors['password'][0]);
    }
}
