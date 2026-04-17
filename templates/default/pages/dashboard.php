<div class="h-full p-8 space-y-10 max-w-7xl mx-auto">
    <!-- Hero -->
    <section class="mb-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">
                    Bem-vindo, <?php echo htmlspecialchars($user_name ?? 'Usuário'); ?>!
                </h2>
                <p class="text-slate-500 mt-2 text-base">Aqui está o que está acontecendo com seus projetos hoje.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-smooth shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Novo Projeto
                </button>
            </div>
        </div>
    </section>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Quadros Ativos -->
        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-smooth group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-smooth shadow-sm shadow-indigo-100/50">
                    <i data-lucide="layout" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em]">Quadros Ativos</p>
            <h3 class="text-3xl font-black text-slate-900 mt-1 font-outfit"><?php echo count($boards ?? []); ?></h3>
        </div>

        <!-- Tarefas Pendentes -->
        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-smooth group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-smooth shadow-sm shadow-purple-100/50">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em]">Pendências</p>
            <h3 class="text-3xl font-black text-slate-900 mt-1 font-outfit"><?php echo $pending_tasks ?? 0; ?></h3>
        </div>

        <!-- Equipe Total -->
        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-smooth group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-smooth shadow-sm shadow-blue-100/50">
                    <i data-lucide="users-2" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em]">Colaboradores</p>
            <h3 class="text-3xl font-black text-slate-900 mt-1 font-outfit"><?php echo $team_size ?? 0; ?></h3>
        </div>

        <!-- Tarefas Concluídas -->
        <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-smooth group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-smooth shadow-sm shadow-emerald-100/50">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em]">Concluídas</p>
            <h3 class="text-3xl font-black text-slate-900 mt-1 font-outfit"><?php echo $done_tasks ?? 0; ?></h3>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-10">
        <!-- Boards Table -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm p-8 overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-outfit text-xl font-bold text-slate-900">Seus Quadros</h3>
                <a href="<?php echo $app_url ?? ''; ?>/projects" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">Ver projetos →</a>
            </div>

            <div class="space-y-4">
                <?php foreach ($boards ?? [] as $board): ?>
                <a href="<?php echo $app_url ?? ''; ?>/boards?id=<?php echo $board->id; ?>"
                   class="flex items-center p-5 rounded-3xl border border-slate-50 bg-slate-50/20 hover:bg-white hover:border-indigo-100 hover:shadow-xl hover:shadow-indigo-500/5 hover:-translate-y-0.5 transition-smooth group">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mr-5 group-hover:bg-indigo-600 group-hover:text-white transition-smooth shadow-lg shadow-indigo-100/20">
                        <i data-lucide="layout" class="w-6 h-6"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors truncate"><?php echo htmlspecialchars($board->name); ?></h4>
                        <p class="text-xs text-slate-400 mt-0.5">Clique para visualizar o quadro</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center bg-slate-50 text-slate-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-smooth">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </div>
                </a>
                <?php endforeach; ?>

                <?php if (empty($boards)): ?>
                <div class="text-center py-10">
                    <i data-lucide="layout" class="w-12 h-12 text-slate-200 mx-auto block"></i>
                    <p class="text-slate-400 mt-2 text-sm">Nenhum quadro encontrado. Crie um projeto para começar.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Progress Widget -->
        <div class="bg-indigo-600 rounded-3xl p-8 text-white flex flex-col justify-between shadow-2xl shadow-indigo-200 relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-1000"></div>
            <div>
                <h3 class="font-outfit text-2xl font-bold mb-2">Progresso Geral</h3>
                <p class="text-indigo-100 text-sm mb-6 leading-relaxed">
                    <?php echo $done_tasks ?? 0; ?> de <?php echo $total_tasks ?? 0; ?> tarefas concluídas.
                </p>
                <div class="space-y-4">
                    <div class="bg-white/10 p-4 rounded-2xl flex items-center justify-between border border-white/5 backdrop-blur-sm">
                        <span class="text-xs font-bold uppercase tracking-widest text-white/80">Conclusão</span>
                        <span class="text-sm font-bold"><?php echo $done_pct ?? 0; ?>%</span>
                    </div>
                    <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                        <div class="bg-white h-full rounded-full shadow-lg transition-all"
                             style="width: <?php echo $done_pct ?? 0; ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-10">
                <a href="<?php echo $app_url ?? ''; ?>/projects"
                   class="block w-full py-3 text-center bg-white text-indigo-600 rounded-2xl font-bold hover:bg-slate-50 transition-colors shadow-lg shadow-indigo-800/20 active:translate-y-0.5">
                    Ver Projetos
                </a>
            </div>
        </div>
    </div>
</div>
