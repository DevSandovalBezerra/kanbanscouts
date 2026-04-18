<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\ColumnDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Policies\ProjectPolicy;
use App\Repositories\ColumnRepository;
use App\Repositories\PdoBoardRepository;
use App\Repositories\PdoProjectRepository;
use App\Services\Column\CreateColumnService;
use App\Services\SessionStore;

final class ColumnController
{
    public function __construct(
        private readonly CreateColumnService $createColumnService,
        private readonly ColumnRepository $columnRepository,
        private readonly ?SessionStore $session = null,
        private readonly ?PdoBoardRepository $boardRepo = null,
        private readonly ?PdoProjectRepository $projectRepo = null,
        private readonly ?ProjectPolicy $policy = null
    ) {
    }

    public function create(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if (empty($payload['board_id']) || empty($payload['name'])) {
            return HttpResponse::json(['error' => 'board_id y name son obligatorios'], 422);
        }

        $boardId = (int) $payload['board_id'];

        // Double guard (S08)
        if ($this->boardRepo !== null && $this->projectRepo !== null && $this->policy !== null && $this->session !== null) {
            $companyId = (int) ($this->session->get('company_id') ?? 0);
            $projectId = $this->boardRepo->resolveProjectId($boardId);

            // Step 1: tenant isolation
            if ($projectId === null || !$this->projectRepo->belongsToCompany($projectId, $companyId)) {
                return HttpResponse::json(['error' => ['code' => 'not_found', 'message' => 'Tablero no encontrado.']], 404);
            }

            // Step 2: role check
            if (!$this->policy->canManageBoard($projectId)) {
                return HttpResponse::json(['error' => ['code' => 'forbidden', 'message' => 'Requiere rol gerente o superior.']], 403);
            }
        }

        $dto = new ColumnDTO(
            boardId: $boardId,
            name: (string) $payload['name'],
            position: (int) ($payload['position'] ?? 1)
        );

        $id = $this->createColumnService->execute($dto);
        return HttpResponse::json(['id' => $id], 201);
    }

    public function reorder(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if (empty($payload['ordered_ids'])) {
            return HttpResponse::json(['error' => 'ordered_ids é obrigatório'], 422);
        }

        // Double guard: resolve project from first column id
        if ($this->columnRepository instanceof \App\Repositories\PdoColumnRepository
            && $this->projectRepo !== null
            && $this->policy !== null
            && $this->session !== null
        ) {
            $firstColumnId = (int) ($payload['ordered_ids'][0] ?? 0);
            if ($firstColumnId > 0) {
                $companyId = (int) ($this->session->get('company_id') ?? 0);
                $projectId = $this->columnRepository->resolveProjectId($firstColumnId);

                if ($projectId === null || !$this->projectRepo->belongsToCompany($projectId, $companyId)) {
                    return HttpResponse::json(['error' => ['code' => 'not_found', 'message' => 'Columna no encontrada.']], 404);
                }

                if (!$this->policy->canManageBoard($projectId)) {
                    return HttpResponse::json(['error' => ['code' => 'forbidden', 'message' => 'Requiere rol gerente o superior.']], 403);
                }
            }
        }

        $result = $this->columnRepository->updatePositions($payload['ordered_ids']);
        return HttpResponse::json(['ok' => $result]);
    }

    public function update(HttpRequest $request): HttpResponse
    {
        $payload = $this->decodeJsonBody($request);
        if ($payload === null) {
            return HttpResponse::json(['error' => ['code' => 'validation_error', 'message' => 'JSON inválido.']], 422);
        }

        $columnId = (int) ($payload['id'] ?? 0);
        $name = trim((string) ($payload['name'] ?? ''));

        $errors = [];
        if ($columnId === 0) $errors['id'] = ['obrigatório'];
        if ($name === '') $errors['name'] = ['obrigatório'];
        if (mb_strlen($name) > 60) $errors['name'] = ['máximo de 60 caracteres'];
        if (!empty($errors)) {
            return HttpResponse::json(['error' => ['code' => 'validation_error', 'message' => 'Payload inválido.', 'details' => $errors]], 422);
        }

        $existing = $this->columnRepository->findById($columnId);
        if (!$existing) {
            return HttpResponse::json(['error' => ['code' => 'not_found', 'message' => 'Columna no encontrada.']], 404);
        }

        if ($this->columnRepository instanceof \App\Repositories\PdoColumnRepository
            && $this->projectRepo !== null
            && $this->policy !== null
            && $this->session !== null
        ) {
            $companyId = (int) ($this->session->get('company_id') ?? 0);
            $projectId = $this->columnRepository->resolveProjectId($columnId);

            if ($projectId === null || !$this->projectRepo->belongsToCompany($projectId, $companyId)) {
                return HttpResponse::json(['error' => ['code' => 'not_found', 'message' => 'Columna no encontrada.']], 404);
            }

            if (!$this->policy->canManageBoard($projectId)) {
                return HttpResponse::json(['error' => ['code' => 'forbidden', 'message' => 'Requiere rol gerente o superior.']], 403);
            }
        }

        try {
            $dto = new ColumnDTO(
                boardId: $existing->boardId,
                name: $name,
                position: $existing->position,
                id: $existing->id,
                createdAt: $existing->createdAt,
                updatedAt: $existing->updatedAt
            );
            $ok = $this->columnRepository->update($dto);
            if (!$ok) {
                return HttpResponse::json(['error' => ['code' => 'server_error', 'message' => 'No fue posible guardar.']], 500);
            }
            return HttpResponse::json(['ok' => true]);
        } catch (\PDOException $e) {
            $msg = (str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains($e->getMessage(), 'columns_board_name_unique'))
                ? 'Já existe uma coluna com este nome.'
                : 'Erro ao salvar.';
            return HttpResponse::json(['error' => ['code' => 'business_rule', 'message' => $msg]], 422);
        } catch (\Throwable $e) {
            return HttpResponse::json(['error' => ['code' => 'server_error', 'message' => 'Erro ao salvar.']], 500);
        }
    }

    private function decodeJsonBody(HttpRequest $request): ?array
    {
        $raw = $request->body();
        return json_decode($raw ?? '', true);
    }
}
