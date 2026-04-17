/* ================================================================
   KanbanLite — main board script
   ================================================================ */

'use strict';

// ── Global state ────────────────────────────────────────────────
let currentTaskId    = null;
let currentCompanyId = null;
let currentBoardId   = null;
let quillCreate      = null;   // Quill instance in create modal
let quillDetail      = null;   // Quill instance in detail modal
let companyUsers     = [];     // [{id, name, email}] — loaded once on boot

// ── Bootstrap ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    const board = document.getElementById('kanban-board');
    if (!board) return;

    currentCompanyId = parseInt(board.closest('[data-company-id]')?.dataset.companyId || '0', 10);
    currentBoardId   = parseInt(board.closest('[data-board-id]')?.dataset.boardId || '0', 10);

    initQuill();
    initDragAndDrop();
    initAddColumn();
    initColumnRename();
    await loadCompanyUsers();   // load once before rendering tasks
    fetchAllTasks();
    initAttachmentDropzone();
});

function initAddColumn() {
    const trigger = document.querySelector('[data-action="add-column"]');
    if (!trigger) return;

    const open = async () => {
        if (!currentBoardId) {
            Swal.fire('Erro', 'Board inválido.', 'error');
            return;
        }

        const { value } = await Swal.fire({
            title: 'Nova Coluna',
            input: 'text',
            inputPlaceholder: 'Ex: Backlog',
            confirmButtonText: 'Criar',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            inputValidator: (v) => {
                const name = (v || '').trim();
                if (!name) return 'Informe um nome.';
                if (name.length > 60) return 'Máximo de 60 caracteres.';
                return null;
            },
        });

        const name = (value || '').trim();
        if (!name) return;

        const position = document.querySelectorAll('[data-column-id]').length + 1;
        try {
            const res = await API.post('/api/columns', { board_id: currentBoardId, name, position });
            if (res?.id) {
                await Swal.fire({ icon: 'success', title: 'Coluna criada!', timer: 1200, showConfirmButton: false });
                window.location.reload();
                return;
            }

            Swal.fire('Erro', res?.error?.message || res?.error || 'Não foi possível criar a coluna.', 'error');
        } catch {
            Swal.fire('Erro', 'Falha na comunicação.', 'error');
        }
    };

    trigger.addEventListener('click', open);
    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            open();
        }
    });
}

function initColumnRename() {
    const els = document.querySelectorAll('[data-column-id] [data-action="rename-column"]');
    if (!els.length) return;

    const open = async (el) => {
        const columnWrap = el.closest('[data-column-id]');
        const columnId = parseInt(columnWrap?.dataset.columnId || '0', 10);
        if (!columnId) {
            Swal.fire('Erro', 'Coluna inválida.', 'error');
            return;
        }

        const currentName = (el.textContent || '').trim();

        const { value } = await Swal.fire({
            title: 'Renomear Coluna',
            input: 'text',
            inputValue: currentName,
            confirmButtonText: 'Salvar',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            inputValidator: (v) => {
                const name = (v || '').trim();
                if (!name) return 'Informe um nome.';
                if (name.length > 60) return 'Máximo de 60 caracteres.';
                return null;
            },
        });

        const name = (value || '').trim();
        if (!name || name === currentName) return;

        try {
            const res = await API.patch('/api/columns', { id: columnId, name });
            if (res?.ok) {
                el.textContent = name;
                return;
            }

            Swal.fire('Erro', res?.error?.message || res?.error || 'Não foi possível renomear a coluna.', 'error');
        } catch {
            Swal.fire('Erro', 'Falha na comunicação.', 'error');
        }
    };

    els.forEach((el) => {
        el.addEventListener('dblclick', () => open(el));
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                open(el);
            }
        });
    });
}

// ── Company users ─────────────────────────────────────────────────
async function loadCompanyUsers() {
    try {
        companyUsers = await API.get('/api/users');
    } catch {
        companyUsers = [];
    }
    populateUserSelects();
}

function userById(id) {
    return companyUsers.find(u => u.id == id) ?? null;
}

function userInitials(name) {
    const p = name.trim().split(/\s+/);
    return (p[0][0] + (p[1]?.[0] ?? '')).toUpperCase();
}

function populateUserSelects() {
    const opts = '<option value="">— Nenhum —</option>' +
        companyUsers.map(u => `<option value="${u.id}">${escHtml(u.name)}</option>`).join('');

    ['create-assignee', 'meta-assignee'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = opts;
    });
}

