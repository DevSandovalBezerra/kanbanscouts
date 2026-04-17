# Plano de Tradução — Espanhol Latino-Americano (Honduras)
**KanbanScout · Versão alvo: es-HN**
**Data:** 2026-04-17 | **Status:** Aguardando execução

---

## Objetivo

Traduzir **100% das saídas de texto visíveis ao usuário** do Português (PT-BR) para o **Espanhol Latino-Americano variante Honduras (es-HN)**. Isso inclui:

- Templates HTML (páginas e partiais)
- Mensagens JavaScript (alerts, confirms, toasts, SweetAlert2)
- Mensagens de erro e validação nos Controllers PHP
- Mensagens de validação nos Validators PHP

> **Nota de regionalismo Honduras:** usar "usted" em contextos formais (login, formulários), "vos" apenas em mensagens informais onde aplicável. Evitar gírias mexicanas ou argentinas. Vocabulário neutro centroamericano.

---

## Escopo — 34 Arquivos / ~390 Strings

### Bloco 1 — Templates de Página (11 arquivos)
Prioridade: **ALTA** — visíveis a todos os usuários em toda interação.

| # | Arquivo | Strings Est. | Conteúdo Principal |
|---|---------|-------------|--------------------|
| 1 | `templates/default/pages/login.php` | ~25 | Formulário de login, saudação, rodapé |
| 2 | `templates/default/pages/dashboard.php` | ~15 | Títulos, cards de resumo, atalhos |
| 3 | `templates/default/pages/kanban.php` | ~35 | Modais, botões, placeholders do board |
| 4 | `templates/default/pages/projects.php` | ~20 | Lista de projetos, estados vazios |
| 5 | `templates/default/pages/admin-users.php` | ~40 | Tabela de usuários, formulários de admin |
| 6 | `templates/default/pages/contacts.php` | ~15 | Lista de contatos, busca, convite |
| 7 | `templates/default/pages/calendar.php` | ~25 | Calendário, dias da semana, eventos |
| 8 | `templates/default/pages/messages.php` | ~20 | Interface de mensagens, placeholders |
| 9 | `templates/default/pages/documents.php` | ~15 | Repositório de documentos |
| 10 | `templates/default/pages/project-members.php` | ~30 | Gestão de membros, papéis |
| 11 | `templates/default/pages/project-secrets.php` | ~25 | Secrets/chaves de projeto |

### Bloco 2 — Layouts e Partiais (3 arquivos)
Prioridade: **ALTA** — presentes em todas as páginas.

| # | Arquivo | Strings Est. | Conteúdo Principal |
|---|---------|-------------|--------------------|
| 12 | `templates/default/layouts/base.html.php` | ~5 | Meta title, base HTML |
| 13 | `templates/default/partials/navbar.php` | ~15 | Barra de navegação superior |
| 14 | `templates/default/partials/sidebar.php` | ~15 | Menu lateral |

### Bloco 3 — JavaScript (1 arquivo)
Prioridade: **ALTA** — todas as mensagens interativas do Kanban.

| # | Arquivo | Strings Est. | Conteúdo Principal |
|---|---------|-------------|--------------------|
| 15 | `public/assets/js/kanban.js` | ~50+ | Alerts, confirms, toasts, validações inline |

### Bloco 4 — Controllers PHP (16 arquivos)
Prioridade: **MÉDIA** — mensagens de erro da API (visíveis via toast ou console).

| # | Arquivo | Strings Est. |
|---|---------|-------------|
| 16 | `app/Controllers/AuthController.php` | ~3 |
| 17 | `app/Controllers/BoardController.php` | ~3 |
| 18 | `app/Controllers/ColumnController.php` | ~12 |
| 19 | `app/Controllers/TaskController.php` | ~8 |
| 20 | `app/Controllers/ProjectController.php` | ~5 |
| 21 | `app/Controllers/UserController.php` | ~8 |
| 22 | `app/Controllers/ProjectMemberController.php` | ~5 |
| 23 | `app/Controllers/ProjectSecretController.php` | ~5 |
| 24 | `app/Controllers/ChecklistController.php` | ~3 |
| 25 | `app/Controllers/LabelController.php` | ~3 |
| 26 | `app/Controllers/CommentController.php` | ~3 |
| 27 | `app/Controllers/AttachmentController.php` | ~3 |
| 28 | `app/Controllers/DependencyController.php` | ~2 |
| 29 | `app/Controllers/EventController.php` | ~1 |
| 30 | `app/Controllers/ContactController.php` | ~1 |
| 31 | `app/Controllers/MessageController.php` | ~1 |

### Bloco 5 — Validators PHP (3 arquivos)
Prioridade: **MÉDIA** — mensagens de validação de formulários.

| # | Arquivo | Strings Est. |
|---|---------|-------------|
| 32 | `app/Validators/CreateUserValidator.php` | ~6 |
| 33 | `app/Validators/UpdateUserValidator.php` | ~6 |
| 34 | `app/Validators/InviteMemberValidator.php` | ~4 |

