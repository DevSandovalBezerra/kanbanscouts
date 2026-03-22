<div class="h-full p-8 space-y-10 max-w-5xl mx-auto">
    <!-- Header -->
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Membros do Projeto</h2>
                    <div class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-indigo-100">
                        <?php echo htmlspecialchars($project_name ?? 'Projeto'); ?>
                    </div>
                </div>
                <p class="text-slate-500 text-base">Gerencie quem tem acesso e qual papel cada membro possui.</p>
            </div>
            <button id="btnAddMember" onclick="openAddModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-all shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95 hidden">
                <span class="material-symbols-outlined text-xl">person_add</span>
                Adicionar Membro
            </button>
        </div>
    </section>

    <!-- Toast -->
    <div id="toast" class="fixed top-6 right-6 z-50 hidden px-5 py-3 rounded-2xl text-sm font-semibold shadow-lg"></div>

    <!-- Members Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-6 py-4 font-semibold">Usuário</th>
                    <th class="text-left px-6 py-4 font-semibold">Papel</th>
                    <th class="text-left px-6 py-4 font-semibold">Convidado em</th>
                    <th class="text-right px-6 py-4 font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody id="membersTableBody">
                <tr><td colspan="4" class="text-center py-12 text-slate-400">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Member -->
<div id="addModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/30 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md p-8 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="font-outfit text-xl font-bold text-slate-900">Adicionar Membro</h3>
            <button onclick="closeModal('addModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form onsubmit="submitAddMember(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Usuário</label>
                <select id="addUserId" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Selecione um usuário...</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Papel</label>
                <select id="addRole" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="editor">Editor — pode criar e mover tarefas</option>
                    <option value="viewer">Viewer — somente leitura</option>
                </select>
            </div>
            <p id="addError" class="text-rose-600 text-xs hidden"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-semibold text-sm transition-colors">Adicionar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Change Role -->
<div id="roleModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/30 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md p-8 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="font-outfit text-xl font-bold text-slate-900">Alterar Papel</h3>
            <button onclick="closeModal('roleModal')" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form onsubmit="submitChangeRole(event)" class="space-y-4">
            <input type="hidden" id="roleMembershipId" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Novo papel</label>
                <select id="roleSelect" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="owner">Owner</option>
                    <option value="manager">Manager</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>
            <p id="roleError" class="text-rose-600 text-xs hidden"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('roleModal')" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-semibold text-sm transition-colors">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE       = '<?php echo $app_url ?? ''; ?>';
const PROJECT_ID = <?php echo (int) ($project_id ?? 0); ?>;
let members      = [];
let myRole       = null;

const ROLE_LABELS = { owner: 'Owner', manager: 'Manager', editor: 'Editor', viewer: 'Viewer' };
const ROLE_COLORS = {
    owner:   'bg-rose-50 text-rose-600',
    manager: 'bg-amber-50 text-amber-600',
    editor:  'bg-indigo-50 text-indigo-600',
    viewer:  'bg-slate-100 text-slate-500',
};

async function loadMembers() {
    const res = await fetch(`${BASE}/api/project-members?project_id=${PROJECT_ID}`);
    if (!res.ok) { document.getElementById('membersTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-12 text-rose-400">Erro ao carregar membros.</td></tr>'; return; }
    members = await res.json();

    // Detect current user's role
    const meRes = await fetch(`${BASE}/api/auth/me`);
    if (meRes.ok) {
        const me = await meRes.json();
        const myId = me?.user?.id ?? me?.id ?? null;
        const myMembership = members.find(m => m.user_id == myId);
        myRole = myMembership ? myMembership.role_in_project : null;
    }

    // Show "Add Member" button only for manager+
    if (myRole === 'owner' || myRole === 'manager') {
        document.getElementById('btnAddMember').classList.remove('hidden');
    }

    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('membersTableBody');
    if (!members.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-12 text-slate-400">Nenhum membro encontrado.</td></tr>';
        return;
    }
    tbody.innerHTML = members.map(m => `
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        ${esc(initials(m.user_name || '?'))}
                    </div>
                    <div>
                        <div class="font-semibold text-slate-900">${esc(m.user_name || '—')}</div>
                        <div class="text-xs text-slate-400">${esc(m.user_email || '')}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold ${ROLE_COLORS[m.role_in_project] || 'bg-slate-100 text-slate-500'}">
                    ${ROLE_LABELS[m.role_in_project] || m.role_in_project}
                </span>
            </td>
            <td class="px-6 py-4 text-slate-400 text-xs">${m.accepted_at ? m.accepted_at.substring(0, 10) : '—'}</td>
            <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    ${myRole === 'owner' ? `
                    <button onclick="openRoleModal(${m.id}, '${m.role_in_project}')" class="p-2 rounded-xl hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-colors" title="Alterar papel">
                        <span class="material-symbols-outlined text-lg">manage_accounts</span>
                    </button>
                    <button onclick="removeMember(${m.id}, '${esc(m.user_name || '')}')" class="p-2 rounded-xl hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors" title="Remover">
                        <span class="material-symbols-outlined text-lg">person_remove</span>
                    </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function initials(name) {
    return name.split(' ').slice(0, 2).map(w => w[0]?.toUpperCase() || '').join('');
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `fixed top-6 right-6 z-50 px-5 py-3 rounded-2xl text-sm font-semibold shadow-lg ${isError ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'}`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3500);
}

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

async function openAddModal() {
    // Load available users (company members not yet in project)
    const res = await fetch(`${BASE}/api/users`);
    const all = res.ok ? await res.json() : [];
    const memberIds = new Set(members.map(m => m.user_id));
    const available = all.filter(u => !memberIds.has(u.id));

    const sel = document.getElementById('addUserId');
    sel.innerHTML = '<option value="">Selecione um usuário...</option>' +
        available.map(u => `<option value="${u.id}">${esc(u.name)} — ${esc(u.email)}</option>`).join('');
    document.getElementById('addError').classList.add('hidden');
    document.getElementById('addModal').classList.remove('hidden');
}

async function submitAddMember(e) {
    e.preventDefault();
    const userId = parseInt(document.getElementById('addUserId').value);
    const role   = document.getElementById('addRole').value;
    const errEl  = document.getElementById('addError');

    if (!userId) { errEl.textContent = 'Selecione um usuário.'; errEl.classList.remove('hidden'); return; }

    const res  = await fetch(`${BASE}/api/project-members`, {
        method: 'POST',
        headers: {'Content-Type':'application/json', 'X-CSRF-Token': getCsrfToken()},
        body: JSON.stringify({ project_id: PROJECT_ID, user_id: userId, role_in_project: role })
    });
    const data = await res.json();

    if (!res.ok) { errEl.textContent = data?.error?.message || 'Erro ao adicionar.'; errEl.classList.remove('hidden'); return; }

    closeModal('addModal');
    showToast('Membro adicionado!');
    loadMembers();
}

function openRoleModal(id, currentRole) {
    document.getElementById('roleMembershipId').value = id;
    document.getElementById('roleSelect').value = currentRole;
    document.getElementById('roleError').classList.add('hidden');
    document.getElementById('roleModal').classList.remove('hidden');
}

async function submitChangeRole(e) {
    e.preventDefault();
    const id     = document.getElementById('roleMembershipId').value;
    const role   = document.getElementById('roleSelect').value;
    const errEl  = document.getElementById('roleError');

    const res  = await fetch(`${BASE}/api/project-members?id=${id}`, {
        method: 'PATCH',
        headers: {'Content-Type':'application/json', 'X-CSRF-Token': getCsrfToken()},
        body: JSON.stringify({ role_in_project: role })
    });
    const data = await res.json();

    if (!res.ok) { errEl.textContent = data?.error?.message || 'Erro ao alterar papel.'; errEl.classList.remove('hidden'); return; }

    closeModal('roleModal');
    showToast('Papel atualizado!');
    loadMembers();
}

async function removeMember(id, name) {
    if (!confirm(`Remover "${name}" do projeto?`)) return;

    const res  = await fetch(`${BASE}/api/project-members?id=${id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-Token': getCsrfToken()}
    });
    const data = await res.json();

    if (!res.ok) { showToast(data?.error?.message || 'Erro ao remover.', true); return; }
    showToast('Membro removido.');
    loadMembers();
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    if (meta) return meta;
    return document.cookie.split(';').map(c => c.trim()).find(c => c.startsWith('csrf_token='))?.split('=')?.[1] ?? '';
}

loadMembers();
</script>
