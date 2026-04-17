<div class="h-full p-8 space-y-10 max-w-7xl mx-auto">
    <!-- Header -->
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Gerenciar Usuários</h2>
                    <div class="px-2.5 py-1 bg-rose-50 text-rose-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-rose-100">Admin</div>
                </div>
                <p class="text-slate-500 text-base">Crie, edite e gerencie os usuários da empresa.</p>
            </div>
            <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-smooth shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Novo Usuário
            </button>
        </div>
    </section>

    <!-- Toast -->
    <div id="toast" class="fixed top-6 right-6 z-50 hidden px-5 py-3 rounded-2xl text-sm font-semibold shadow-lg transition-all"></div>

    <!-- Users Table -->
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-6 py-4 font-semibold">Nome</th>
                    <th class="text-left px-6 py-4 font-semibold">E-mail</th>
                    <th class="text-left px-6 py-4 font-semibold">Status</th>
                    <th class="text-left px-6 py-4 font-semibold">Papel</th>
                    <th class="text-left px-6 py-4 font-semibold">Criado em</th>
                    <th class="text-right px-6 py-4 font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <tr><td colspan="6" class="text-center py-12 text-slate-400">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create / Edit User -->
<div id="userModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/30 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md p-8 space-y-6">
        <div class="flex items-center justify-between">
            <h3 id="modalTitle" class="font-outfit text-xl font-bold text-slate-900">Novo Usuário</h3>
            <button onclick="closeModal('userModal')" class="text-slate-400 hover:text-slate-600 transition-smooth">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="userForm" class="space-y-4" onsubmit="submitUserForm(event)">
            <input type="hidden" id="editUserId" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Nome completo</label>
                <input id="fieldName" type="text" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="João Silva">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">E-mail</label>
                <input id="fieldEmail" type="email" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="joao@empresa.com">
            </div>
            <div id="passwordGroup">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Senha <span id="passwordHint" class="text-slate-400 font-normal">(mín. 8 chars, 1 maiúscula, 1 número)</span></label>
                <input id="fieldPassword" type="password" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Senha@123">
            </div>
            <div class="flex items-center gap-3">
                <input id="fieldIsAdmin" type="checkbox" class="w-4 h-4 accent-indigo-600">
                <label for="fieldIsAdmin" class="text-sm font-medium text-slate-700">Administrador</label>
            </div>
            <p id="formError" class="text-rose-600 text-xs hidden"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('userModal')" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-smooth">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-semibold text-sm transition-smooth">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reset Password -->
<div id="resetModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/30 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md p-8 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="font-outfit text-xl font-bold text-slate-900">Redefinir Senha</h3>
            <button onclick="closeModal('resetModal')" class="text-slate-400 hover:text-slate-600 transition-smooth">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form onsubmit="submitResetPassword(event)" class="space-y-4">
            <input type="hidden" id="resetUserId" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Nova Senha</label>
                <input id="resetPassword" type="password" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="NovaSenha@456">
                <p class="text-[11px] text-slate-400 mt-1">Mín. 8 chars, 1 maiúscula, 1 número</p>
            </div>
            <p id="resetError" class="text-rose-600 text-xs hidden"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('resetModal')" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-smooth">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-semibold text-sm transition-smooth">Redefinir</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE = '<?php echo $app_url ?? ''; ?>';
let users = [];

async function loadUsers() {
    const res = await fetch(BASE + '/api/admin/users');
    if (!res.ok) { showToast('Erro ao carregar usuários', true); return; }
    users = await res.json();
    renderTable();
    lucide.createIcons();
}

