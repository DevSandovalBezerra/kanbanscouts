<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Helpers\HttpRequest;
use App\Helpers\HttpResponse;
use App\Helpers\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Services\PhpSessionStore;

return static function (Router $router, \PDO $pdo): void {
    $dbConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
    $sessionConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'session.php';
    $rateLimitConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'rate_limit.php';

    $session = new PhpSessionStore();
    $auth = new App\Services\AuthService(new App\Services\PdoUserRepository($pdo), $session);
    $authController = new AuthController($auth, $session);

    // Repositories
    $boardRepo   = new App\Repositories\PdoBoardRepository($pdo);
    $columnRepo  = new App\Repositories\PdoColumnRepository($pdo);
    $taskRepo    = new App\Repositories\PdoTaskRepository($pdo);
    $commentRepo = new App\Repositories\PdoCommentRepository($pdo);
    $historyRepo = new App\Repositories\PdoHistoryRepository($pdo);
    $labelRepo   = new App\Repositories\PdoLabelRepository($pdo);

    // Services
    $createBoardService   = new App\Services\Board\CreateBoardService($boardRepo);
    $createColumnService  = new App\Services\Column\CreateColumnService($columnRepo);
    $createTaskService    = new App\Services\Task\CreateTaskService($taskRepo);
    $moveTaskService      = new App\Services\Task\MoveTaskService($taskRepo, $historyRepo);
    $createCommentService = new App\Services\Comment\CreateCommentService($commentRepo);
    $deleteCommentService = new App\Services\Comment\DeleteCommentService($commentRepo);
    $createLabelService   = new App\Services\Label\CreateLabelService($labelRepo);
    $attachLabelService   = new App\Services\Label\AttachLabelService($labelRepo);
    $detachLabelService   = new App\Services\Label\DetachLabelService($labelRepo);

    // Shared policy dependencies (instantiated once, reused across controllers)
    $sharedProjectRepo    = new App\Repositories\PdoProjectRepository($pdo);
    $sharedMemberRepo     = new App\Repositories\PdoProjectMemberRepository($pdo);
    $sharedPolicy         = new App\Policies\ProjectPolicy($sharedMemberRepo, $session);
    $sharedColumnRepo     = new App\Repositories\PdoColumnRepository($pdo);

    // Controllers
    $boardController   = new App\Controllers\BoardController($createBoardService, $boardRepo, $session, $sharedProjectRepo, $sharedPolicy);
    $columnController  = new App\Controllers\ColumnController($createColumnService, $sharedColumnRepo, $session, new App\Repositories\PdoBoardRepository($pdo), $sharedProjectRepo, $sharedPolicy);
    $taskController    = new App\Controllers\TaskController($createTaskService, $moveTaskService, $taskRepo, $session, $sharedColumnRepo, $sharedProjectRepo, $sharedPolicy);
    $commentController = new App\Controllers\CommentController($commentRepo, $createCommentService, $deleteCommentService, $session);
    $labelController   = new App\Controllers\LabelController($labelRepo, $createLabelService, $attachLabelService, $detachLabelService, $session);


    $createProjectService = new App\Services\Project\CreateProjectService($sharedProjectRepo);
    $projectController    = new App\Controllers\ProjectController($sharedProjectRepo, $createProjectService, $session, $sharedMemberRepo);


    $router->addMiddleware(new AuthMiddleware($session, (int) ($sessionConfig['idle_timeout_seconds'] ?? 1800)));
    $router->addMiddleware(new RateLimitMiddleware(
        $session,
        (int) ($rateLimitConfig['login']['max_attempts'] ?? 5),
        (int) ($rateLimitConfig['login']['window_seconds'] ?? 60)
    ));
    $router->addMiddleware(new CsrfMiddleware($session, ['/api/auth/login']));

    // Helper to wrap controller actions
    $wrap = static function (object $controller, string $method): callable {
        return static function (HttpRequest $request) use ($controller, $method): HttpResponse {
            return $controller->$method($request);
        };
    };

    // Auth Routes
    $router->add('POST', '/api/auth/login', $wrap($authController, 'login'));
    $router->add('POST', '/api/auth/logout', $wrap($authController, 'logout'));
    $router->add('GET', '/api/auth/me', $wrap($authController, 'me'));

    // Board Routes
    $router->add('POST', '/api/boards', $wrap($boardController, 'create'));
    $router->add('GET', '/api/boards', $wrap($boardController, 'show'));

    // Column Routes
    $router->add('POST', '/api/columns', $wrap($columnController, 'create'));
    $router->add('POST', '/api/columns/reorder', $wrap($columnController, 'reorder'));

    // Task Routes
    $router->add('GET',   '/api/tasks',      $wrap($taskController, 'findTasksByColumnId'));
    $router->add('POST',  '/api/tasks',      $wrap($taskController, 'create'));
    $router->add('PATCH', '/api/tasks',      $wrap($taskController, 'update'));
    $router->add('POST',  '/api/tasks/move', $wrap($taskController, 'move'));

    // Comment Routes
    $router->add('GET', '/api/comments', $wrap($commentController, 'index'));
    $router->add('POST', '/api/comments', $wrap($commentController, 'create'));
    $router->add('DELETE', '/api/comments', $wrap($commentController, 'delete'));

    // Label Routes
    $router->add('GET',    '/api/labels',       $wrap($labelController, 'index'));
    $router->add('POST',   '/api/labels',       $wrap($labelController, 'create'));
    $router->add('DELETE', '/api/labels',       $wrap($labelController, 'delete'));
    $router->add('POST',   '/api/task-labels',  $wrap($labelController, 'attach'));
    $router->add('DELETE', '/api/task-labels',  $wrap($labelController, 'detach'));

    // Checklist Routes
    $checklistRepo          = new App\Repositories\PdoChecklistRepository($pdo);
    $createChecklistService = new App\Services\Checklist\CreateChecklistService($checklistRepo);
    $addItemService         = new App\Services\Checklist\AddChecklistItemService($checklistRepo);
    $toggleItemService      = new App\Services\Checklist\ToggleChecklistItemService($checklistRepo);
    $deleteItemService      = new App\Services\Checklist\DeleteChecklistItemService($checklistRepo);
    $checklistController    = new App\Controllers\ChecklistController(
        $checklistRepo, $createChecklistService, $addItemService, $toggleItemService, $deleteItemService
    );

    $router->add('GET',    '/api/checklists',       $wrap($checklistController, 'index'));
    $router->add('POST',   '/api/checklists',       $wrap($checklistController, 'create'));
    $router->add('DELETE', '/api/checklists',       $wrap($checklistController, 'delete'));
    $router->add('POST',   '/api/checklist-items',  $wrap($checklistController, 'addItem'));
    $router->add('PATCH',  '/api/checklist-items',  $wrap($checklistController, 'toggleItem'));
    $router->add('DELETE', '/api/checklist-items',  $wrap($checklistController, 'deleteItem'));


    // Project Routes
    $router->add('POST', '/api/projects/create', $wrap($projectController, 'create'));
    $router->add('POST', '/api/projects/update', $wrap($projectController, 'update'));
    $router->add('POST', '/api/projects/delete', $wrap($projectController, 'delete'));

    // Project Member Routes
    $memberController = new App\Controllers\ProjectMemberController(
        $sharedMemberRepo,
        $sharedProjectRepo,
        new App\Services\ProjectMember\AddMemberService($sharedMemberRepo, $pdo),
        new App\Services\ProjectMember\UpdateMemberRoleService($sharedMemberRepo),
        new App\Services\ProjectMember\RemoveMemberService($sharedMemberRepo),
        new App\Validators\InviteMemberValidator(),
        $sharedPolicy,
        $session
    );
    $router->add('GET',    '/api/project-members', $wrap($memberController, 'index'));
    $router->add('POST',   '/api/project-members', $wrap($memberController, 'add'));
    $router->add('PATCH',  '/api/project-members', $wrap($memberController, 'updateRole'));
    $router->add('DELETE', '/api/project-members', $wrap($memberController, 'remove'));

    // Attachment Routes
    $uploadBasePath      = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    $attachmentRepo      = new App\Repositories\PdoAttachmentRepository($pdo);
    $uploadService       = new App\Services\Attachment\UploadAttachmentService($attachmentRepo, $uploadBasePath);
    $deleteAttachService = new App\Services\Attachment\DeleteAttachmentService($attachmentRepo, $uploadBasePath);
    $attachmentController = new App\Controllers\AttachmentController($attachmentRepo, $uploadService, $deleteAttachService, $session);

    $router->add('GET',    '/api/attachments', $wrap($attachmentController, 'index'));
    $router->add('POST',   '/api/attachments', $wrap($attachmentController, 'upload'));
    $router->add('DELETE', '/api/attachments', $wrap($attachmentController, 'delete'));

    // Event Routes
    $repoEvents = new \App\Repositories\PdoEventRepository($pdo);
    $eventController = new \App\Controllers\EventController($repoEvents);
    $router->add('POST', '/api/events/create', $wrap($eventController, 'create'));

    // Dependency Routes
    $addDepService    = new App\Services\Task\AddDependencyService($pdo);
    $removeDepService = new App\Services\Task\RemoveDependencyService($pdo);
    $depController    = new App\Controllers\DependencyController($addDepService, $removeDepService, $pdo);

    $router->add('GET',    '/api/dependencies', $wrap($depController, 'index'));
    $router->add('POST',   '/api/dependencies', $wrap($depController, 'add'));
    $router->add('DELETE', '/api/dependencies', $wrap($depController, 'remove'));

    // Message Routes
    $router->add('GET', '/api/messages', static function (HttpRequest $request) use ($pdo, $session): HttpResponse {
        $userId = (int) $session->get('user_id');
        if ($userId === 0) return HttpResponse::json(['error' => ['code' => 'unauthorized']], 401);

        $peerId = (int) ($request->query()['peer_id'] ?? 0);
        if ($peerId === 0) return HttpResponse::json(['error' => ['code' => 'validation_error', 'details' => ['peer_id' => ['obrigatório']]]], 422);

        $stmt = $pdo->prepare(
            'SELECT m.id, m.sender_id, m.receiver_id, m.content, m.is_read,
                    m.created_at, u.name AS sender_name,
                    IF(m.sender_id = ?, 1, 0) AS is_own
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE (m.sender_id = ? AND m.receiver_id = ?)
                OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.created_at ASC
             LIMIT 100'
        );
        $stmt->execute([$userId, $userId, $peerId, $peerId, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format time for display
        foreach ($messages as &$msg) {
            $msg['time']    = date('H:i', strtotime($msg['created_at']));
            $msg['is_own']  = (bool) $msg['is_own'];
            $msg['is_read'] = (bool) $msg['is_read'];
        }
        unset($msg);

        // Mark incoming messages as read
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?')
            ->execute([$userId, $peerId]);

        return HttpResponse::json($messages);
    });

    $router->add('POST', '/api/messages', static function (HttpRequest $request) use ($pdo, $session): HttpResponse {
        $userId = (int) $session->get('user_id');
        if ($userId === 0) return HttpResponse::json(['error' => ['code' => 'unauthorized']], 401);

        $raw     = $request->body() ?? '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) return HttpResponse::json(['error' => ['code' => 'validation_error']], 422);

        $receiverId = (int) ($payload['receiver_id'] ?? 0);
        $content    = trim((string) ($payload['content'] ?? ''));

        $errors = [];
        if ($receiverId === 0) $errors['receiver_id'] = ['obrigatório'];
        if ($content === '')   $errors['content']     = ['obrigatório'];
        if ($receiverId === $userId) $errors['receiver_id'] = ['não pode enviar mensagem para si mesmo'];

        if (!empty($errors)) {
            return HttpResponse::json(['error' => ['code' => 'validation_error', 'details' => $errors]], 422);
        }

        $now  = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'INSERT INTO messages (sender_id, receiver_id, content, is_read, created_at) VALUES (?, ?, ?, 0, ?)'
        );
        $stmt->execute([$userId, $receiverId, $content, $now]);
        $id = (int) $pdo->lastInsertId();

        return HttpResponse::json([
            'id'          => $id,
            'sender_id'   => $userId,
            'receiver_id' => $receiverId,
            'content'     => $content,
            'is_own'      => true,
            'time'        => date('H:i', strtotime($now)),
            'created_at'  => $now,
        ], 201);
    });

    // Users (company members)
    $router->add('GET', '/api/users', static function (HttpRequest $request) use ($pdo, $session): HttpResponse {
        $companyId = (int) $session->get('company_id');
        if ($companyId === 0) return HttpResponse::json(['error' => ['code' => 'unauthorized']], 401);

        $stmt = $pdo->prepare(
            "SELECT id, name, email FROM users WHERE company_id = ? AND status = 'active' ORDER BY name ASC"
        );
        $stmt->execute([$companyId]);
        return HttpResponse::json($stmt->fetchAll(\PDO::FETCH_ASSOC));
    });

    // Admin — User Management (AdminMiddleware revalidates is_admin against DB)
    $adminMiddleware  = new AdminMiddleware($session, $pdo);
    $userController   = new App\Controllers\UserController(
        $pdo,
        new App\Services\User\CreateUserService($pdo),
        new App\Services\User\UpdateUserService($pdo),
        new App\Services\User\ToggleUserStatusService($pdo),
        new App\Services\User\ResetPasswordService($pdo),
        new App\Validators\CreateUserValidator(),
        new App\Validators\UpdateUserValidator(),
        $session
    );

    $adminWrap = static function (object $ctrl, string $method) use ($adminMiddleware): callable {
        return static function (HttpRequest $request) use ($ctrl, $method, $adminMiddleware): HttpResponse {
            return $adminMiddleware($request, fn ($r) => $ctrl->$method($r));
        };
    };

    $router->add('GET',    '/api/admin/users',                $adminWrap($userController, 'index'));
    $router->add('POST',   '/api/admin/users',                $adminWrap($userController, 'create'));
    $router->add('PATCH',  '/api/admin/users',                $adminWrap($userController, 'update'));
    $router->add('POST',   '/api/admin/users/toggle-status',  $adminWrap($userController, 'toggleStatus'));
    $router->add('POST',   '/api/admin/users/reset-password', $adminWrap($userController, 'resetPassword'));
    $router->add('DELETE', '/api/admin/users',                $adminWrap($userController, 'delete'));

    // Profile Routes (self-service, any authenticated user)
    $uploadBasePath  = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';
    $profileController = new App\Controllers\ProfileController($pdo, $session, $uploadBasePath);
    $router->add('POST', '/api/profile/update',   $wrap($profileController, 'update'));
    $router->add('POST', '/api/profile/password', $wrap($profileController, 'password'));
    $router->add('POST', '/api/profile/avatar',   $wrap($profileController, 'avatar'));

    // Global Search
    $router->add('GET', '/api/search', static function (HttpRequest $request) use ($pdo): HttpResponse {
        $q = $request->query()['q'] ?? '';
        if (strlen((string)$q) < 2) return HttpResponse::json(['results' => []]);
        
        $term = "%$q%";
        $stmt = $pdo->prepare("SELECT 'board' as type, id, name FROM boards WHERE name LIKE :term LIMIT 10");
        $stmt->execute(['term' => $term]);
        return HttpResponse::json(['results' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    });
};




