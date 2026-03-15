<?php

declare(strict_types=1);

namespace App\Services;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly SessionStore $session
    ) {
    }

    public function login(string $email, string $password): AuthServiceResult
    {
        $email = strtolower(trim($email));
        $records = $this->users->findActiveByEmail($email);

        if (count($records) === 0) {
            return new AuthServiceResult(AuthServiceResult::INVALID_CREDENTIALS);
        }

        if (count($records) > 1) {
            return new AuthServiceResult(AuthServiceResult::AMBIGUOUS_IDENTITY);
        }

        $record = $records[0];
        if (!password_verify($password, $record->passwordHash)) {
            return new AuthServiceResult(AuthServiceResult::INVALID_CREDENTIALS);
        }

        $this->session->regenerate();
        $this->session->set('user_id', $record->id);
        $this->session->set('company_id', $record->companyId);

        return new AuthServiceResult(AuthServiceResult::OK);
    }

    public function logout(): void
    {
        $this->session->destroy();
    }
}