function renderTable() {
    const tbody = document.getElementById('usersTableBody');
    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-slate-400">Nenhum usuário encontrado.</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(u => `
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4 font-semibold text-slate-900">${esc(u.name)}</td>
            <td class="px-6 py-4 text-slate-500">${esc(u.email)}</td>
            <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold ${u.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}">
                    ${u.status === 'active' ? 'Ativo' : 'Inativo'}
                </span>
            </td>
            <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold ${u.is_admin ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500'}">
                    ${u.is_admin ? 'Admin' : 'Membro'}
                </span>
            </td>
            <td class="px-6 py-4 text-slate-400 text-xs">${u.created_at ? u.created_at.substring(0,10) : '—'}</td>
            <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="openEditModal(${u.id})" class="p-2 rounded-xl hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-smooth" title="Editar">
                        <i data-lucide="edit-2" class="w-4.5 h-4.5"></i>
                    </button>
                    <button onclick="openResetModal(${u.id})" class="p-2 rounded-xl hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-smooth" title="Redefinir senha">
                        <i data-lucide="refresh-cw" class="w-4.5 h-4.5"></i>
                    </button>
                    <button onclick="toggleStatus(${u.id})" class="p-2 rounded-xl hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-smooth" title="${u.status === 'active' ? 'Desativar' : 'Ativar'}">
                        <i data-lucide="${u.status === 'active' ? 'user-x' : 'user-check'}" class="w-4.5 h-4.5"></i>
                    </button>
                    <button onclick="deleteUser(${u.id})" class="p-2 rounded-xl hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-smooth" title="Excluir">
                        <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
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

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Novo Usuário';
    document.getElementById('editUserId').value = '';
    document.getElementById('fieldName').value = '';
    document.getElementById('fieldEmail').value = '';
    document.getElementById('fieldPassword').value = '';
    document.getElementById('fieldPassword').required = true;
    document.getElementById('passwordHint').textContent = '(mín. 8 chars, 1 maiúscula, 1 número)';
    document.getElementById('fieldIsAdmin').checked = false;
    document.getElementById('formError').classList.add('hidden');
    document.getElementById('userModal').classList.remove('hidden');
}

function openEditModal(id) {
    const u = users.find(x => x.id == id);
    if (!u) return;
    document.getElementById('modalTitle').textContent = 'Editar Usuário';
    document.getElementById('editUserId').value = id;
    document.getElementById('fieldName').value = u.name;
    document.getElementById('fieldEmail').value = u.email;
    document.getElementById('fieldPassword').value = '';
    document.getElementById('fieldPassword').required = false;
    document.getElementById('passwordHint').textContent = '(deixe vazio para não alterar)';
    document.getElementById('fieldIsAdmin').checked = u.is_admin;
    document.getElementById('formError').classList.add('hidden');
    document.getElementById('userModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

async function submitUserForm(e) {
    e.preventDefault();
    const id = document.getElementById('editUserId').value;
    const isEdit = id !== '';
    const errEl = document.getElementById('formError');

    const body = {
        name:     document.getElementById('fieldName').value.trim(),
        email:    document.getElementById('fieldEmail').value.trim(),
        is_admin: document.getElementById('fieldIsAdmin').checked,
    };
    const pass = document.getElementById('fieldPassword').value;
    if (pass) body.password = pass;
    if (!isEdit && !pass) { errEl.textContent = 'Senha é obrigatória.'; errEl.classList.remove('hidden'); return; }

    const url    = BASE + '/api/admin/users' + (isEdit ? '?id=' + id : '');
    const method = isEdit ? 'PATCH' : 'POST';

    const res  = await fetch(url, { method, headers: {'Content-Type':'application/json', 'X-CSRF-Token': getCsrfToken()}, body: JSON.stringify(body) });
    const data = await res.json();

    if (!res.ok) {
        const msg = data?.error?.details ? Object.values(data.error.details).flat().join(', ') : (data?.error?.message || 'Erro desconhecido');
        errEl.textContent = msg; errEl.classList.remove('hidden'); return;
    }

    closeModal('userModal');
    showToast(isEdit ? 'Usuário atualizado!' : 'Usuário criado!');
    loadUsers();
}

function openResetModal(id) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('resetPassword').value = '';
    document.getElementById('resetError').classList.add('hidden');
    document.getElementById('resetModal').classList.remove('hidden');
}

async function submitResetPassword(e) {
    e.preventDefault();
    const userId = document.getElementById('resetUserId').value;
    const pass   = document.getElementById('resetPassword').value;
    const errEl  = document.getElementById('resetError');

    const res  = await fetch(BASE + '/api/admin/users/reset-password', {
        method: 'POST',
        headers: {'Content-Type':'application/json', 'X-CSRF-Token': getCsrfToken()},
        body: JSON.stringify({ user_id: parseInt(userId), new_password: pass })
    });
    const data = await res.json();

    if (!res.ok) {
        errEl.textContent = data?.error?.message || 'Erro ao redefinir senha.';
        errEl.classList.remove('hidden'); return;
    }

    closeModal('resetModal');
    showToast('Senha redefinida com sucesso!');
}

async function toggleStatus(id) {
    const u = users.find(x => x.id == id);
    const label = u.status === 'active' ? 'desativar' : 'ativar';
    if (!confirm(`Deseja ${label} o usuário "${u.name}"?`)) return;

    const res  = await fetch(BASE + '/api/admin/users/toggle-status', {
        method: 'POST',
        headers: {'Content-Type':'application/json', 'X-CSRF-Token': getCsrfToken()},
        body: JSON.stringify({ user_id: id })
    });
    const data = await res.json();

    if (!res.ok) { showToast(data?.error?.message || 'Erro ao alterar status.', true); return; }
    showToast('Status alterado!');
    loadUsers();
}

async function deleteUser(id) {
    const u = users.find(x => x.id == id);
    if (!confirm(`Excluir o usuário "${u.name}"? Esta ação não pode ser desfeita.`)) return;

    const res  = await fetch(BASE + '/api/admin/users?id=' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-Token': getCsrfToken()}
    });
    const data = await res.json();

    if (!res.ok) {
        let msg = data?.error?.message || 'Erro ao excluir.';
        if (data?.error?.details?.projects) {
            msg += ' Projetos: ' + data.error.details.projects.map(p => p.name).join(', ');
        }
        showToast(msg, true); return;
    }
    showToast('Usuário excluído.');
    loadUsers();
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    if (meta) return meta;
    return document.cookie.split(';').map(c => c.trim()).find(c => c.startsWith('csrf_token='))?.split('=')?.[1] ?? '';
}

loadUsers();
</script>
