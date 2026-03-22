<?php

declare(strict_types=1);

use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Helpers\Router;

return static function (Router $router, \PDO $pdo): void {
    $session = new \App\Services\PhpSessionStore();

    // Redirect helper
    $ensureAuth = static function () use ($session): ?HttpResponse {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        if ($session->get('user_id') === null) {
            return HttpResponse::redirect($scriptName . '/login');
        }
        return null;
    };

    // Shared template data helper
    $sharedData = static function () use ($session, $pdo): array {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseDir    = str_replace('\\', '/', dirname($scriptName));
        if ($baseDir === '/' || $baseDir === '.') $baseDir = '';

        $userId    = (int) $session->get('user_id');
        $companyId = (int) $session->get('company_id');

        // Resolve user name + email
        $userName   = 'Usuário';
        $userEmail  = '';
        $userAvatar = '';
        if ($userId > 0) {
            $stmt = $pdo->prepare('SELECT name, email, avatar FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $userName   = $row['name'];
                $userEmail  = $row['email'];
                $userAvatar = (string) ($row['avatar'] ?? '');
            }
        }

        // Resolve first board for sidebar link
        $firstBoardId = 0;
        if ($companyId > 0) {
            $stmt = $pdo->prepare(
                'SELECT b.id FROM boards b
                 JOIN projects p ON p.id = b.project_id
                 WHERE p.company_id = ? LIMIT 1'
            );
            $stmt->execute([$companyId]);
            $firstBoardId = (int) ($stmt->fetchColumn() ?: 0);
        }

        // user_is_admin is for UI/navigation only — actions always revalidate against DB
        $userIsAdmin = (bool) $session->get('user_is_admin');

        return [
            'app_url'        => $scriptName,
            'base_path'      => $baseDir,
            'user_name'      => $userName,
            'user_email'     => $userEmail,
            'user_avatar'    => $userAvatar,
            'user_id'        => $userId,
            'company_id'     => $companyId,
            'first_board_id' => $firstBoardId,
            'user_is_admin'  => $userIsAdmin,
        ];
    };

    $router->add('GET', '/', static function (HttpRequest $request) use ($pdo, $ensureAuth, $sharedData): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $shared    = $sharedData();
        $companyId = $shared['company_id'];

        // Boards
        $stmt = $pdo->prepare('SELECT id FROM projects WHERE company_id = ? LIMIT 1');
        $stmt->execute([$companyId]);
        $projectId = (int) $stmt->fetchColumn();
        $boardRepo = new \App\Repositories\PdoBoardRepository($pdo);
        $boards    = $projectId > 0 ? $boardRepo->findByProjectId($projectId) : [];

        // Real stats
        $stmtTasks = $pdo->prepare(
            "SELECT COUNT(*) FROM tasks t
             JOIN columns c ON c.id = t.column_id
             JOIN boards b ON b.id = c.board_id
             JOIN projects p ON p.id = b.project_id
             WHERE p.company_id = ? AND t.status != 'done'"
        );
        $stmtTasks->execute([$companyId]);
        $pendingTasks = (int) $stmtTasks->fetchColumn();

        $stmtTeam = $pdo->prepare('SELECT COUNT(*) FROM users WHERE company_id = ?');
        $stmtTeam->execute([$companyId]);
        $teamSize = (int) $stmtTeam->fetchColumn();

        $stmtTotal = $pdo->prepare(
            "SELECT COUNT(*) FROM tasks t
             JOIN columns c ON c.id = t.column_id
             JOIN boards b ON b.id = c.board_id
             JOIN projects p ON p.id = b.project_id
             WHERE p.company_id = ?"
        );
        $stmtTotal->execute([$companyId]);
        $totalTasks = (int) $stmtTotal->fetchColumn();

        $stmtDone = $pdo->prepare(
            "SELECT COUNT(*) FROM tasks t
             JOIN columns c ON c.id = t.column_id
             JOIN boards b ON b.id = c.board_id
             JOIN projects p ON p.id = b.project_id
             WHERE p.company_id = ? AND t.status = 'done'"
        );
        $stmtDone->execute([$companyId]);
        $doneTasks  = (int) $stmtDone->fetchColumn();
        $donePct    = $totalTasks > 0 ? (int) round($doneTasks / $totalTasks * 100) : 0;

        return \App\Helpers\View::render('pages.dashboard', array_merge($shared, [
            'title'        => 'Painel de Controle - KanbanLite',
            'boards'       => $boards,
            'pending_tasks' => $pendingTasks,
            'team_size'    => $teamSize,
            'total_tasks'  => $totalTasks,
            'done_tasks'   => $doneTasks,
            'done_pct'     => $donePct,
        ]));
    });


    $router->add('GET', '/boards', static function (HttpRequest $request) use ($pdo, $ensureAuth, $session): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $boardId   = (int) ($request->query()['id'] ?? 1);
        $companyId = (int) $session->get('company_id');
        $userId    = (int) $session->get('user_id');

        $boardRepo  = new \App\Repositories\PdoBoardRepository($pdo);
        $columnRepo = new \App\Repositories\PdoColumnRepository($pdo);

        $board = $boardRepo->findById($boardId);
        if (!$board) {
            $projectId = $boardId;

            $stmtProj = $pdo->prepare('SELECT id, name FROM projects WHERE id = ? AND company_id = ? LIMIT 1');
            $stmtProj->execute([$projectId, $companyId]);
            $proj = $stmtProj->fetch(\PDO::FETCH_ASSOC);
            if (!$proj) {
                return HttpResponse::text('Quadro não encontrado: ' . $boardId, 404);
            }

            $stmtBoard = $pdo->prepare('SELECT id FROM boards WHERE project_id = ? ORDER BY id ASC LIMIT 1');
            $stmtBoard->execute([$projectId]);
            $existingBoardId = (int) ($stmtBoard->fetchColumn() ?: 0);

            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            if ($existingBoardId > 0) {
                return HttpResponse::redirect($scriptName . '/boards?id=' . $existingBoardId);
            }

            $newBoardId = $boardRepo->create(new \App\DTO\BoardDTO(
                projectId: $projectId,
                name: (string) ($proj['name'] ?? 'Quadro'),
                createdBy: $userId > 0 ? $userId : 1
            ));

            $defaultColumns = ['A Fazer', 'Em Progresso', 'Revisão', 'Concluído'];
            foreach ($defaultColumns as $idx => $name) {
                $columnRepo->create(new \App\DTO\ColumnDTO(
                    boardId: $newBoardId,
                    name: $name,
                    position: $idx + 1
                ));
            }

            return HttpResponse::redirect($scriptName . '/boards?id=' . $newBoardId);
        }

        // Resolve project name
        $stmtProj = $pdo->prepare('SELECT name FROM projects WHERE id = ? LIMIT 1');
        $stmtProj->execute([$board->projectId]);
        $projectName = (string) ($stmtProj->fetchColumn() ?: 'Projeto');

        $columns = $columnRepo->findByBoardId($boardId);

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseDir = str_replace('\\', '/', dirname($scriptName));
        if ($baseDir === '/' || $baseDir === '.') $baseDir = '';
        $appUrl = $scriptName;

        $data = [
            'base_path'    => $baseDir,
            'app_url'      => $appUrl,
            'title'        => 'Kanban - ' . $board->name,
            'board_name'   => $board->name,
            'project_name' => $projectName,
            'board_id'     => $boardId,
            'company_id'   => $companyId,
            'columns'      => $columns,
            'extra_css'    => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css">' .
                              '<link rel="stylesheet" href="' . $baseDir . '/assets/css/kanban.css?v=' . (string) (@filemtime(dirname(__DIR__) . '/public/assets/css/kanban.css') ?: time()) . '">',
            'extra_js'     => '<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>' .
                              '<script src="' . $baseDir . '/assets/js/kanban.js?v=' . (string) (@filemtime(dirname(__DIR__) . '/public/assets/js/kanban.js') ?: time()) . '"></script>',
        ];

        return \App\Helpers\View::render('pages.kanban', $data);
    });

    $router->add('GET', '/projects', static function (HttpRequest $request) use ($pdo, $ensureAuth, $session): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $repo          = new \App\Repositories\PdoProjectRepository($pdo);
        $createService = new \App\Services\Project\CreateProjectService($repo);
        $controller    = new \App\Controllers\ProjectController($repo, $createService, $session);
        return $controller->index($request);
    });

    $router->add('GET', '/contacts', static function (HttpRequest $request) use ($pdo, $ensureAuth): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $repo       = new \App\Repositories\PdoContactRepository($pdo);
        $controller = new \App\Controllers\ContactController($repo);
        return $controller->index($request);
    });

    $router->add('GET', '/calendar', static function (HttpRequest $request) use ($pdo, $ensureAuth): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $repo       = new \App\Repositories\PdoEventRepository($pdo);
        $controller = new \App\Controllers\EventController($repo);
        return $controller->index($request);
    });

    $router->add('GET', '/messages', static function (HttpRequest $request) use ($pdo, $ensureAuth, $sharedData): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $shared    = $sharedData();
        $userId    = $shared['user_id'];
        $companyId = $shared['company_id'];

        // Recent conversations: last message per peer
        $conversations = [];
        if ($userId > 0) {
            $stmt = $pdo->prepare(
                "SELECT
                    u.id   AS peer_id,
                    u.name AS peer_name,
                    u.email AS peer_email,
                    m.content AS last_msg,
                    m.created_at AS last_time,
                    SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread
                 FROM messages m
                 JOIN users u ON u.id = IF(m.sender_id = ?, m.receiver_id, m.sender_id)
                 WHERE (m.sender_id = ? OR m.receiver_id = ?)
                   AND u.company_id = ?
                 GROUP BY u.id, u.name, u.email, m.content, m.created_at
                 ORDER BY m.created_at DESC
                 LIMIT 20"
            );
            $stmt->execute([$userId, $userId, $userId, $userId, $companyId]);
            $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return \App\Helpers\View::render('pages.messages', array_merge($shared, [
            'title'         => 'Mensagens - KanbanLite',
            'conversations' => $conversations,
        ]));
    });

    $router->add('GET', '/documents', static function (HttpRequest $request) use ($pdo, $ensureAuth, $sharedData): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $shared    = $sharedData();
        $companyId = $shared['company_id'];

        // All attachments belonging to this company's tasks
        $stmt = $pdo->prepare(
            "SELECT ta.id, ta.filename, ta.filepath, ta.mime_type, ta.size_bytes, ta.created_at,
                    t.title AS task_title, u.name AS uploader_name
             FROM task_attachments ta
             JOIN tasks t   ON t.id  = ta.task_id
             JOIN users u   ON u.id  = ta.uploaded_by
             JOIN columns c ON c.id  = t.column_id
             JOIN boards b  ON b.id  = c.board_id
             JOIN projects p ON p.id = b.project_id
             WHERE p.company_id = ?
             ORDER BY ta.created_at DESC
             LIMIT 50"
        );
        $stmt->execute([$companyId]);
        $attachments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return \App\Helpers\View::render('pages.documents', array_merge($shared, [
            'title'       => 'Documentos - KanbanLite',
            'attachments' => $attachments,
        ]));
    });

    $router->add('GET', '/login', static function (HttpRequest $request) use ($session): HttpResponse {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $baseDir    = str_replace('\\', '/', dirname($scriptName));
        if ($baseDir === '/' || $baseDir === '.') $baseDir = '';

        if ($session->get('user_id') !== null) {
            return HttpResponse::redirect($scriptName . '/');
        }

        return \App\Helpers\View::render('pages.login', [
            'title'      => 'Login - KanbanLite',
            'app_url'    => $scriptName,
            'base_path'  => $baseDir,
            'user_name'  => '',
            'user_email' => '',
            'first_board_id' => 0,
        ], 'blank');
    });

    $router->add('GET', '/logout', static function (HttpRequest $request): HttpResponse {
        $session = new \App\Services\PhpSessionStore();
        $session->destroy();
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        return HttpResponse::redirect($scriptName . '/login');
    });

    $router->add('GET', '/projects/members', static function (HttpRequest $request) use ($pdo, $ensureAuth, $sharedData, $session): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $projectId = (int) ($request->query()['id'] ?? 0);
        if ($projectId === 0) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            return HttpResponse::redirect($scriptName . '/projects');
        }

        $shared    = $sharedData();
        $companyId = $shared['company_id'];
        $userId    = $shared['user_id'];

        // Tenant check
        $repo = new \App\Repositories\PdoProjectRepository($pdo);
        if (!$repo->belongsToCompany($projectId, $companyId)) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            return HttpResponse::redirect($scriptName . '/projects');
        }

        // Must be a member
        $memberRepo = new \App\Repositories\PdoProjectMemberRepository($pdo);
        $role       = $memberRepo->getRoleInProject($projectId, $userId);
        if ($role === null) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            return HttpResponse::redirect($scriptName . '/projects');
        }

        $stmtProj = $pdo->prepare('SELECT name FROM projects WHERE id = ? LIMIT 1');
        $stmtProj->execute([$projectId]);
        $projectName = (string) ($stmtProj->fetchColumn() ?: 'Projeto');

        return \App\Helpers\View::render('pages.project-members', array_merge($shared, [
            'title'        => 'Membros — ' . $projectName . ' - KanbanLite',
            'project_id'   => $projectId,
            'project_name' => $projectName,
        ]));
    });

    $router->add('GET', '/admin/users', static function (HttpRequest $request) use ($pdo, $ensureAuth, $sharedData): HttpResponse {
        if ($res = $ensureAuth()) return $res;

        $shared = $sharedData();
        $userId = $shared['user_id'];

        // Validate admin directly against DB (never trust session)
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || !(bool) $row['is_admin']) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            return HttpResponse::redirect($scriptName . '/');
        }

        return \App\Helpers\View::render('pages.admin-users', array_merge($shared, [
            'title' => 'Gerenciar Usuários - KanbanLite',
        ]));
    });

    $router->add('GET', '/health', static function (HttpRequest $request): HttpResponse {
        return HttpResponse::json(['ok' => true], 200);
    });
};
