<div class="h-full p-8 space-y-10 max-w-5xl mx-auto">
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Secretos</h2>
                    <div class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-indigo-100">
                        <?php echo htmlspecialchars($project_name ?? 'Projeto'); ?>
                    </div>
                </div>
                <p class="text-slate-500 text-base">Configure e compartilhe chaves e senhas do projeto com os membros. Os valores são armazenados criptografados e ficam visíveis para quem tem acesso ao projeto.</p>
            </div>
            <button id="btnAddSecret" onclick="openSecretModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-smooth shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95 hidden">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Nuevo Secreto
            </button>
        </div>
    </section>

    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-6 py-4 font-semibold">Título / Clave</th>
                    <th class="text-left px-6 py-4 font-semibold">Descrição</th>
                    <th class="text-left px-6 py-4 font-semibold">Valor</th>
                    <th class="text-left px-6 py-4 font-semibold">Actualizado</th>
                    <th class="text-right px-6 py-4 font-semibold">Ações</th>
                </tr>
            </thead>
            <tbody id="secretsTableBody">
                <tr><td colspan="5" class="text-center py-12 text-slate-400">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="secretModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/30 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-md p-8 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="font-outfit text-xl font-bold text-slate-900" id="secretModalTitle">Nuevo Secreto</h3>
            <button onclick="closeModal('secretModal')" class="text-slate-400 hover:text-slate-600 transition-smooth">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form onsubmit="submitSecret(event)" class="space-y-4">
            <input type="hidden" id="secretId" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Título</label>
                <input id="secretTitle" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Ex.: Stripe API"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Chave</label>
                <input id="secretKey" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="EX: STRIPE_API_KEY"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Descrição</label>
                <textarea id="secretDescription" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Para que serve, onde é usada, ambiente etc."></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Valor</label>
                <input id="secretValue" required type="text" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Cole o valor aqui"/>
            </div>
            <p id="secretError" class="text-rose-600 text-xs hidden"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('secretModal')" class="flex-1 border border-slate-200 text-slate-600 py-2.5 rounded-xl font-semibold text-sm hover:bg-slate-50 transition-smooth">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-semibold text-sm transition-smooth">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE       = '<?php echo $app_url ?? ''; ?>';
const PROJECT_ID = <?php echo (int) ($project_id ?? 0); ?>;
let secrets      = [];
let myRole       = null;

async function loadMyRole() {
    const membersRes = await fetch(`${BASE}/api/project-members?project_id=${PROJECT_ID}`);
    if (!membersRes.ok) return;
    const members = await membersRes.json();

    const meRes = await fetch(`${BASE}/api/auth/me`);
    if (!meRes.ok) return;
    const me = await meRes.json();
    const myId = me?.user?.id ?? me?.id ?? null;
    const myMembership = members.find(m => m.user_id == myId);
    myRole = myMembership ? myMembership.role_in_project : null;

    if (myRole === 'owner' || myRole === 'manager' || myRole === 'editor') {
        document.getElementById('btnAddSecret').classList.remove('hidden');
    }
}