// ── Quill initialisation ─────────────────────────────────────────
function initQuill() {
    if (typeof Quill === 'undefined') return;

    const toolbar = [
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link', 'image'],
        ['clean'],
    ];

    // Create-modal editor
    const createEl = document.getElementById('create-desc-editor');
    if (createEl) {
        quillCreate = new Quill(createEl, {
            theme: 'snow',
            placeholder: 'Descreva a tarefa com detalhes…',
            modules: { toolbar },
        });
    }

    // Detail-modal editor (lazy — element exists but Quill mounts once)
    const detailEl = document.getElementById('modal-desc-editor');
    if (detailEl) {
        quillDetail = new Quill(detailEl, {
            theme: 'snow',
            placeholder: 'Escreva uma descrição…',
            modules: { toolbar },
        });
    }
}

// ── API Helper ───────────────────────────────────────────────────
const API = {
    base() {
        return window.location.pathname.split('index.php')[0] + 'index.php';
    },

    async get(path) {
        const r = await fetch(this.base() + path);
        return r.json();
    },

    async post(path, body) {
        const r = await fetch(this.base() + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify(body),
        });
        return r.json();
    },

    async patch(path, body) {
        const r = await fetch(this.base() + path, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify(body),
        });
        return r.json();
    },

    async del(path) {
        const r = await fetch(this.base() + path, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        });
        return r.json();
    },

    async upload(path, formData) {
        const r = await fetch(this.base() + path, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: formData,
        });
        return r.json();
    },
};

