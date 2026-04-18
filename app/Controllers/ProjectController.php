<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\ProjectDTO;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Repositories\PdoProjectMemberRepository;
use App\Repositories\ProjectRepository;
use App\Services\Project\CreateProjectService;
use App\Services\SessionStore;

final class ProjectController
{
    public function __construct(
        private readonly ProjectRepository $repository,
        private readonly CreateProjectService $createService,
        private readonly ?SessionStore $session = null,
        private readonly ?PdoProjectMemberRepository $memberRepo = null
    ) {}

    public function index(HttpRequest $request): HttpResponse
    {
        $companyId = (int) ($this->session?->get('company_id') ?? 0);
        $userId    = (int) ($this->session?->get('user_id') ?? 0);

        $projects = ($companyId > 0 && $userId > 0)
            ? $this->repository->findByCompanyIdAndUserId($companyId, $userId)
            : [];

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseDir = str_replace('\\', '/', dirname($scriptName));
        if ($baseDir === '/' || $baseDir === '.') $baseDir = '';

        return \App\Helpers\View::render('pages.projects', [
            'title'    => 'Gerenciar Projetos - KanbanLite',
            'projects' => $projects,
            'app_url'  => $scriptName,
            'base_path' => $baseDir,
        ]);
    }

    public function create(HttpRequest $request): HttpResponse
    {
        $data = $request->jsonBody();
        if (empty($data['name'])) {
            return HttpResponse::json(['ok' => false, 'error' => 'El nombre es obligatorio'], 400);
        }

        $companyId = (int) ($this->session?->get('company_id') ?? 0);
        $userId    = (int) ($this->session?->get('user_id')    ?? 0);

        $dto = new ProjectDTO(
            id: null,
            companyId: $companyId,
            name: $data['name'],
            description: $data['description'] ?? '',
            createdBy: $userId
        );

        try {
            $id = $this->createService->execute($dto);

            // Auto-insert creator as owner in project_members
            if ($this->memberRepo !== null) {
                $this->memberRepo->add(
                    projectId: $id,
                    userId:    $userId,
                    role:      'owner',
                    invitedBy: $userId
                );
            }

            return HttpResponse::json(['ok' => true, 'id' => $id], 201);
        } catch (\Exception $e) {
            return HttpResponse::json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function update(HttpRequest $request): HttpResponse
    {
        $id = (int) $request->query()['id'];
        $data = $request->jsonBody();

        $project = $this->repository->findById($id);
        if (!$project) {
            return HttpResponse::json(['ok' => false, 'error' => 'Proyecto no encontrado'], 404);
        }

        $companyId = (int) ($this->session?->get('company_id') ?? 0);
        $userId    = (int) ($this->session?->get('user_id') ?? 0);
        if ($companyId === 0 || $userId === 0) {
            return HttpResponse::json(['ok' => false, 'error' => 'No autenticado'], 401);
        }

        if (!$this->repository->belongsToCompany($id, $companyId)) {
            return HttpResponse::json(['ok' => false, 'error' => 'Proyecto no encontrado'], 404);
        }

        $role = $this->memberRepo?->getRoleInProject($id, $userId);
        if ($role !== 'owner') {
            return HttpResponse::json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $project->name = $data['name'] ?? $project->name;
        $project->description = $data['description'] ?? $project->description;

        try {
            $this->repository->update($project);
            return HttpResponse::json(['ok' => true], 200);
        } catch (\Exception $e) {
            return HttpResponse::json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function delete(HttpRequest $request): HttpResponse
    {
        $id = (int) $request->query()['id'];
        try {
            $companyId = (int) ($this->session?->get('company_id') ?? 0);
            $userId    = (int) ($this->session?->get('user_id') ?? 0);
            if ($companyId === 0 || $userId === 0) {
                return HttpResponse::json(['ok' => false, 'error' => 'No autenticado'], 401);
            }

            if (!$this->repository->belongsToCompany($id, $companyId)) {
                return HttpResponse::json(['ok' => false, 'error' => 'Proyecto no encontrado'], 404);
            }

            $role = $this->memberRepo?->getRoleInProject($id, $userId);
            if ($role !== 'owner') {
                return HttpResponse::json(['ok' => false, 'error' => 'Sin permiso'], 403);
            }

            $this->repository->delete($id);
            return HttpResponse::json(['ok' => true], 200);
        } catch (\Exception $e) {
            return HttpResponse::json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
