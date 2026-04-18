<?php

declare(strict_types=1);

namespace App\Validators;

final class InviteMemberValidator
{
    private const VALID_ROLES = ['editor', 'viewer'];

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['project_id']) || (int) $data['project_id'] <= 0) {
            $errors['project_id'] = ['obligatorio'];
        }

        if (empty($data['user_id']) || (int) $data['user_id'] <= 0) {
            $errors['user_id'] = ['obligatorio'];
        }

        $role = (string) ($data['role_in_project'] ?? '');
        if (!in_array($role, self::VALID_ROLES, true)) {
            $errors['role_in_project'] = ['deve ser "editor" ou "viewer"'];
        }

        return $errors;
    }
}