// ── CSRF ─────────────────────────────────────────────────────────
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function htmlToText(html) {
    if (!html) return '';
    const raw = String(html);
    if (typeof DOMParser !== 'undefined') {
        const p1 = new DOMParser().parseFromString(raw, 'text/html');
        const t1 = String(p1.body?.textContent ?? '');
        if (t1.includes('<') && t1.includes('>')) {
            const p2 = new DOMParser().parseFromString(t1, 'text/html');
            const t2 = String(p2.body?.textContent ?? '');
            return t2.replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
        }
        return t1.replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
    }
    return raw.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

// ── Fetch & Render all tasks ─────────────────────────────────────
async function fetchAllTasks() {
    const columns    = document.querySelectorAll('[data-column-id]');
    for (const col of columns) {
        const colId    = col.dataset.columnId;
        const taskList = col.querySelector('.task-list');
        try {
            const tasks = await API.get(`/api/tasks?column_id=${colId}`);
            taskList.innerHTML = '';
            if (Array.isArray(tasks)) {
                tasks.forEach(t => renderTask(t, taskList));
            }
        } catch (e) {
            console.error('Erro ao buscar tarefas col=' + colId, e);
        }
    }
    updateCounts();
}

// ── Card rendering ───────────────────────────────────────────────
function renderTask(task, container) {
    const card = document.createElement('div');
    card.className = 'task-card group';
    card.draggable  = true;
    card.dataset.taskId = task.id;

    const p  = getPriorityConfig(task.priority);
    const sp = task.story_points ? `<span class="story-points-badge">${task.story_points} SP</span>` : '';

    const labelsHtml = (task.labels || []).slice(0, 4).map(l =>
        `<span class="label-pill" style="background:${hexToLight(l.color)};color:${l.color};border-color:${l.color}33">${escHtml(l.name)}</span>`
    ).join('');

    card.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <span class="priority-badge ${p.cls}">${p.label}</span>
            ${sp}
        </div>
        <h4 class="font-outfit font-bold text-slate-800 text-sm mb-1.5 leading-snug group-hover:text-indigo-600 transition-colors">${escHtml(task.title)}</h4>
        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed mb-3">${escHtml(htmlToText(task.description || ''))}</p>
        ${labelsHtml ? `<div class="flex flex-wrap gap-1 mb-3">${labelsHtml}</div>` : ''}
        <div class="mt-auto pt-3 border-t border-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                ${task.assigned_to
                    ? (() => { const u = userById(task.assigned_to); return u
                        ? `<div class="h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[9px] font-bold shadow-sm" title="${escHtml(u.name)}">${userInitials(u.name)}</div>`
                        : `<div class="h-6 w-6 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[9px] font-bold shadow-sm">#${task.assigned_to}</div>`; })()
                    : `<div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-400"><i data-lucide="user" class="w-3.5 h-3.5"></i></div>`
                }
                ${task.deadline ? `<span class="text-[10px] text-slate-500 font-medium" data-field="deadline">${formatDate(task.deadline)}</span>` : ''}
            </div>
            <div class="flex items-center gap-2 text-slate-400 group-hover:text-indigo-500 transition-smooth">
                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                <span class="text-[10px] font-bold">0</span>
            </div>
        </div>`;

    card.addEventListener('click', () => openModal(task));
    container.appendChild(card);
    lucide.createIcons();
}

// ── Priority config ──────────────────────────────────────────────
function getPriorityConfig(p) {
    switch ((p || '').toLowerCase()) {
        case 'critical': return { label: 'CRÍTICA', cls: 'priority-critical' };
        case 'high':     return { label: 'ALTA',    cls: 'priority-high' };
        case 'medium':   return { label: 'MÉDIA',   cls: 'priority-medium' };
        default:         return { label: 'BAIXA',   cls: 'priority-low' };
    }
}

// ── Column counts ────────────────────────────────────────────────
function updateCounts() {
    document.querySelectorAll('[data-column-id]').forEach(col => {
        const n  = col.querySelectorAll('.task-card').length;
        const el = col.querySelector('.count');
        if (el) el.textContent = n;
    });
}

// ══════════════════════════════════════════════════════════════════
//  TASK DETAIL MODAL
// ══════════════════════════════════════════════════════════════════

async function openModal(task) {
    currentTaskId = task.id;

    // Populate static header fields
    document.getElementById('modal-title').textContent = task.title;
    document.getElementById('modal-desc').innerHTML = task.description || '<span class="text-slate-500">—</span>';

    // Badges row
    const p = getPriorityConfig(task.priority);
    document.getElementById('modal-badges').innerHTML =
        `<span class="priority-badge ${p.cls}">${p.label}</span>` +
        (task.story_points ? `<span class="story-points-badge">${task.story_points} SP</span>` : '');

    // Meta sidebar
    document.getElementById('meta-sp').textContent = task.story_points ? task.story_points + ' SP' : '—';

    // Priority select
    const prioritySelect = document.getElementById('meta-priority-select');
    if (prioritySelect) prioritySelect.value = task.priority || 'medium';

    // Deadline input (expects YYYY-MM-DD)
    const deadlineInput  = document.getElementById('meta-deadline-input');
    const clearDeadlineBtn = document.getElementById('clear-deadline-btn');
    if (deadlineInput) deadlineInput.value = task.deadline ? task.deadline.substring(0, 10) : '';
    if (clearDeadlineBtn) clearDeadlineBtn.classList.toggle('hidden', !task.deadline);

    // Populate assignee select and set current value
    populateUserSelects();
    const assigneeEl = document.getElementById('meta-assignee');
    if (assigneeEl) assigneeEl.value = task.assigned_to ?? '';

    // Show modal immediately, load sections async
    document.getElementById('task-modal').classList.remove('hidden');

    // Reset dynamic sections
    document.getElementById('checklists-container').innerHTML  = '<div class="text-xs text-slate-400">Carregando…</div>';
    document.getElementById('attachments-container').innerHTML = '<div class="text-xs text-slate-400">Carregando…</div>';
    document.getElementById('comments-container').innerHTML    = '<div class="text-xs text-slate-400">Carregando…</div>';
    document.getElementById('labels-container').innerHTML      = '';
    document.getElementById('deps-blocked-by').innerHTML       = '';
    document.getElementById('deps-blocking').innerHTML         = '';
    document.getElementById('history-container').innerHTML     = '';

    // Load all sections in parallel
    await Promise.all([
        loadLabels(task.id),
        loadChecklists(task.id),
        loadAttachments(task.id),
        loadComments(task.id),
        loadDependencies(task.id),
    ]);
}

function toggleDescEditor(open = true) {
    document.getElementById('modal-desc').classList.toggle('hidden', open);
    document.getElementById('desc-editor-wrap').classList.toggle('hidden', !open);

    if (open && quillDetail) {
        // Load current HTML into Quill
        const current = document.getElementById('modal-desc').innerHTML;
        quillDetail.root.innerHTML = current === '<span class="text-slate-500">—</span>' ? '' : current;
        quillDetail.focus();
    }
}

async function saveDescription() {
    if (!currentTaskId || !quillDetail) return;

    const html = quillDetail.root.innerHTML;
    const btn  = document.getElementById('save-desc-btn');
    if (btn) { btn.disabled = true; btn.textContent = 'Salvando…'; }

    try {
        const res = await API.patch(`/api/tasks?id=${currentTaskId}`, { description: html });
        if (res.id ?? res.ok ?? !res.error) {
            document.getElementById('modal-desc').innerHTML = html || '<span class="text-slate-500">—</span>';
            toggleDescEditor(false);
        } else {
            Swal.fire('Erro', res.error?.message || 'Não foi possível salvar.', 'error');
        }
    } catch {
        Swal.fire('Erro', 'Falha na comunicação.', 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Salvar'; }
    }
}

async function updateAssignee(userId) {
    if (!currentTaskId) return;
    await API.patch(`/api/tasks?id=${currentTaskId}`, {
        assigned_to: userId ? parseInt(userId, 10) : null,
    });
    // Refresh the card on the board to show updated avatar
    await fetchAllTasks();
}

// ── Inline title editing ──────────────────────────────────────────
function startTitleEdit() {
    const title = document.getElementById('modal-title').textContent.trim();
    document.getElementById('modal-title-input').value = title;
    document.getElementById('modal-title-wrap').classList.add('hidden');
    document.getElementById('modal-title-edit').classList.remove('hidden');
    const input = document.getElementById('modal-title-input');
    input.focus();
    input.select();
}

function cancelTitleEdit() {
    const editEl = document.getElementById('modal-title-edit');
    const wrapEl = document.getElementById('modal-title-wrap');
    if (editEl) editEl.classList.add('hidden');
    if (wrapEl) wrapEl.classList.remove('hidden');
}

function handleTitleKey(e) {
    if (e.key === 'Enter') { e.preventDefault(); saveTitle(); }
    if (e.key === 'Escape') cancelTitleEdit();
}

async function saveTitle() {
    if (!currentTaskId) return;
    const input = document.getElementById('modal-title-input');
    const title = input.value.trim();
    if (!title) { input.focus(); return; }

    const saveBtn = document.querySelector('#modal-title-edit button');
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Salvando…'; }

    try {
        const res = await API.patch(`/api/tasks?id=${currentTaskId}`, { title });
        if (res.id ?? res.ok ?? !res.error) {
            document.getElementById('modal-title').textContent = title;
            cancelTitleEdit();
            // Update card on board without full re-fetch
            const card = document.querySelector(`[data-task-id="${currentTaskId}"]`);
            if (card) {
                const h4 = card.querySelector('h4');
                if (h4) h4.textContent = title;
            }
        } else {
            Swal.fire('Erro', res.error?.message || 'Não foi possível salvar.', 'error');
        }
    } catch {
        Swal.fire('Erro', 'Falha na comunicação.', 'error');
    } finally {
        if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Salvar'; }
    }
}

// ── Inline priority editing ───────────────────────────────────────
async function updatePriority(value) {
    if (!currentTaskId) return;
    try {
        const res = await API.patch(`/api/tasks?id=${currentTaskId}`, { priority: value });
        if (res.id ?? res.ok ?? !res.error) {
            const p = getPriorityConfig(value);
            // Update header badges (preserve story points badge)
            const badges = document.getElementById('modal-badges');
            const sp     = badges?.querySelector('.story-points-badge');
            if (badges) badges.innerHTML = `<span class="priority-badge ${p.cls}">${p.label}</span>` + (sp ? sp.outerHTML : '');
            // Update card on board without full re-fetch
            const card = document.querySelector(`[data-task-id="${currentTaskId}"]`);
            if (card) {
                const badge = card.querySelector('.priority-badge');
                if (badge) { badge.className = `priority-badge ${p.cls}`; badge.textContent = p.label; }
            }
        } else {
            Swal.fire('Erro', res.error?.message || 'Não foi possível atualizar.', 'error');
        }
    } catch {
        Swal.fire('Erro', 'Falha na comunicação.', 'error');
    }
}

// ── Inline deadline editing ───────────────────────────────────────
async function updateDeadline(value) {
    if (!currentTaskId) return;
    try {
        const res = await API.patch(`/api/tasks?id=${currentTaskId}`, { deadline: value || null });
        if (res.id ?? res.ok ?? !res.error) {
            const clearBtn = document.getElementById('clear-deadline-btn');
            if (clearBtn) clearBtn.classList.toggle('hidden', !value);
            // Update card on board
            const card = document.querySelector(`[data-task-id="${currentTaskId}"]`);
            if (card) {
                let dateSpan = card.querySelector('[data-field="deadline"]');
                if (value) {
                    if (dateSpan) {
                        dateSpan.textContent = formatDate(value);
                    } else {
                        // Add deadline span if it didn't exist before
                        const footer = card.querySelector('.flex.items-center.gap-1\\.5');
                        if (footer) {
                            const span = document.createElement('span');
                            span.className = 'text-[10px] text-slate-400';
                            span.dataset.field = 'deadline';
                            span.textContent = formatDate(value);
                            footer.appendChild(span);
                        }
                    }
                } else {
                    dateSpan?.remove();
                }
            }
        } else {
            Swal.fire('Erro', res.error?.message || 'Não foi possível atualizar.', 'error');
        }
    } catch {
        Swal.fire('Erro', 'Falha na comunicação.', 'error');
    }
}

async function clearDeadline() {
    document.getElementById('meta-deadline-input').value = '';
    await updateDeadline('');
}

function closeModal() {
    document.getElementById('task-modal').classList.add('hidden');
    toggleDescEditor(false);
    cancelTitleEdit();
    currentTaskId = null;
}

// ── Labels ───────────────────────────────────────────────────────
async function loadLabels(taskId) {
    const container = document.getElementById('labels-container');
    const select    = document.getElementById('label-select');

    // Fetch task labels and all company labels in parallel
    const [taskLabels, allLabels] = await Promise.all([
        API.get(`/api/labels?company_id=0`).catch(() => []), // placeholder – overridden below
        currentCompanyId > 0
            ? API.get(`/api/labels?company_id=${currentCompanyId}`)
            : Promise.resolve([]),
    ]);

    // Reload task-specific labels via task endpoint (labels are already in task.labels from renderTask)
    // For fresh load, fetch the full task
    const fullTask = await API.get(`/api/tasks?column_id=0`).catch(() => null);
    // Actually we can't easily get a single task. Use the labels already on the task object in openModal.
    // The labels param passed from renderTask click is stale after ops. Re-fetch via findByColumnId is heavy.
    // Best: store task labels in data-attribute on card, or rely on current task.labels from click payload.
    // For simplicity: render the labels from the most recent API call for task labels.
    // We'll use the company labels list and cross-reference task_labels table via the task endpoint workaround.
    // Since we have GET /api/labels?task_id not implemented, we use the data already on the card's task object.

    // Build select with company labels
    select.innerHTML = allLabels.map
        ? allLabels.map(l => `<option value="${l.id}" data-color="${l.color}">${escHtml(l.name)}</option>`).join('')
        : '';

    // Render task's labels (from original task payload passed to openModal – stored in card data)
    const card = document.querySelector(`[data-task-id="${taskId}"]`);
    const taskLabelData = card?._taskLabels || [];
    renderLabelPills(taskLabelData);
}

function renderLabelPills(labels) {
    const container = document.getElementById('labels-container');
    container.innerHTML = labels.map(l => `
        <span class="label-pill" style="background:${hexToLight(l.color)};color:${l.color};border-color:${l.color}33">
            ${escHtml(l.name)}
            <button onclick="detachLabel(${l.id})" class="ml-1 opacity-60 hover:opacity-100 leading-none">×</button>
        </span>`).join('');
}

async function attachLabel() {
    const sel = document.getElementById('label-select');
    if (!sel.value || !currentTaskId) return;

    const res = await API.post('/api/task-labels', { task_id: currentTaskId, label_id: parseInt(sel.value) });
    if (!res.error) await loadLabels(currentTaskId);
}

async function detachLabel(labelId) {
    if (!currentTaskId) return;
    await API.del(`/api/task-labels?task_id=${currentTaskId}&label_id=${labelId}`);
    await loadLabels(currentTaskId);
}

// ── Checklists ───────────────────────────────────────────────────
async function loadChecklists(taskId) {
    const container = document.getElementById('checklists-container');
    const data = await API.get(`/api/checklists?task_id=${taskId}`);
    container.innerHTML = '';

    if (!Array.isArray(data) || data.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-400">Nenhum checklist ainda.</p>';
        return;
    }

    data.forEach(cl => renderChecklist(cl, container));
    lucide.createIcons();
}

function renderChecklist(cl, container) {
    const done  = (cl.items || []).filter(i => i.is_done).length;
    const total = (cl.items || []).length;
    const pct   = total > 0 ? Math.round((done / total) * 100) : 0;

    const div = document.createElement('div');
    div.className = 'checklist-block';
    div.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-700">${escHtml(cl.title)}</span>
            <span class="text-[10px] text-slate-500">${done}/${total}</span>
        </div>
        <div class="w-full bg-slate-100 h-1.5 rounded-full mb-3">
            <div class="h-full bg-indigo-500 rounded-full transition-all" style="width:${pct}%"></div>
        </div>
        <div class="space-y-2 checklist-items" data-checklist-id="${cl.id}">
            ${(cl.items || []).map(item => renderChecklistItem(item)).join('')}
        </div>
        <div class="flex gap-2 mt-3">
            <input type="text" placeholder="Novo item…" data-cl-id="${cl.id}"
                   class="flex-1 h-8 px-3 bg-slate-50 border border-slate-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-100"
                   onkeydown="if(event.key==='Enter'){addChecklistItem(${cl.id}, this);}">
            <button onclick="addChecklistItem(${cl.id}, this.previousElementSibling)"
                    class="px-3 h-8 bg-slate-100 hover:bg-indigo-100 text-slate-600 rounded-xl text-xs font-bold transition-colors">
                Adicionar
            </button>
        </div>`;

    container.appendChild(div);
}

