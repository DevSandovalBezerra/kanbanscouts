# PRD — Kanban Corporativo Modular em PHP

## 1. Nome do Produto
Kanban Modular Enterprise

## 2. Objetivo do Produto
Sistema web para gestão de pequenos projetos empresariais com:

- controle de tarefas
- colunas dinâmicas
- usuários
- permissões corporativas
- templates front customizáveis
- arquitetura escalável

## 3. Stack Oficial

### Backend
- PHP 8.3+

### Banco principal
- MySQL

### Banco testes
- SQLite

### Frontend
- HTML5
- JavaScript Vanilla
- Tailwind CSS

### Testes
- PHPUnit

### Estrutura templates
- inspirado em Hugo

## 4. Filosofia arquitetural
Separação total entre:

- Core de negócio
- Camada visual totalmente substituível

## 5. Estrutura oficial de diretórios

```bash
project/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── routes/
├── storage/
├── templates/
├── tests/
├── vendor/
```

## 6. app/

```bash
app/
├── Controllers/
├── Services/
├── Repositories/
├── Validators/
├── DTO/
├── Middleware/
├── Policies/
├── Permissions/
├── Helpers/
├── Exceptions/
├── Traits/
```

## 7. Controllers
Responsabilidade:
- entrada HTTP apenas
- sem regra de negócio

### Exemplos
- AuthController.php
- TaskController.php
- ProjectController.php
- AdminController.php
- PermissionController.php

## 8. Services
Toda regra empresarial.

### Exemplos
- CreateTaskService.php
- MoveTaskService.php
- AssignTaskService.php
- LoginService.php
- PermissionResolverService.php

## 9. Repositories
PDO puro.

### Exemplos
- TaskRepository.php
- UserRepository.php
- PermissionRepository.php

## 10. Validators
Entrada obrigatória.

### Exemplos
- LoginValidator.php
- TaskValidator.php
- UserValidator.php

## 11. DTO
Contrato interno.

### Exemplos
- TaskDTO.php
- UserDTO.php
- LoginDTO.php

## 12. Middleware
Interceptação.

### Exemplos
- AuthMiddleware.php
- PermissionMiddleware.php
- CsrfMiddleware.php

## 13. Policies
Regra fina de autorização.

### Exemplos
- TaskPolicy.php
- ProjectPolicy.php

## 14. Sistema RBAC
Perfis:
- global_admin
- company_admin
- manager
- operator
- viewer

## 15. Tabelas RBAC

### users
- id
- name
- email
- password
- company_id
- status

### roles
- id
- name

### permissions
- id
- name

### role_permissions
- role_id
- permission_id

### user_roles
- user_id
- role_id

## 16. Login corporativo
- password_hash()
- password_verify()
- session_regenerate_id(true)
- timeout de sessão
- csrf obrigatório
- brute force protection

## 17. Templates estilo Hugo

```bash
templates/
├── default/
│   ├── layouts/
│   ├── partials/
│   ├── pages/
│   ├── assets/
```

### layouts
- base.html.php
- admin.html.php
- auth.html.php

### partials
- header.php
- sidebar.php
- footer.php
- navbar.php

### pages
- dashboard.php
- tasks.php
- projects.php
- login.php

## 18. Engine de renderização

```php
View::render('tasks.index', $data);
```

## 19. Kanban core
Entidades:
- project
- board
- column
- task
- comment
- history

## 20. Banco principal

Resumo das tabelas do MVP. Para detalhes completos, constraints e índices, ver a seção 39.

### companies
- id
- name
- status
- created_at
- updated_at

### projects
- id
- company_id
- name
- description
- created_by
- created_at
- updated_at

### project_members
- project_id
- user_id
- role_in_project
- created_at

### boards
- id
- project_id
- name
- created_by
- created_at
- updated_at

### columns
- id
- board_id
- name
- position
- created_at
- updated_at

### tasks
- id
- column_id
- title
- description
- assigned_to
- priority
- deadline
- status
- position
- created_by
- created_at
- updated_at

### task_comments
- id
- task_id
- user_id
- body
- created_at

### task_history
- id
- task_id
- action
- old_value
- new_value
- user_id
- created_at

## 21. Histórico obrigatório
Toda alteração gera histórico.

## 22. Transações
- beginTransaction
- commit
- rollback

## 23. Testes

### Unit
- Services

### Integration
- PDO + SQLite

### Functional
- APIs completas

### Contract
- JSON schema

### E2E
- navegador

## 24. Ambientes
- local
- staging
- production

## 25. Configuração

```bash
config/
├── app.php
├── database.php
├── permissions.php
├── session.php
```

## 26. Storage

```bash
storage/
├── logs/
├── cache/
├── uploads/
├── sessions/
```

## 27. API interna