async function loadSecretos() {
    const res = await fetch(`${BASE}/api/project-secrets?project_id=${PROJECT_ID}`);
    const tbody = document.getElementById('secretsTableBody');
    if (!res.ok) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-12 text-rose-400">Erro ao carregar secrets.</td></tr>';
        return;
    }
    secrets = await res.json();
    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('secretsTableBody');
    if (!secrets.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-12 text-slate-400">Nenhum secret configurado.</td></tr>';
        return;
    }

    const canEdit = (myRole === 'owner' || myRole === 'manager' || myRole === 'editor');
    tbody.innerHTML = secrets.map(s => `
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
            <td class="px-6 py-4">
                <div class="font-semibold text-slate-900">${esc(s.title || s.secret_key || '')}</div>
                ${s.title ? `<div class="text-[11px] text-slate-400 font-mono mt-0.5">${esc(s.secret_key || '')}</div>` : ''}
            </td>
            <td class="px-6 py-4 text-slate-600 text-xs leading-relaxed">
                <div class="line-clamp-3">${esc(s.description || '') || '<span class="text-slate-300">—</span>'}</div>
            </td>
            <td class="px-6 py-4 text-slate-700 font-mono text-xs break-all">${esc(s.secret_value || '')}</td>
            <td class="px-6 py-4 text-slate-400 text-xs">${s.updated_at ? esc(String(s.updated_at).substring(0, 19)) : '—'}</td>
            <td class="px-6 py-4 text-right">
                <button onclick="copySecretValue(${s.id})" class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-smooth" title="Copiar valor">
                    <i data-lucide="copy" class="w-4.5 h-4.5"></i>
                </button>
                ${canEdit ? `
                <button onclick="openSecretModal(${s.id})" class="p-2 rounded-xl hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-smooth" title="Editar">
                    <i data-lucide="edit-2" class="w-4.5 h-4.5"></i>
                </button>
                <button onclick="deleteSecret(${s.id}, '${esc(s.title || s.secret_key || '')}')" class="p-2 rounded-xl hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-smooth" title="Excluir">
                    <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
    lucide.createIcons();
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    if (meta) return meta;
    return document.cookie.split(';').map(c => c.trim()).find(c => c.startsWith('csrf_token='))?.split('=')?.[1] ?? '';
}

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openSecretModal(id = null) {
    document.getElementById('secretError').classList.add('hidden');

    if (!id) {
        document.getElementById('secretModalTitle').textContent = 'Nuevo Secreto';
        document.getElementById('secretId').value = '';
        document.getElementById('secretTitle').value = '';
        document.getElementById('secretKey').value = '';
        document.getElementById('secretDescription').value = '';
        document.getElementById('secretValue').value = '';
    } else {
        const s = secrets.find(x => x.id == id);
        if (!s) return;
        document.getElementById('secretModalTitle').textContent = 'Editar Secret';
        document.getElementById('secretId').value = String(id);
        document.getElementById('secretTitle').value = s.title || '';
        document.getElementById('secretKey').value = s.secret_key || '';
        document.getElementById('secretDescription').value = s.description || '';
        document.getElementById('secretValue').value = s.secret_value || '';
    }

    document.getElementById('secretModal').classList.remove('hidden');
}

async function copySecretValue(id) {
    const s = secrets.find(x => x.id == id);
    if (!s) return;
    const value = s.secret_value || '';
    if (!value) {
        await Swal.fire({ title: 'Sem valor', text: 'Este secret não possui valor.', icon: 'info' });
        return;
    }
    try {
        await navigator.clipboard.writeText(value);
        await Swal.fire({ title: 'Copiado', text: 'Valor copiado para a área de transferência.', icon: 'success' });
    } catch {
        await Swal.fire({ title: 'Erro', text: 'Não foi possível copiar automaticamente.', icon: 'error' });
    }
}

async function submitSecret(e) {
    e.preventDefault();
    const id = document.getElementById('secretId').value;
    const secret_key = document.getElementById('secretKey').value.trim();
    const secret_value = document.getElementById('secretValue').value;
    const title = document.getElementById('secretTitle').value.trim();
    const description = document.getElementById('secretDescription').value.trim();
    const errEl = document.getElementById('secretError');

    if (!secret_key) { errEl.textContent = 'Chave é obrigatória.'; errEl.classList.remove('hidden'); return; }
    if (!secret_value) { errEl.textContent = 'Valor é obrigatório.'; errEl.classList.remove('hidden'); return; }

    const method = id ? 'PATCH' : 'POST';
    const url = id ? `${BASE}/api/project-secrets?id=${encodeURIComponent(id)}` : `${BASE}/api/project-secrets`;
    const body = id
        ? { secret_key, secret_value, title: title || null, description: description || null }
        : { project_id: PROJECT_ID, secret_key, secret_value, title: title || null, description: description || null };

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
        body: JSON.stringify(body)
    });
    const dataText = await res.text();
    let data = null;
    try { data = JSON.parse(dataText); } catch { data = null; }

    if (!res.ok) {
        const msg = data?.error?.message || 'Erro ao salvar.';
        errEl.textContent = msg;
        errEl.classList.remove('hidden');
        return;
    }

    closeModal('secretModal');
    await Swal.fire({ title: 'Salvo', text: 'Secret salvo com sucesso.', icon: 'success' });
    loadSecretos();
}

async function deleteSecret(id, key) {
    const confirmResult = await Swal.fire({
        title: 'Excluir secret?',
        text: `Deseja excluir "${key}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar'
    });
    if (!confirmResult.isConfirmed) return;

    const res = await fetch(`${BASE}/api/project-secrets?id=${encodeURIComponent(id)}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-Token': getCsrfToken() }
    });
    const dataText = await res.text();
    let data = null;
    try { data = JSON.parse(dataText); } catch { data = null; }
    if (!res.ok) {
        await Swal.fire({ title: 'Erro', text: data?.error?.message || 'Erro ao excluir.', icon: 'error' });
        return;
    }

    await Swal.fire({ title: 'Excluído', text: 'Secret excluído.', icon: 'success' });
    loadSecretos();
}

(async () => {
    await loadMyRole();
    await loadSecretos();
})();
</script>
