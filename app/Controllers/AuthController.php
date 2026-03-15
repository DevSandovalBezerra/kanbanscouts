<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Services\AuthService;
use App\Services\AuthServiceResult;
use App\Services\SessionStore;

final class AuthController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly SessionStore $session
    ) {
    }

    public function login(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $email = $payload['email'] ?? null;
        $password = $payload['password'] ?? null;

        $details = [];
        if (!is_string($email) || trim($email) === '') {
            $details['email'] = ['obrigatório'];
        }
        if (!is_string($password) || $password === '') {
            $details['password'] = ['obrigatório'];
        }
        if ($details !== []) {
            return $this->validationError($details);
        }

        $result = $this->auth->login($email, $password);
        if ($result->code === AuthServiceResult::OK) {
            $csrfToken = $this->ensureCsrfToken();
            return HttpResponse::json(['ok' => true, 'csrf_token' => $csrfToken], 200);
        }

        if ($result->code === AuthServiceResult::AMBIGUOUS_IDENTITY) {
            return $this->apiError(422, 'ambiguous_identity', 'Identidade ambígua para este e-mail.', []);
        }

        return $this->apiError(401, 'unauthorized', 'Credenciais inválidas.', []);
    }

    public function logout(HttpRequest $request): HttpResponse
    {
        $this->auth->logout();
        return HttpResponse::json(['ok' => true], 200);
    }

    public function me(HttpRequest $request): HttpResponse
    {
        $userId = $this->sessionInt('user_id');
        $companyId = $this->sessionInt('company_id');

        if ($userId === null || $companyId === null) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        return HttpResponse::json([
            'user' => [
                'id' => $userId,
                'company_id' => $companyId
            ]
        ], 200);
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function sessionInt(string $key): ?int
    {
        $value = $this->session->get($key);
        if ($value === null) {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }
        return null;
    }

    private function ensureCsrfToken(): string
    {
        $existing = $this->session->get('csrf_token');
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    private function validationError(array $details): HttpResponse
    {
        return $this->apiError(422, 'validation_error', 'Payload inválido.', $details);
    }

    private function apiError(int $statusCode, string $code, string $message, array $details): HttpResponse
    {
        return HttpResponse::json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ], $statusCode);
    }
}
