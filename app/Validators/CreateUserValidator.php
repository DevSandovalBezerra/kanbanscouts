<?php

declare(strict_types=1);

namespace App\Validators;

final class CreateUserValidator
{
    private const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';
    private const PASSWORD_MSG   = 'La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un número.';

    public function validate(array $data): array
    {
        $errors = [];

        $name  = trim((string) ($data['name']  ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $pass  = (string) ($data['password'] ?? '');

        if ($name === '') {
            $errors['name'] = ['obrigatório'];
        }

        if ($email === '') {
            $errors['email'] = ['obrigatório'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['correo electrónico inválido'];
        }

        if ($pass === '') {
            $errors['password'] = ['obrigatório'];
        } elseif (!preg_match(self::PASSWORD_REGEX, $pass)) {
            $errors['password'] = [self::PASSWORD_MSG];
        }

        return $errors;
    }
}
