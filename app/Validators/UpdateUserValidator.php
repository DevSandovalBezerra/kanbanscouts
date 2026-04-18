<?php

declare(strict_types=1);

namespace App\Validators;

final class UpdateUserValidator
{
    private const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    private const PASSWORD_MSG   = 'La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número.';

    public function validate(array $data): array
    {
        $errors = [];

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                $errors['name'] = ['no puede estar vacío'];
            }
        }

        if (array_key_exists('email', $data)) {
            $email = trim((string) $data['email']);
            if ($email === '') {
                $errors['email'] = ['no puede estar vacío'];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['e-mail inválido'];
            }
        }

        if (array_key_exists('password', $data) && $data['password'] !== null && $data['password'] !== '') {
            if (!preg_match(self::PASSWORD_REGEX, (string) $data['password'])) {
                $errors['password'] = [self::PASSWORD_MSG];
            }
        }

        return $errors;
    }
}