function renderChecklistItem(item) {
    return `
        <div class="flex items-center gap-2 checklist-item group/item" data-item-id="${item.id}">
            <input type="checkbox" ${item.is_done ? 'checked' : ''}
                   onchange="toggleItem(${item.id}, this.checked)"
                   class="w-4 h-4 rounded border-slate-300 accent-indigo-600 cursor-pointer flex-shrink-0 transition-smooth">
            <span class="text-xs flex-1 ${item.is_done ? 'line-through text-slate-400' : 'text-slate-700'} transition-smooth">${escHtml(item.body)}</span>
            <button onclick="deleteChecklistItem(${item.id}, this.closest('.checklist-item'))"
                    class="opacity-0 group-hover/item:opacity-100 text-slate-300 hover:text-rose-500 transition-smooth">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>`;
}

async function addChecklist() {
    if (!currentTaskId) return;
    const title = await Swal.fire({
        title: 'Nome do Checklist',
        input: 'text', inputPlaceholder: 'Ex: Critérios de aceite',
        showCancelButton: true, confirmButtonText: 'Criar',
        cancelButtonText: 'Cancelar',
    });
    if (!title.value) return;
    await API.post('/api/checklists', { task_id: currentTaskId, title: title.value });
    await loadChecklists(currentTaskId);
}

