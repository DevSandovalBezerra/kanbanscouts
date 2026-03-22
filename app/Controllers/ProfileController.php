<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\SessionStore;
use PDO;

final class ProfileController
{
    private const AVATAR_DIR = 'avatars';
    private const MAX_SIZE   = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).{8,}$/';

    public function __construct(
        private readonly PDO          $pdo,
        private readonly SessionStore $session,
        private readonly string       $uploadBasePath
    ) {}

    /** POST /api/profile/update */
    public function update(HttpRequest $request): HttpResponse
    {
        $userId    = (int) $this->session->get('user_id');
        $companyId = (int) $this->session->get('company_id');
        if ($userId === 0) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $name  = trim((string) ($data['name']           ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $emailPassword = (string) ($data['email_password'] ?? '');

        $errors = [];
        if ($name === '') $errors['name'] = ['obrigatório'];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['email inválido'];
        }
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        // Fetch current user
        $stmt = $this->pdo->prepare(
            "SELECT name, email, password FROM users WHERE id = ? AND company_id = ? AND status != 'deleted' LIMIT 1"
        );
        $stmt->execute([$userId, $companyId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$current) {
            return $this->apiError(404, 'not_found', 'Usuário não encontrado.', []);
        }

        // If email changed, require current password
        if ($email !== $current['email']) {
            if ($emailPassword === '') {
                return $this->validationError(['email_password' => ['senha atual obrigatória para trocar o email']]);
            }
            if (!password_verify($emailPassword, $current['password'])) {
                return $this->validationError(['email_password' => ['senha incorreta']]);
            }

            // Check email uniqueness within company
            $checkStmt = $this->pdo->prepare(
                "SELECT id FROM users WHERE company_id = ? AND email = ? AND id != ? AND status != 'deleted' LIMIT 1"
            );
            $checkStmt->execute([$companyId, $email, $userId]);
            if ($checkStmt->fetch()) {
                return $this->validationError(['email' => ['já está em uso nesta empresa']]);
            }
        }

        $this->pdo->prepare(
            'UPDATE users SET name = ?, email = ?, updated_at = ? WHERE id = ?'
        )->execute([$name, $email, date('Y-m-d H:i:s'), $userId]);

        return HttpResponse::json(['ok' => true, 'name' => $name, 'email' => $email]);
    }

    /** POST /api/profile/password */
    public function password(HttpRequest $request): HttpResponse
    {
        $userId    = (int) $this->session->get('user_id');
        $companyId = (int) $this->session->get('company_id');
        if ($userId === 0) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        $data = $this->jsonBody($request);
        if ($data === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $currentPassword = (string) ($data['current_password'] ?? '');
        $newPassword     = (string) ($data['new_password']     ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');

        $errors = [];
        if ($currentPassword === '') $errors['current_password'] = ['obrigatório'];
        if ($newPassword === '')     $errors['new_password']     = ['obrigatório'];
        if ($confirmPassword === '') $errors['confirm_password'] = ['obrigatório'];
        if (!empty($errors)) return $this->validationError($errors);

        if ($newPassword !== $confirmPassword) {
            return $this->validationError(['confirm_password' => ['as senhas não coincidem']]);
        }

        if (!preg_match(self::PASSWORD_REGEX, $newPassword)) {
            return $this->validationError([
                'new_password' => ['mínimo 8 caracteres, uma letra maiúscula e um número'],
            ]);
        }

        // Fetch current hash
        $stmt = $this->pdo->prepare(
            "SELECT password FROM users WHERE id = ? AND company_id = ? AND status != 'deleted' LIMIT 1"
        );
        $stmt->execute([$userId, $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return $this->apiError(404, 'not_found', 'Usuário não encontrado.', []);
        }

        if (!password_verify($currentPassword, $row['password'])) {
            return $this->validationError(['current_password' => ['senha incorreta']]);
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->pdo->prepare(
            'UPDATE users SET password = ?, updated_at = ? WHERE id = ?'
        )->execute([$newHash, date('Y-m-d H:i:s'), $userId]);

        return HttpResponse::json(['ok' => true]);
    }

    /** POST /api/profile/avatar  (multipart/form-data, field "avatar") */
    public function avatar(HttpRequest $request): HttpResponse
    {
        $userId    = (int) $this->session->get('user_id');
        $companyId = (int) $this->session->get('company_id');
        if ($userId === 0) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        if (empty($_FILES['avatar'])) {
            return $this->validationError(['avatar' => ['nenhum arquivo enviado']]);
        }

        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->validationError(['avatar' => ['erro no upload do arquivo']]);
        }

        if ($file['size'] > self::MAX_SIZE) {
            return $this->validationError(['avatar' => ['arquivo deve ter no máximo 2MB']]);
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return $this->validationError(['avatar' => ['apenas JPG, PNG ou WebP são permitidos']]);
        }

        $ext      = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $avatarDir = $this->uploadBasePath . DIRECTORY_SEPARATOR . self::AVATAR_DIR;
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }

        // Delete old avatar if exists
        $stmtOld = $this->pdo->prepare(
            "SELECT avatar FROM users WHERE id = ? AND company_id = ? LIMIT 1"
        );
        $stmtOld->execute([$userId, $companyId]);
        $oldAvatar = (string) ($stmtOld->fetchColumn() ?: '');
        if ($oldAvatar !== '') {
            $oldPath = $this->uploadBasePath . DIRECTORY_SEPARATOR . self::AVATAR_DIR . DIRECTORY_SEPARATOR . basename($oldAvatar);
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $filename = sprintf('avatar_%d_%d.%s', $userId, time(), $ext);
        $dest     = $avatarDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return $this->apiError(500, 'upload_error', 'Falha ao salvar o arquivo.', []);
        }

        $relativePath = '/uploads/' . self::AVATAR_DIR . '/' . $filename;

        $this->pdo->prepare(
            'UPDATE users SET avatar = ?, updated_at = ? WHERE id = ?'
        )->execute([$relativePath, date('Y-m-d H:i:s'), $userId]);

        return HttpResponse::json(['ok' => true, 'avatar_url' => $relativePath], 201);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function jsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') return [];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function validationError(array $details): HttpResponse
    {
        return $this->apiError(422, 'validation_error', 'Payload inválido.', $details);
    }

    private function apiError(int $status, string $code, string $message, array $details): HttpResponse
    {
        return HttpResponse::json([
            'error' => ['code' => $code, 'message' => $message, 'details' => $details],
        ], $status);
    }
}
