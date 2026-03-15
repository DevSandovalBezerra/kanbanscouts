# Plano de Execução (TDD-first) — Kanban Modular Enterprise

Este documento transforma o PRD em um plano de entrega executável, com backlogs independentes por épico e uma sequência orientada por testes (TDD).

## Princípios

- TDD-first: cada entrega começa por testes (RED) e termina com testes passando (GREEN) e refatoração segura (REFACTOR)
- Pirâmide de testes:
  - Unit: Services + Policies (rápido, alta cobertura de regra)
  - Integration: Repositories + transações + constraints (SQLite)
  - Functional: API (fluxos críticos ponta-a-ponta, sem UI)
- Isolamento multiempresa é inegociável: nenhum endpoint pode vazar dados entre empresas
- Definition of Done por item: Validator + Service + Repository + Policy + histórico (quando aplicável) + testes

## Ordem de execução (dependências mínimas)

1. Fundação de testes (Backlog 0)
2. Multiempresa e modelo de dados (Backlog 1)
3. Autenticação, sessão e CSRF (Backlog 2)
4. RBAC e Policies (Backlog 3)
5. Kanban core (Backlog 4)
6. Admin global/empresa (Backlog 5)
7. UI/UX (Backlog 6)

## Workflow TDD por item (padrão)

1. Escrever teste unitário do Service/Policy (RED)
2. Implementar regra mínima no Service/Policy (GREEN)
3. Escrever teste de integração do Repository/transação/constraints (RED→GREEN)
4. Escrever teste funcional do endpoint (RED→GREEN)
5. Refatorar mantendo testes verdes (REFACTOR)

## Backlog 0 — Fundação de TDD (primeiro)

Objetivo: tornar barato escrever e rodar testes em loop curto.

- Padronizar bootstrap de testes PHPUnit por tipo (Unit/Integration/Functional)
- Criar helpers de banco SQLite para testes de integração
- Criar factories/fixtures de companies/users/projects/boards/columns/tasks
- Definir contrato padrão de erros da API e testes de serialização
- Criar smoke tests do pipeline local (rodar sempre)

Entregáveis:
- Estrutura de testes consistente
- Seed/fixtures reutilizáveis
- Suite mínima garantindo que “o projeto roda”

## Backlog 1 — Multiempresa e modelo de dados (isolamento primeiro)

Objetivo: garantir isolamento por company_id e integridade de dados antes de expandir features.

- Definir migrations do MVP (companies, users, projects, project_members, boards, columns, tasks, task_comments, task_history)
- Implementar constraints e índices mínimos e validar com testes (SQLite)
- Implementar filtro obrigatório por company_id nos Repositories (direto ou por joins)
- Criar testes de “vazamento” entre empresas (negative tests) para cada aggregate principal
- Implementar validação de pertencimento:
  - assigned_to deve ser membro do projeto
  - column_id deve pertencer ao board do projeto da empresa do usuário

Entregáveis:
- Schema MVP consistente
- Repositórios “company-safe” com testes

## Backlog 2 — Autenticação, sessão e CSRF

Objetivo: autenticação segura e base para proteger rotas web e API.

- Implementar endpoints /api/auth/login, /api/auth/logout, /api/auth/me
- Implementar sessão segura:
  - rotação de sessão no login
  - timeout por inatividade
- Implementar CSRF middleware para rotas web e ações mutáveis
- Implementar rate limiting no login e ações críticas (brute force protection)
- Criar testes funcionais para login/logout/me + expiração + CSRF

Entregáveis:
- Autenticação funcional
- Proteções mínimas aplicadas e testadas

## Backlog 3 — RBAC e Policies (antes do Kanban “real”)

Objetivo: travar o modelo de autorização antes de expandir CRUDs.

- Criar catálogo de permissões do MVP e seeds
- Implementar resolução de permissão por escopo:
  - global_admin (global)
  - company_admin (empresa)
  - manager/operator/viewer (projeto via project_members)
- Implementar Policies para Project/Board/Column/Task
- Suite de testes por papel (matriz mínima de permitir/negar)
- Aplicar middleware de permissão nas rotas e testar 403/200

Entregáveis:
- Autorização previsível e testada
- Rotas protegidas por escopo correto

## Backlog 4 — Kanban Core (entrega de valor)

Objetivo: operações completas de Kanban com consistência, ordenação e histórico.

### 4.1 Projetos e membership
- Implementar CRUD de projetos com validação e Policy
- Implementar endpoints de membership do projeto (add/update/remove) e testes
- Implementar listagem paginada e filtros básicos (quando aplicável)

### 4.2 Boards
- Implementar CRUD de boards por projeto
- Implementar consulta “board agregado”:
  - colunas ordenadas por position
  - tarefas por coluna ordenadas por position
- Testes de ordenação e pertencimento multiempresa

### 4.3 Colunas
- Implementar CRUD de colunas com unicidade de name e position por board
- Implementar reorder transacional de colunas e testes de consistência

### 4.4 Tarefas
- Implementar CRUD de tarefas:
  - position obrigatório e consistente por coluna
  - status apenas active/archived
- Implementar move transacional:
  - mudar column_id
  - atualizar position
  - reordenar afetados
- Implementar reorder dentro da coluna e testes
- Implementar archive/restore e testes de visibilidade no board
- Implementar histórico obrigatório em ações relevantes e testes de auditoria

### 4.5 Comentários
- Implementar criar/listar comentários e testes
- Comentários imutáveis no MVP (não editar)

Entregáveis:
- Kanban operacional via API com histórico e consistência

## Backlog 5 — Admin (global e empresa)

Objetivo: gestão corporativa mínima para operar o sistema.

- Implementar CRUD de companies (somente global_admin) e testes
- Implementar gestão de usuários por empresa (company_admin) e testes
- Implementar reset de senha administrativo e testes de segurança
- Implementar auditoria consultável (audit.view) e testes de escopo
- Implementar gestão mínima de templates (flags/ativação) se fizer parte do MVP

Entregáveis:
- Administração mínima do sistema com segurança por escopo

## Backlog 6 — UI/UX (MVP)

Objetivo: interface mínima para operar sem depender de ferramentas externas.

- Implementar páginas:
  - /login
  - /dashboard
  - /projects
  - /projects/{id}
  - /boards/{id}
  - /tasks/{id}
  - /admin (conforme permissões)
- Implementar mover/reordenar com fallback (botões) além de drag&drop
- Implementar padrão de feedback:
  - erros de validação por campo
  - mensagens de sucesso
- Garantir proteção CSRF em ações web e testes funcionais básicos

Entregáveis:
- UI funcional e segura para o MVP

## Critérios de pronto (Definition of Done)

- Item possui:
  - validação (Validator)
  - regra (Service)
  - persistência (Repository)
  - autorização (Policy + middleware)
  - histórico/auditoria quando aplicável
- Testes:
  - unit cobrindo regras e permissões
  - integration cobrindo transações e constraints
  - functional cobrindo o endpoint/fluxo principal
- Sem vazamento de dados entre empresas em nenhum cenário testado