async function addChecklistItem(checklistId, input) {
    const body = input.value.trim();
    if (!body) return;
    input.value = '';
    await API.post('/api/checklist-items', { checklist_id: checklistId, body });
    await loadChecklists(currentTaskId);
}

async function toggleItem(itemId, isDone) {
    await API.patch('/api/checklist-items', { id: itemId, is_done: isDone });
    await loadChecklists(currentTaskId);
}

async function deleteChecklistItem(itemId, el) {
    el?.remove();
    await API.del(`/api/checklist-items?id=${itemId}`);
}

// ── Attachments ──────────────────────────────────────────────────
async function loadAttachments(taskId) {
    const container = document.getElementById('attachments-container');
    const data = await API.get(`/api/attachments?task_id=${taskId}`);
    container.innerHTML = '';

    if (!Array.isArray(data) || data.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-400">Nenhum anexo ainda.</p>';
        return;
    }

    data.forEach(a => {
        const row = document.createElement('div');
        row.className = 'attachment-row';
        const isImage  = a.mime_type && a.mime_type.startsWith('image/');
        const fileUrl  = attachmentPublicUrl(a.filepath);

        if (isImage) {
            row.innerHTML = `
                <a href="${fileUrl}" target="_blank" rel="noopener" class="flex-shrink-0" title="${escHtml(a.filename)}">
                    <img src="${fileUrl}" alt="${escHtml(a.filename)}"
                         class="attachment-thumb"
                         onerror="this.style.display='none'">
                </a>
                <div class="flex flex-col flex-1 min-w-0">
                    <a href="${fileUrl}" target="_blank" rel="noopener"
                       class="text-xs text-indigo-600 hover:underline truncate font-medium">${escHtml(a.filename)}</a>
                    <span class="text-[10px] text-slate-500">${formatBytes(a.size_bytes)}</span>
                </div>
                <button onclick="deleteAttachment(${a.id}, this.closest('.attachment-row'))"
                        class="text-slate-400 hover:text-rose-500 transition-smooth ml-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>`;
        } else {
            row.innerHTML = `
                <i data-lucide="${mimeIcon(a.mime_type)}" class="w-5 h-5 text-slate-400"></i>
                <a href="${fileUrl}" target="_blank" rel="noopener"
                   class="text-xs flex-1 truncate text-indigo-600 hover:underline font-medium">${escHtml(a.filename)}</a>
                <span class="text-[10px] text-slate-500">${formatBytes(a.size_bytes)}</span>
                <button onclick="deleteAttachment(${a.id}, this.closest('.attachment-row'))"
                        class="text-slate-400 hover:text-rose-500 transition-smooth ml-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>`;
        }
        container.appendChild(row);
    });
    lucide.createIcons();
}

