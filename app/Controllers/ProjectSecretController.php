<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\ProjectSecretDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Policies\ProjectPolicy;
use App\Repositories\PdoProjectRepository;
use App\Repositories\ProjectSecretRepository;
use App\Services\SessionStore;

final class ProjectSecretController
{
    public function __construct(
        private readonly ProjectSecretRepository $repo,
        private readonly PdoProjectRepository $projectRepo,
        private readonly ProjectPolicy $policy,
        private readonly SessionStore $session
    ) {}

    /** GET /api/project-secrets?project_id=X */
    public function index(HttpRequest $request): HttpResponse
    {
        $projectId = (int) ($request->query()['project_id'] ?? 0);
        if ($projectId === 0) {
            return $this->validationError(['project_id' => ['obrigatório']]);
        }

        $companyId = (int) ($this->session->get('company_id') ?? 0);
        if (!$this->projectRepo->belongsToCompany($projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Projeto não encontrado.', []);
        }

        if (!$this->policy->canView($projectId)) {
            return $this->apiError(403, 'forbidden', 'Sem acesso a este projeto.', []);
        }

        $appSecret = $this->getAppSecret();
        if ($appSecret === '') {
            return $this->apiError(500, 'server_misconfigured', 'APP_SECRET não configurado.', []);
        }

        $rows = $this->repo->findByProjectId($projectId);
        $result = array_map(function (ProjectSecretDTO $s) use ($appSecret): array {
            return [
                'id' => $s->id,
                'project_id' => $s->projectId,
                'secret_key' => $s->secretKey,
                'title' => $s->title,
                'description' => $s->description,
                'secret_value' => $s->secretValueEnc !== '' ? $this->decryptValue($s->secretValueEnc, $appSecret) : '',
                'created_by' => $s->createdBy,
                'created_at' => $s->createdAt,
                'updated_at' => $s->updatedAt,
            ];
        }, $rows);

        return HttpResponse::json($result);
    }

    /** POST /api/project-secrets */
    public function create(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $projectId = (int) ($payload['project_id'] ?? 0);
        $key = trim((string) ($payload['secret_key'] ?? ''));
        $value = (string) ($payload['secret_value'] ?? '');
        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        $errors = [];
        if ($projectId === 0) $errors['project_id'] = ['obrigatório'];
        if ($key === '') $errors['secret_key'] = ['obrigatório'];
        if ($value === '') $errors['secret_value'] = ['obrigatório'];
        if ($title !== '' && mb_strlen($title) > 190) $errors['title'] = ['máximo 190 caracteres'];
        if (!empty($errors)) return $this->validationError($errors);

        $companyId = (int) ($this->session->get('company_id') ?? 0);
        $userId = (int) ($this->session->get('user_id') ?? 0);
        if ($userId === 0) {
            return $this->apiError(401, 'unauthorized', 'Não autenticado.', []);
        }

        if (!$this->projectRepo->belongsToCompany($projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Projeto não encontrado.', []);
        }

        if (!$this->policy->canWrite($projectId)) {
            return $this->apiError(403, 'forbidden', 'Requer papel editor ou superior.', []);
        }

        $appSecret = $this->getAppSecret();
        if ($appSecret === '') {
            return $this->apiError(500, 'server_misconfigured', 'APP_SECRET não configurado.', []);
        }

        try {
            $enc = $this->encryptValue($value, $appSecret);
            $id = $this->repo->create(new ProjectSecretDTO(
                id: null,
                projectId: $projectId,
                secretKey: $key,
                title: $title !== '' ? $title : null,
                description: $description !== '' ? $description : null,
                secretValueEnc: $enc,
                createdBy: $userId
            ));

            return HttpResponse::json(['ok' => true, 'id' => $id], 201);
        } catch (\PDOException $e) {
            $msg = (str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains($e->getMessage(), 'uq_project_secret_key'))
                ? 'Chave já existe neste projeto.'
                : 'Erro ao salvar.';
            return $this->apiError(422, 'business_rule', $msg, []);
        } catch (\Throwable $e) {
            return $this->apiError(500, 'server_error', 'Erro ao salvar.', []);
        }
    }

    /** PATCH /api/project-secrets?id=X */
    public function update(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);
        if ($id === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return $this->validationError(['body' => ['json inválido']]);
        }

        $key = trim((string) ($payload['secret_key'] ?? ''));
        $value = (string) ($payload['secret_value'] ?? '');
        $title = array_key_exists('title', $payload) ? trim((string) ($payload['title'] ?? '')) : null;
        $description = array_key_exists('description', $payload) ? trim((string) ($payload['description'] ?? '')) : null;

        $errors = [];
        if ($key === '') $errors['secret_key'] = ['obrigatório'];
        if ($value === '') $errors['secret_value'] = ['obrigatório'];
        if (is_string($title) && $title !== '' && mb_strlen($title) > 190) $errors['title'] = ['máximo 190 caracteres'];
        if (!empty($errors)) return $this->validationError($errors);

        $existing = $this->repo->findById($id);
        if (!$existing) {
            return $this->apiError(404, 'not_found', 'Secret não encontrado.', []);
        }

        $companyId = (int) ($this->session->get('company_id') ?? 0);
        if (!$this->projectRepo->belongsToCompany($existing->projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Secret não encontrado.', []);
        }

        if (!$this->policy->canWrite($existing->projectId)) {
            return $this->apiError(403, 'forbidden', 'Requer papel editor ou superior.', []);
        }

        $appSecret = $this->getAppSecret();
        if ($appSecret === '') {
            return $this->apiError(500, 'server_misconfigured', 'APP_SECRET não configurado.', []);
        }

        try {
            $existing->secretKey = $key;
            if (is_string($title)) $existing->title = $title !== '' ? $title : null;
            if (is_string($description)) $existing->description = $description !== '' ? $description : null;
            $existing->secretValueEnc = $this->encryptValue($value, $appSecret);

            $this->repo->update($existing);
            return HttpResponse::json(['ok' => true]);
        } catch (\PDOException $e) {
            $msg = (str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains($e->getMessage(), 'uq_project_secret_key'))
                ? 'Chave já existe neste projeto.'
                : 'Erro ao salvar.';
            return $this->apiError(422, 'business_rule', $msg, []);
        } catch (\Throwable $e) {
            return $this->apiError(500, 'server_error', 'Erro ao salvar.', []);
        }
    }

    /** DELETE /api/project-secrets?id=X */
    public function delete(HttpRequest $request): HttpResponse
    {
        $id = (int) ($request->query()['id'] ?? 0);
        if ($id === 0) {
            return $this->validationError(['id' => ['obrigatório']]);
        }

        $existing = $this->repo->findById($id);
        if (!$existing) {
            return $this->apiError(404, 'not_found', 'Secret não encontrado.', []);
        }

        $companyId = (int) ($this->session->get('company_id') ?? 0);
        if (!$this->projectRepo->belongsToCompany($existing->projectId, $companyId)) {
            return $this->apiError(404, 'not_found', 'Secret não encontrado.', []);
        }

        if (!$this->policy->canWrite($existing->projectId)) {
            return $this->apiError(403, 'forbidden', 'Requer papel editor ou superior.', []);
        }

        $this->repo->delete($id);
        return HttpResponse::json(['ok' => true]);
    }

    private function getAppSecret(): string
    {
        $v = getenv('APP_SECRET');
        if (is_string($v) && trim($v) !== '') return trim($v);

        $v = $_ENV['APP_SECRET'] ?? null;
        if (is_string($v) && trim($v) !== '') return trim($v);

        $v = $_SERVER['APP_SECRET'] ?? null;
        if (is_string($v) && trim($v) !== '') return trim($v);

        return '';
    }

    private function encryptValue(string $plain, string $appSecret): string
    {
        $key = hash('sha256', $appSecret, true);
        $iv  = random_bytes(12);
        $tag = '';
        $ct  = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (!is_string($ct) || $ct === '' || $tag === '') {
            throw new \RuntimeException('encrypt_failed');
        }

        return 'v1:' . base64_encode($iv) . ':' . base64_encode($tag) . ':' . base64_encode($ct);
    }

    private function decryptValue(string $encoded, string $appSecret): string
    {
        if (!str_starts_with($encoded, 'v1:')) {
            return '';
        }

        $parts = explode(':', $encoded, 4);
        if (count($parts) !== 4) {
            return '';
        }

        [, $ivB64, $tagB64, $ctB64] = $parts;
        $iv  = base64_decode($ivB64, true);
        $tag = base64_decode($tagB64, true);
        $ct  = base64_decode($ctB64, true);

        if (!is_string($iv) || !is_string($tag) || !is_string($ct)) {
            return '';
        }

        $key = hash('sha256', $appSecret, true);
        $pt  = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return is_string($pt) ? $pt : '';
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        return json_decode($raw, true);
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
