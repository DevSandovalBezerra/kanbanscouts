<div class="h-full p-8 space-y-10 max-w-7xl mx-auto">
    <!-- Header Section -->
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                   <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Gerenciar Projetos</h2>
                   <div class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-indigo-100">Controle Total</div>
                </div>
                <p class="text-slate-500 text-base">Visualize, crie e edite os projetos da sua empresa.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openProjectModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-all shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                    <span class="material-symbols-outlined text-xl">add_circle</span>
                    Novo Projeto
                </button>
            </div>
        </div>
    </section>

    <!-- Projects List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($projects ?? [] as $project): ?>
        <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-indigo-100/30 transition-all p-8 group relative overflow-hidden flex flex-col h-full">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-slate-50 rounded-full blur-2xl group-hover:bg-indigo-50/50 transition-colors"></div>
            
            <div class="flex items-start justify-between mb-6 relative">
                <div class="w-14 h-14 bg-indigo-50 rounded-[22px] border border-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                    <span class="material-symbols-outlined text-2xl font-bold">folder_open</span>
                </div>
                <div class="flex gap-1">
                    <button onclick="editProject(<?php echo $project->id; ?>)" class="p-2 bg-slate-50 hover:bg-amber-50 rounded-xl text-slate-400 hover:text-amber-600 transition-colors" title="Editar Projeto">
                        <span class="material-symbols-outlined text-lg">edit</span>
                    </button>
                    <button onclick="deleteProject(<?php echo $project->id; ?>)" class="p-2 bg-slate-50 hover:bg-rose-50 rounded-xl text-slate-400 hover:text-rose-600 transition-colors" title="Excluir Projeto">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </div>
            </div>

            <div class="flex-1 relative">
                <h4 class="font-outfit text-xl font-bold text-slate-900 mb-2 truncate"><?php echo $project->name; ?></h4>
                <p class="text-slate-500 text-sm line-clamp-3 leading-relaxed"><?php echo $project->description ?: 'Sem descrição informada.'; ?></p>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-between relative">
                 <div class="flex items-center gap-2">
                    <div class="flex -space-x-2 mr-2 js-project-members" data-project-id="<?php echo $project->id; ?>">
                        <!-- populated by JS -->
                    </div>
                </div>
                <a href="<?php echo $app_url ?? ''; ?>/boards?id=<?php echo $project->id; ?>" class="flex items-center gap-2 font-bold text-xs text-indigo-600 hover:text-indigo-700 transition-colors group/link">
                   Ir para o Quadro
                   <span class="material-symbols-outlined text-[18px] group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($projects)): ?>
            <div class="col-span-full py-20 bg-slate-50/50 border-2 border-dashed border-slate-200 rounded-[40px] text-center">
                <div class="w-20 h-20 bg-white rounded-3xl shadow-sm border border-slate-100 flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-4xl text-slate-200">folder_off</span>
                </div>
                <h5 class="text-xl font-bold text-slate-800">Nenhum projeto encontrado</h5>
                <p class="text-slate-400 mt-2">Clique no botão "Novo Projeto" para começar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Project Modal -->