### routes/api.php
Catálogo mínimo de endpoints do MVP está especificado na seção 41. Neste arquivo, as rotas devem seguir os grupos:
- /api/auth/*
- /api/companies/* (global_admin)
- /api/users/*
- /api/projects/* e /api/projects/{id}/members/*
- /api/boards/* e /api/boards/{id}/columns/*
- /api/columns/* e /api/boards/{id}/columns/reorder
- /api/tasks/* e subrecursos (move, reorder, archive, restore, comments, history)

## 28. Web routes

### routes/web.php
- GET /dashboard
- GET /projects
- GET /tasks

## 29. Admin global
Controla:
- empresas
- usuários
- permissões
- auditoria
- templates

## 30. Segurança mínima
- csrf
- htmlspecialchars()
- prepared statements
- upload seguro

## 31. Nomenclatura corporativa
Nunca usar nomes genéricos.

Sempre usar:
- CreateProjectService.php
- UpdateTaskDeadlineService.php
- ResolvePermissionService.php

## 32. Regra central
Uma classe = uma responsabilidade

## 33. Evolução futura
- notificações
- comentários
- anexos
- webhooks
- API pública

## 34. Escopo do MVP

### Objetivo do MVP
Entregar um Kanban corporativo multiempresa, com autenticação, isolamento por empresa, RBAC, projetos, boards, colunas, tarefas, histórico e uma UI funcional.

### Incluído no MVP
- Autenticação (login/logout) com sessão segura e CSRF
- Multiempresa (companies) com isolamento por company_id em toda consulta/escrita
- Usuários e RBAC (roles, permissions, user_roles, role_permissions)
- Projetos com participantes e permissões
- Boards por projeto
- Colunas por board (CRUD + ordenação)
- Tarefas por coluna (CRUD + mover + ordenação)
- Comentários em tarefas
- Histórico/auditoria de mudanças relevantes
- Admin global para gestão de empresas e usuários

### Fora de escopo do MVP
- Notificações (e-mail, push)
- Webhooks e integrações externas
- API pública externa (manter apenas interna)
- SSO (SAML/OIDC)
- Tempo real via websockets
- Gantt, calendário, relatórios avançados

## 35. Personas e papéis

### Global Admin
Administra o sistema inteiro: empresas, usuários, templates, auditoria e configuração.

### Company Admin
Administra a empresa: usuários, papéis e permissões dentro da empresa; visibilidade total dos projetos da empresa.

### Manager
Gerencia projetos e boards: cria colunas, define regras, cria/edita/move tarefas de qualquer membro do projeto.

### Operator
Executa trabalho: cria/edita/move tarefas onde tem permissão, normalmente em projetos onde participa.

### Viewer
Somente leitura: visualiza projetos/boards/tarefas conforme permissão.

## 36. Fluxos principais

### Autenticação
1. Usuário acessa /login
2. Envia credenciais, recebe sessão autenticada
3. Ao autenticar: session_regenerate_id(true)
4. Sessão expira por inatividade (config/session.php)

### Criar projeto e board
1. Company Admin ou Manager cria projeto
2. Define participantes do projeto e seus papéis (membership)
3. Cria um board inicial no projeto
4. Cria colunas padrão (ex.: A Fazer, Fazendo, Feito)

### Operação Kanban (tarefas)
1. Criar tarefa em uma coluna
2. Editar atributos (título, descrição, prioridade, deadline, responsável)
3. Mover tarefa entre colunas (drag&drop ou ação)
4. Reordenar tarefas dentro da coluna
5. Registrar histórico em toda mudança relevante
6. Comentar em uma tarefa
7. Arquivar/restaurar tarefa

## 37. Requisitos funcionais (detalhados)

### 37.1 Empresas (multi-tenant)
- O sistema suporta múltiplas empresas com isolamento completo por company_id
- Um usuário pertence a exatamente uma empresa (company_id obrigatório) no MVP
- Global Admin pode criar/ativar/desativar empresas
- Company Admin pode gerenciar usuários da própria empresa

### 37.2 Usuários
- Criar usuário (Company Admin) com e-mail único por empresa
- Ativar/desativar usuário (status)
- Reset de senha por fluxo administrativo (Company Admin) no MVP

### 37.3 Papéis e permissões (RBAC)
- Papéis são atribuídos por usuário e escopo:
  - Global: global_admin
  - Empresa: company_admin
  - Projeto: manager/operator/viewer
- Permissões são avaliadas em Policies (por recurso + ação)

### 37.4 Projetos
- CRUD de projetos (restrito por permissão)
- Projeto pertence a uma empresa (company_id)
- Participantes do projeto:
  - adicionar/remover usuário ao projeto
  - definir papel no projeto (manager/operator/viewer)

### 37.5 Boards
- CRUD de boards por projeto
- Board pertence a um projeto
- Um projeto pode ter múltiplos boards

### 37.6 Colunas
- CRUD de colunas por board
- Ordenação de colunas via campo position
- Restrições:
  - position deve ser única por board
  - nome da coluna deve ser único por board (case-insensitive)

### 37.7 Tarefas
- CRUD de tarefas
- Regras:
  - tarefa pertence a uma coluna (column_id)
  - tarefa tem ordenação dentro da coluna via position
  - mover tarefa entre colunas preserva consistência (transação)
- Atribuição:
  - assigned_to opcional
  - assigned_to deve ser membro do projeto (no MVP)
- Arquivamento:
  - status: active | archived
  - tarefas archived não aparecem no board por padrão

### 37.8 Comentários
- Criar e listar comentários em tarefas
- Comentários são imutáveis (não editar) no MVP
- Permitir exclusão apenas por manager/company_admin (opcional)

### 37.9 Histórico e auditoria
- Toda alteração relevante em tarefas, colunas, boards e projetos gera um evento de histórico
- Histórico mínimo para tarefas:
  - criação
  - edição de campos relevantes (title, description, assigned_to, priority, deadline, status)
  - movimento de coluna e reordenação

### 37.10 Admin global
- CRUD de empresas
- CRUD de usuários (cross-company) e atribuição de global_admin
- Visualização de auditoria global
- Gestão de templates instalados/ativos por empresa

## 38. Regras de domínio e consistência

### Fonte da verdade do estado Kanban
- A coluna atual da tarefa é definida por tasks.column_id
- O campo tasks.status é usado apenas para active/archived

### Ordenação
- columns.position define a ordem das colunas no board
- tasks.position define a ordem das tarefas dentro da coluna
- Operações de reorder e move são sempre transacionais

### Integridade multiempresa
- Toda entidade de negócio deve carregar company_id direta ou indiretamente e ser validada por joins/filters no repositório
- Nunca aceitar IDs “soltos” sem validar pertencimento à empresa do usuário autenticado

## 39. Modelo de dados (completo do MVP)

### Convenções
- Todas as tabelas com created_at e updated_at (datetime) no MySQL
- Soft-delete não é obrigatório no MVP; arquivamento aplica-se apenas a tasks
- Índices em chaves estrangeiras e em colunas de busca frequente

### companies
- id
- name
- status (active|inactive)
- created_at
- updated_at

### users (atualização)
- id
- company_id
- name
- email
- password
- status (active|inactive)
- created_at
- updated_at

### projects (atualização)
- id
- company_id
- name
- description
- created_by (user_id)
- created_at
- updated_at

### project_members
- project_id
- user_id
- role_in_project (manager|operator|viewer)
- created_at

### boards (atualização)
- id
- project_id
- name
- created_by (user_id)
- created_at
- updated_at

### columns (atualização)
- id
- board_id
- name
- position
- created_at
- updated_at

### tasks (atualização)
- id
- column_id
- title
- description
- assigned_to (user_id, nullable)
- priority (low|medium|high)
- deadline (datetime, nullable)
- status (active|archived)
- position
- created_by (user_id)
- created_at
- updated_at

### task_comments
- id
- task_id
- user_id
- body
- created_at

### task_history (atualização)
- id
- task_id
- action
- old_value
- new_value
- user_id
- created_at

### constraints e índices mínimos
- users: unique(company_id, email)
- project_members: unique(project_id, user_id)
- columns: unique(board_id, position)
- columns: unique(board_id, name)
- tasks: index(column_id, position)

## 40. Permissões (RBAC) — catálogo mínimo

### Permissões sugeridas (permissions.name)
- company.manage_users
- company.manage_roles
- project.create
- project.update
- project.delete
- project.manage_members
- board.create
- board.update
- board.delete
- column.create
- column.update
- column.delete
- task.create
- task.update
- task.move
- task.archive
- task.comment
- audit.view
- template.manage

### Matriz recomendada (alto nível)
- global_admin: tudo + cross-company
- company_admin: company.* + audit.view + template.manage + permissões de projeto/board/coluna/tarefa dentro da empresa
- manager: project.* (exceto delete, opcional) + project.manage_members + board/column/task completos no projeto
- operator: task.create/update/move/comment no projeto; sem gerenciar membros e sem deletar projeto/board
- viewer: leitura (sem permissões de escrita)

## 41. Contratos de API (interna)

### Convenções de resposta
- Sucesso: 2xx com JSON
- Erro: 4xx/5xx com JSON

Formato de erro:
```json
{
  "error": {
    "code": "validation_error",
    "message": "Mensagem legível",
    "details": {
      "field": ["mensagem"]
    }
  }
}
```

### Endpoints mínimos (MVP)

Auth
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/auth/me

Companies (global_admin)
- POST /api/companies
- GET /api/companies
- PUT /api/companies/{id}
- POST /api/companies/{id}/disable

Users
- POST /api/users
- GET /api/users
- PUT /api/users/{id}
- POST /api/users/{id}/disable
- POST /api/users/{id}/reset-password

Projects
- POST /api/projects
- GET /api/projects
- GET /api/projects/{id}
- PUT /api/projects/{id}
- DELETE /api/projects/{id}
- POST /api/projects/{id}/members
- DELETE /api/projects/{id}/members/{userId}
- PUT /api/projects/{id}/members/{userId}

Boards
- POST /api/projects/{projectId}/boards
- GET /api/projects/{projectId}/boards
- GET /api/boards/{id}
- PUT /api/boards/{id}
- DELETE /api/boards/{id}

Columns
- POST /api/boards/{boardId}/columns
- GET /api/boards/{boardId}/columns
- PUT /api/columns/{id}
- DELETE /api/columns/{id}
- POST /api/boards/{boardId}/columns/reorder

Tasks
- POST /api/tasks
- GET /api/tasks/{id}
- PUT /api/tasks/{id}
- DELETE /api/tasks/{id}
- POST /api/tasks/{id}/move
- POST /api/tasks/{id}/reorder
- POST /api/tasks/{id}/archive
- POST /api/tasks/{id}/restore

Comments
- POST /api/tasks/{taskId}/comments
- GET /api/tasks/{taskId}/comments

History
- GET /api/tasks/{taskId}/history

### Payloads mínimos

Criar tarefa:
```json
{
  "column_id": 123,
  "title": "Implementar login",
  "description": "Fluxo básico de autenticação",
  "assigned_to": 55,
  "priority": "high",
  "deadline": "2026-03-20T18:00:00"
}
```

Mover tarefa:
```json
{
  "to_column_id": 456,
  "to_position": 2
}
```

Reordenar colunas:
```json
{
  "ordered_column_ids": [10, 12, 11, 13]
}
```

## 42. UI/UX (MVP)

### Páginas
- /login: formulário de autenticação
- /dashboard: visão geral (projetos recentes, boards)
- /projects: lista e criação de projetos
- /projects/{id}: detalhes do projeto e boards
- /boards/{id}: kanban (colunas e tarefas)
- /tasks/{id}: detalhe da tarefa (comentários, histórico)
- /admin: gestão (global_admin/company_admin conforme escopo)

### Interações essenciais
- Drag&drop para mover e reordenar tarefas (com fallback por botões)
- Drag&drop para reordenar colunas (com fallback por botões)
- Validações de formulário com feedback claro
- Tratamento padrão de erros e mensagens de sucesso

## 43. Requisitos não-funcionais e segurança

### Segurança
- Sessões seguras: cookie HttpOnly, SameSite e Secure em produção
- CSRF obrigatório em rotas web e ações mutáveis
- Rate limiting no login e ações críticas (brute force protection)
- Senhas: password_hash() com algoritmo padrão seguro e custo adequado
- Prepared statements em toda operação de banco
- Escapar saída HTML com htmlspecialchars() nas views
- Upload seguro:
  - validar tipo MIME e extensão
  - limitar tamanho
  - armazenar fora de public/ e servir via controller com autorização

### Confiabilidade e consistência
- Operações de move/reorder de tarefas e colunas são transacionais
- Conflitos de concorrência: aceitar last-write-wins no MVP e registrar histórico

### Performance mínima
- Paginação em listas (projects, tasks, users)
- Consultas de board devem trazer colunas + tarefas ordenadas com 1 query por agregado (ou número controlado)

## 44. Observabilidade e auditoria
- Log de aplicação em storage/logs com rotação por ambiente
- Auditoria:
  - registrar user_id, company_id, recurso, ação, timestamp e diffs relevantes
  - expor consulta para admins conforme permissão (audit.view)

## 45. Estratégia de testes (MVP)

### Unit (Services)
- Criar/mover/reordenar tarefa
- Regras de permissão em Policies (cenários principais)

### Integration (PDO + SQLite)
- Repositories com transações e constraints
- Consultas por company_id e validação de pertencimento

### Functional (API)
- Fluxos principais: login, CRUD projeto/board/coluna, criar/mover/arquivar tarefa, comentar e histórico

## 46. Migrações e seed (diretriz)
- Migrar schema do MySQL com scripts versionados em database/
- Seeds mínimos:
  - roles e permissions padrão
  - global_admin inicial em ambiente local (configurável)

## 47. Critérios de pronto (Definition of Done)
- Feature com validação (Validator) + regra (Service) + persistência (Repository) + autorização (Policy)
- Histórico gerado quando aplicável
- Testes unit e integração cobrindo o fluxo principal
- Rotas protegidas por middleware de autenticação e permissão
- Sem dados de empresa vazando entre empresas em nenhum endpoint