---

## Glossário Técnico — PT-BR → es-HN

Termos recorrentes com tradução padronizada para manter consistência.

| Português | Español (es-HN) | Observação |
|-----------|----------------|------------|
| Projeto | Proyecto | |
| Quadro / Board | Tablero | |
| Tarefa | Tarea | |
| Coluna | Columna | |
| Membro | Miembro | |
| Papel / Função | Rol | |
| Administrador | Administrador | |
| Gerenciador / Manager | Gestor | |
| Editor | Editor | |
| Visualizador / Viewer | Observador | |
| Proprietário / Owner | Propietario | |
| Empresa | Empresa | |
| Painel | Panel | |
| Configurações | Configuración | |
| Senha | Contraseña | |
| E-mail | Correo electrónico | |
| Entrar | Ingresar | |
| Cancelar | Cancelar | |
| Salvar | Guardar | |
| Criar | Crear | |
| Editar | Editar | |
| Excluir / Remover | Eliminar | |
| Convidar | Invitar | |
| Buscar | Buscar | |
| Carregar | Cargar | |
| Anexo | Adjunto | |
| Comentário | Comentario | |
| Checklist | Lista de verificación | |
| Etiqueta / Label | Etiqueta | |
| Secret / Chave | Clave secreta | |
| Documento | Documento | |
| Mensagem | Mensaje | |
| Notificação | Notificación | |
| Vencimento / Prazo | Fecha de vencimiento | |
| Atribuído para | Asignado a | |
| Nenhum / Nenhuma | Ninguno / Ninguna | |
| Sem descrição | Sin descripción | |
| Erro | Error | |
| Sucesso | Éxito | |
| Aviso | Advertencia | |
| Obrigatório | Obligatorio | |
| Inválido | Inválido | |
| Não encontrado | No encontrado | |
| Sem permissão | Sin permiso | |
| Fortalecendo comunidades mais saudáveis | Fortaleciendo comunidades más saludables | Slogan da marca |
| KanbanLite (título legado) | KanbanScout | Corrigir inconsistência de marca |

---

## Guia de Tom e Voz — es-HN

### Regras obrigatórias
1. **Formalidade:** usar "usted" em botões, formulários, labels. Ex: "Ingrese su correo".
2. **Neutro centroamericano:** sem "vosotros", sem gírias mexicanas ("güey"), sem "che" argentino.
3. **Datas:** formato `DD/MM/AAAA`. Meses em minúsculas.
4. **Erros:** mensagens diretas e claras. Ex: "El nombre es obligatorio" (não "¡Ups!").
5. **Confirmações destrutivas:** usar "¿Está seguro?" ou "Esta acción no se puede deshacer."
6. **Placeholders:** tom neutro. Ex: "Escriba aquí..." / "Buscar proyectos..."
7. **Nome da marca:** sempre "KanbanScout" — corrigir ocorrências de "KanbanLite".

---

## Ordem de Execução

```
FASE 1 — Estrutura compartilhada (impacto máximo)
  ├── navbar.php
  ├── sidebar.php
  └── base.html.php

FASE 2 — Página de entrada
  └── login.php

FASE 3 — Páginas principais (por frequência de uso)
  ├── dashboard.php
  ├── projects.php
  ├── kanban.php          ← maior complexidade
  ├── project-members.php
  └── project-secrets.php

FASE 4 — Páginas secundárias
  ├── contacts.php
  ├── calendar.php
  ├── messages.php
  └── documents.php

FASE 5 — Admin
  └── admin-users.php

FASE 6 — JavaScript
  └── kanban.js           ← ~50 strings em SweetAlert2 e toasts

FASE 7 — Backend (Controllers)
  └── [16 controllers]    ← mensagens de erro da API

FASE 8 — Validadores
  └── [3 validators]      ← mensagens de validação de formulário
```

---

## Critérios de Conclusão (Definition of Done)

- [ ] Nenhuma string em Português visível ao usuário em qualquer fluxo normal
- [ ] Todas as mensagens de erro da API retornam em Espanhol
- [ ] Todos os alerts/confirms/toasts do kanban.js em Espanhol
- [ ] Glossário respeitado em 100% das strings (sem termos divergentes)
- [ ] Inconsistência de marca corrigida: "KanbanLite" → "KanbanScout" em todos os arquivos
- [ ] Nenhuma funcionalidade quebrada por alteração de texto

---

## Resumo de Esforço Estimado

| Fase | Arquivos | Strings | Esforço Est. |
|------|----------|---------|-------------|
| 1–2 (Layout + Login) | 4 | ~60 | Baixo |
| 3 (Páginas principais) | 5 | ~120 | Médio |
| 4–5 (Secundárias + Admin) | 5 | ~110 | Médio |
| 6 (JavaScript) | 1 | ~50 | Médio-alto |
| 7–8 (Backend) | 19 | ~70 | Médio |
| **Total** | **34** | **~410** | — |

---

*Plano gerado em 2026-04-17. Executar fase a fase conforme disponibilidade.*