/** Build the public URL for an uploaded file from its relative filepath. */
function attachmentPublicUrl(filepath) {
    // API.base() => e.g. "/kanban/index.php" — strip index.php to get web root
    const base = API.base().replace(/index\.php$/, '');
    return base + 'uploads/' + filepath;
}

function initAttachmentDropzone() {
    const zone  = document.getElementById('attachment-dropzone');
    const input = document.getElementById('attachment-input');
    if (!zone || !input) return;

    zone.addEventListener('click', () => input.click());

    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-active'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-active'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-active');
        uploadFiles(e.dataTransfer.files);
    });

    input.addEventListener('change', () => uploadFiles(input.files));
}

async function uploadFiles(fileList) {
    if (!currentTaskId || !fileList.length) return;
    for (const file of fileList) {
        const fd = new FormData();
        fd.append('file', file);
        const res = await API.upload(`/api/attachments?task_id=${currentTaskId}`, fd);
        if (res.error) {
            Swal.fire('Erro no upload', res.error.message, 'error');
        }
    }
    await loadAttachments(currentTaskId);
}

async function deleteAttachment(attachId, el) {
    el?.remove();
    await API.del(`/api/attachments?id=${attachId}`);
}

// ── Comments ─────────────────────────────────────────────────────
async function loadComments(taskId) {
    const container = document.getElementById('comments-container');
    const data = await API.get(`/api/comments?task_id=${taskId}`);
    container.innerHTML = '';

    if (!Array.isArray(data) || data.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-400">Nenhum comentário ainda.</p>';
        return;
    }

    data.forEach(c => {
        const div = document.createElement('div');
        div.className = 'comment-block';
        div.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[14px] text-indigo-600">person</span>
                </div>
                <div class="flex-1 bg-slate-50 rounded-2xl p-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-bold text-slate-500">#${c.user_id}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-500">${formatDate(c.created_at)}</span>
                            <button onclick="deleteComment(${c.id}, this.closest('.comment-block'))"
                                    class="text-slate-400 hover:text-rose-500 transition-colors">
                                <span class="material-symbols-outlined text-[14px]">delete</span>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed">${escHtml(c.body)}</p>
                </div>
            </div>`;
        container.appendChild(div);
    });
}

async function submitComment() {
    if (!currentTaskId) return;
    const input = document.getElementById('comment-input');
    const body  = input.value.trim();
    if (!body) return;

    const res = await API.post('/api/comments', { task_id: currentTaskId, body });
    if (!res.error) {
        input.value = '';
        await loadComments(currentTaskId);
    }
}

async function deleteComment(commentId, el) {
    el?.remove();
    await API.del(`/api/comments?id=${commentId}`);
}

// ── Dependencies ─────────────────────────────────────────────────
async function loadDependencies(taskId) {
    const data = await API.get(`/api/dependencies?task_id=${taskId}`);
    renderDepList('deps-blocked-by', data.blocked_by || [], 'remove', taskId);
    renderDepList('deps-blocking',   data.blocking   || [], 'remove', taskId);
}

function renderDepList(containerId, items, action, taskId) {
    const el = document.getElementById(containerId);
    if (!items.length) { el.innerHTML = '<span class="text-slate-400">—</span>'; return; }
    el.innerHTML = items.map(t => `
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 flex-shrink-0"></span>
            <span class="flex-1 truncate">#${t.id} ${escHtml(t.title)}</span>
            <button onclick="removeDependency(${taskId}, ${t.id})" class="text-slate-400 hover:text-rose-500">
                <span class="material-symbols-outlined text-[12px]">link_off</span>
            </button>
        </div>`).join('');
}

async function addDependency() {
    if (!currentTaskId) return;
    const input = document.getElementById('dep-task-input');
    const depId = parseInt(input.value, 10);
    if (!depId) return;

    const res = await API.post('/api/dependencies', { task_id: currentTaskId, depends_on_id: depId });
    if (res.error) {
        Swal.fire('Erro', res.error.message, 'warning');
    } else {
        input.value = '';
        await loadDependencies(currentTaskId);
    }
}

async function removeDependency(taskId, dependsOnId) {
    await API.del(`/api/dependencies?task_id=${taskId}&depends_on_id=${dependsOnId}`);
    await loadDependencies(currentTaskId);
}

// ══════════════════════════════════════════════════════════════════
//  CREATE TASK MODAL
// ══════════════════════════════════════════════════════════════════

function openCreateModal(colId = null) {
    const modal    = document.getElementById('create-task-modal');
    const colInput = document.getElementById('create-task-col-id');
    if (!modal) return;

    if (!colId) {
        colId = document.querySelector('[data-column-id]')?.dataset.columnId || null;
    }
    if (colInput) colInput.value = colId;
    modal.classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('create-task-modal')?.classList.add('hidden');
    if (quillCreate) quillCreate.setContents([]);
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('create-task-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const data = Object.fromEntries(new FormData(form).entries());

        // Cast / clean numeric fields
        if (!data.story_points) delete data.story_points;
        else data.story_points = parseInt(data.story_points, 10);

        if (!data.assigned_to) delete data.assigned_to;
        else data.assigned_to = parseInt(data.assigned_to, 10);

        // Inject Quill HTML into the hidden description field
        if (quillCreate) {
            data.description = quillCreate.root.innerHTML;
        }

        const btn = form.querySelector('button[type="submit"]');
        btn.disabled    = true;
        btn.textContent = 'Criando…';

        try {
            const res = await API.post('/api/tasks', data);
            if (res.id) {
                Swal.fire({ icon: 'success', title: 'Tarefa criada!', timer: 1200, showConfirmButton: false });
                closeCreateModal();
                form.reset();
                await fetchAllTasks();
            } else {
                Swal.fire('Erro', res.error?.message || 'Erro ao criar.', 'error');
            }
        } catch {
            Swal.fire('Erro', 'Falha na comunicação.', 'error');
        } finally {
            btn.disabled    = false;
            btn.textContent = 'Criar Tarefa';
        }
    });
});

// ══════════════════════════════════════════════════════════════════
//  DRAG & DROP
// ══════════════════════════════════════════════════════════════════

function initDragAndDrop() {
    const board = document.getElementById('kanban-board');

    board.addEventListener('dragstart', e => {
        if (!e.target.classList.contains('task-card')) return;
        e.target.classList.add('dragging');
        e.target.style.opacity = '0.5';
    });

    board.addEventListener('dragend', e => {
        if (!e.target.classList.contains('task-card')) return;
        e.target.classList.remove('dragging');
        e.target.style.opacity = '1';
    });

    document.querySelectorAll('.task-list').forEach(list => {
        list.addEventListener('dragover', e => {
            e.preventDefault();
            const dragging = document.querySelector('.dragging');
            if (!dragging) return;
            const after = getDragAfterElement(list, e.clientY);
            if (!after) list.appendChild(dragging);
            else list.insertBefore(dragging, after);
        });

        list.addEventListener('drop', async e => {
            e.preventDefault();
            const task  = document.querySelector('.dragging');
            if (!task) return;

            const taskId     = task.dataset.taskId;
            const toColumnId = list.closest('[data-column-id]').dataset.columnId;
            const tasks      = [...list.querySelectorAll('.task-card')];
            const toPosition = tasks.indexOf(task) + 1;

            task.classList.remove('dragging');

            try {
                const res = await API.post('/api/tasks/move', { id: taskId, to_column_id: toColumnId, to_position: toPosition });
                if (!res.ok) throw new Error();
                updateCounts();
            } catch {
                Swal.fire({ icon: 'error', title: 'Falha ao mover card', timer: 2000, showConfirmButton: false });
            }
        });
    });
}

function getDragAfterElement(container, y) {
    return [...container.querySelectorAll('.task-card:not(.dragging)')]
        .reduce((closest, child) => {
            const box    = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset) ? { offset, element: child } : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// ── Utility helpers ──────────────────────────────────────────────
function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatDate(raw) {
    if (!raw) return '';
    try { return new Date(raw).toLocaleDateString('pt-BR', { day:'2-digit', month:'short', year:'numeric' }); }
    catch { return raw; }
}

function formatBytes(b) {
    if (b < 1024)        return b + ' B';
    if (b < 1048576)     return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(1) + ' MB';
}

function hexToLight(hex) {
    // Returns a very light tint of a hex color for label backgrounds
    const r = parseInt(hex.slice(1,3),16);
    const g = parseInt(hex.slice(3,5),16);
    const b = parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},0.1)`;
}

function mimeIcon(mime) {
    if (!mime) return 'attach_file';
    if (mime.startsWith('image/'))       return 'image';
    if (mime === 'application/pdf')      return 'picture_as_pdf';
    if (mime.includes('word'))           return 'description';
    if (mime.startsWith('text/'))        return 'article';
    return 'attach_file';
}
