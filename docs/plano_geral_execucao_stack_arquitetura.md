# Plano Geral de Execução — Stack e Arquitetura (PRD)

Este documento consolida stack, arquitetura e um plano geral de execução do sistema descrito em `prd_kanban_php_empresarial_markdown_v_1.md`, alinhado ao processo TDD-first.

## Referências do repositório

- PRD: `prd_kanban_php_empresarial_markdown_v_1.md`
- Regras do agente (TDD-first): `AGENT.md`
- Backlog TDD (épicos): `docs/plano_execucao_tdd_backlog.md`

## Stack oficial

### Backend
- PHP 8.3+
- PDO puro (camada Repository)

### Banco de dados
- MySQL (principal)
- SQLite (testes de integração)

### Frontend
- HTML5
- JavaScript Vanilla
- Tailwind CSS

### Testes
- PHPUnit

## Filosofia arquitetural

- Separação total entre core de negócio e camada visual substituível
- Controllers tratam HTTP e delegam regras para Services
- Services concentram regra empresarial e orquestram transações quando necessário
- Repositories fazem persistência via PDO e garantem filtros de escopo (principalmente company_id)
- Validators validam entrada e normalizam payloads antes de chegar ao domínio
- Policies aplicam autorização fina por recurso e ação
- Middleware protege rotas (auth, permission, csrf)
- Templates seguem estrutura inspirada no Hugo, com layouts, partials e pages

## Estrutura de diretórios (contrato)

```
project/
├── app/
│   ├── Controllers/
│   ├── Services/
│   ├── Repositories/
│   ├── Validators/
│   ├── DTO/
│   ├── Middleware/
│   ├── Policies/
│   ├── Permissions/
│   ├── Helpers/
│   ├── Exceptions/
│   ├── Traits/
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

## Arquitetura em camadas (responsabilidades)

### Controllers
- Recebem request, extraem parâmetros, chamam Validators e Services
- Definem status code e formato de resposta (web ou JSON)
- Não contêm regra de negócio

### Validators
- Validam campos obrigatórios e tipos
- Impõem regras de formato (ex.: prioridade, deadline, payloads de reorder/move)
- Retornam erros de validação no contrato padronizado da API

### Services
- Implementam casos de uso (CreateTask, MoveTask, ReorderColumns, AssignTask, etc.)
- Orquestram transações (begin/commit/rollback) em operações multi-step
- Disparam registro de histórico/auditoria quando aplicável

### Repositories
- Persistência via PDO (MySQL/SQLite)
- Consultas devem ser “company-safe”:
  - filtrar por company_id direta ou indiretamente (via joins)
  - validar pertencimento de IDs informados pelo cliente

### Policies
- Autorização fina por recurso e ação
- Usadas por middleware e pelos Services quando necessário

### Middleware
- AuthMiddleware: exige sessão autenticada
- PermissionMiddleware: exige permissão/papel compatível com a rota
- CsrfMiddleware: exige token em ações mutáveis nas rotas web

### Templates e view engine
- `templates/default/layouts`: layouts base
- `templates/default/partials`: header/sidebar/footer/navbar
- `templates/default/pages`: páginas (dashboard/projects/tasks/login)
- Renderização via engine interna do tipo `View::render('tasks.index', $data)`

## Domínio e invariantes do MVP (pontos que não podem quebrar)

### Multiempresa (isolamento)
- `company_id` é obrigatório no modelo e nas consultas
- Nenhum endpoint pode permitir leitura/escrita cross-company

### Fonte da verdade do Kanban
- Posição e coluna determinam visualização no board
  - coluna atual: `tasks.column_id`
  - status: somente `active|archived`

### Ordenação
- Colunas: `columns.position` por board
- Tarefas: `tasks.position` por coluna
- Move e reorder devem ser transacionais

### Membership
- `project_members` define acesso ao projeto e papel no projeto
- `assigned_to` deve ser membro do projeto no MVP

## Rotas e contratos (visão geral)

### Web
- /login
- /dashboard
- /projects
- /boards/{id}
- /tasks/{id}
- /admin (conforme permissões)

### API interna (MVP)
- /api/auth/*
- /api/companies/* (global_admin)
- /api/users/*
- /api/projects/* e /api/projects/{id}/members/*
- /api/boards/* e /api/boards/{id}/columns/*
- /api/columns/* e reorder
- /api/tasks/* e subrecursos (move, reorder, archive, restore, comments, history)

### Contrato de erro padrão
- Resposta JSON com `error.code`, `error.message` e `error.details` quando aplicável

## Estratégia de testes (TDD-first)

### Pirâmide
- Unit: Services e Policies (maior volume)
- Integration: Repositories + SQLite (constraints, transações, isolamento por company_id)
- Functional: endpoints críticos da API (fluxos)

### Ordem obrigatória por mudança
1. Teste falhando (RED)
2. Implementação mínima (GREEN)
3. Refatoração segura (REFACTOR)

### Critérios mínimos para aceitar uma entrega
- Caso de uso com cobertura unitária de regra
- Persistência validada via testes de integração
- Endpoint/fluxo validado via teste funcional
- Autorização e isolamento por empresa cobertos por testes negativos (403/404)

## Plano geral de execução (macro)

### Fase 0 — Base de testes e tooling
- Padronizar suites e helpers de teste
- Fixtures para entidades principais
- Smoke test garantindo execução local

### Fase 1 — Schema e isolamento multiempresa
- Migrations do MVP e constraints
- Repositories company-safe
- Testes de vazamento entre empresas

### Fase 2 — Autenticação, sessão e CSRF
- Login/logout/me
- Sessão segura e expiração
- CSRF e rate limiting com testes

### Fase 3 — RBAC e Policies
- Seeds de roles/permissions
- Resolução por escopo (global/empresa/projeto)
- Policies por recurso e middleware nas rotas

### Fase 4 — Kanban core
- Projetos e membership
- Boards agregados (colunas+tarefas ordenadas)
- Colunas (CRUD + reorder)
- Tarefas (CRUD + move + reorder + archive/restore)
- Comentários e histórico

### Fase 5 — Admin
- Companies e Users
- Auditoria consultável
- Gestão mínima de templates (se no MVP)

### Fase 6 — UI/UX
- Páginas e interações com fallback
- Feedback de validação e erros
- Segurança aplicada na camada web (CSRF)

## Definition of Done (DoD)

- Validator + Service + Repository + Policy/middleware implementados
- Histórico/auditoria incluídos quando aplicável
- Testes unit/integration/functional cobrindo o fluxo principal e negativos de segurança
- Sem vazamento de dados entre empresas em cenários testados