<div id="project-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-[550px] rounded-[40px] shadow-2xl overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-300">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <span class="material-symbols-outlined text-2xl">folder_special</span>
                </div>
                <div>
                     <h3 class="font-outfit text-xl font-bold text-slate-900" id="project-modal-title">Novo Projeto</h3>
                     <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Informações Básicas</p>
                </div>
            </div>
            <button onclick="closeProjectModal()" class="w-10 h-10 bg-white text-slate-400 hover:text-slate-600 hover:scale-110 rounded-2xl transition-all flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined font-bold">close</span>
            </button>
        </div>
        <form id="project-form" class="p-10 space-y-8">
            <input type="hidden" id="project-id" value="">
            
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1">Nome do Projeto</label>
                <input type="text" id="project-name" required placeholder="Ex: App de Delivery, Redesign UX" class="w-full h-14 px-5 bg-slate-50 border border-slate-100 rounded-2xl text-base focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1">Descrição</label>
                <textarea id="project-desc" placeholder="Descreva brevemente os objetivos..." rows="4" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl text-base focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium resize-none"></textarea>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeProjectModal()" class="flex-1 py-4 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" class="flex-[2] py-4 bg-indigo-600 text-white rounded-2xl text-sm font-bold shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all hover:scale-[1.02] active:scale-95">Salvar Projeto</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ── Avatar helpers ───────────────────────────────────────────
    const AVATAR_COLORS = [
        ['#eef2ff','#6366f1'], ['#f0fdf4','#16a34a'], ['#fff7ed','#ea580c'],
        ['#fdf4ff','#a21caf'], ['#f0f9ff','#0284c7'], ['#fef9c3','#ca8a04'],
    ];

    function initials(name) {
        const parts = name.trim().split(/\s+/);
        return (parts[0][0] + (parts[1]?.[0] ?? '')).toUpperCase();
    }

    function avatarHtml(user, idx) {
        const [bg, fg] = AVATAR_COLORS[idx % AVATAR_COLORS.length];
        return `<div class="w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-[9px] font-bold"
                     style="background:${bg};color:${fg}" title="${user.name}">${initials(user.name)}</div>`;
    }

    (async () => {
        try {
            const appBase = '<?php echo $app_url ?? ''; ?>';
            const res     = await fetch(appBase + '/api/users');
            if (!res.ok) return;
            const users = await res.json();

            document.querySelectorAll('.js-project-members').forEach(el => {
                el.innerHTML = users.slice(0, 3).map((u, i) => avatarHtml(u, i)).join('');
                if (users.length > 3) {
                    el.innerHTML += `<div class="w-7 h-7 rounded-full border-2 border-white bg-slate-100 text-slate-500 shadow-sm flex items-center justify-center text-[9px] font-bold">+${users.length - 3}</div>`;
                }
            });
        } catch {}
    })();

    function openProjectModal(id = null) {
        document.getElementById('project-modal').classList.remove('hidden');
        if (!id) {
            document.getElementById('project-modal-title').textContent = 'Novo Projeto';
            document.getElementById('project-id').value = '';
            document.getElementById('project-form').reset();
        }
    }

    function closeProjectModal() {
        document.getElementById('project-modal').classList.add('hidden');
    }

    async function editProject(id) {
        // Enriched: could fetch data from API, but for now we'll assume the name/desc are visible
        // In a real app, I'd fetch specific project data.
        const row = event.target.closest('.group');
        const name = row.querySelector('h4').textContent;
        const desc = row.querySelector('p').textContent;

        document.getElementById('project-modal-title').textContent = 'Editar Projeto';
        document.getElementById('project-id').value = id;
        document.getElementById('project-name').value = name;
        document.getElementById('project-desc').value = desc === 'Sem descrição informada.' ? '' : desc;
        document.getElementById('project-modal').classList.remove('hidden');
    }

    async function deleteProject(id) {
        if (!confirm('Deseja realmente excluir este projeto e todos os seus quadros?')) return;

        const apiUrl = '<?php echo $appUrl; ?>/api/projects/delete?id=' + id;
        try {
            const response = await fetch(apiUrl, { method: 'POST' });
            const result = await response.json();
            if (result.ok) location.reload();
            else alert('Erro: ' + result.error);
        } catch (e) {
            console.error(e);
            alert('Falha na comunicação com o servidor');
        }
    }

    document.getElementById('project-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('project-id').value;
        const name = document.getElementById('project-name').value;
        const description = document.getElementById('project-desc').value;

        const method = id ? 'update' : 'create';
        const url = '<?php echo $appUrl; ?>/api/projects/' + method + (id ? '?id=' + id : '');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, description })
            });
            const result = await response.json();
            if (result.ok) location.reload();
            else alert('Erro: ' + result.error);
        } catch (e) {
            console.error(e);
            alert('Falha na comunicação com o servidor');
        }
    });
</script>
