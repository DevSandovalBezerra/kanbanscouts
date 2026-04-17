<?php
$user_initials = '';
if (!empty($user_name)) {
    $words = explode(' ', trim($user_name));
    if (count($words) >= 2) {
        $user_initials = strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
    } else {
        $user_initials = strtoupper(substr($user_name, 0, 2));
    }
}
?>
<aside class="w-72 h-full glass-dark border-r border-slate-800/50 hidden md:flex flex-col flex-shrink-0 z-40 relative">
    <!-- Brand -->
    <div class="px-8 py-10 flex items-center gap-4 group cursor-pointer">
        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-2xl shadow-indigo-500/20 group-hover:scale-110 group-hover:rotate-3 transition-smooth">
            <i data-lucide="box" class="w-7 h-7"></i>
        </div>
        <div class="flex flex-col">
            <span class="font-outfit text-2xl font-black tracking-tighter text-white">Kanban<span class="text-indigo-400">Scout</span></span>
            <span class="text-[9px] text-slate-500 font-bold uppercase tracking-[0.3em] leading-none mt-1">Enterprise Edition</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-5 space-y-1.5 overflow-y-auto mt-2 custom-scrollbar">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] px-4 mb-3 mt-6">Workspace</div>
        
        <a href="<?php echo $app_url ?? ''; ?>/" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group relative overflow-hidden">
            <i data-lucide="layout-dashboard" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Panel General
        </a>

        <a href="<?php echo $app_url ?? ''; ?>/projects" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group">
            <i data-lucide="folder-kanban" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Proyectos
        </a>
        
        <a href="<?php echo ($app_url ?? '') . '/boards' . ($first_board_id ?? 0 ? '?id=' . $first_board_id : ''); ?>" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group">
            <i data-lucide="kanban" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Tablero Kanban
        </a>

        <a href="<?php echo $app_url ?? ''; ?>/calendar" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group">
            <i data-lucide="calendar" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Calendario
        </a>

        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] px-4 mb-3 mt-8">Colaboración</div>

        <a href="<?php echo $app_url ?? ''; ?>/contacts" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group">
            <i data-lucide="users-2" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Contactos
        </a>

        <a href="<?php echo $app_url ?? ''; ?>/messages" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group">
            <i data-lucide="message-circle" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Mensajes
        </a>
        
        <a href="<?php echo $app_url ?? ''; ?>/documents" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-white/5 hover:text-white transition-smooth group">
            <i data-lucide="files" class="w-5 h-5 group-hover:scale-110 group-hover:text-indigo-400 transition-smooth"></i>
            Documentos
        </a>

        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] px-4 mb-3 mt-8">Sistema</div>

        <?php if (!empty($user_is_admin)): ?>
        <a href="<?php echo $app_url ?? ''; ?>/admin/users" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-smooth group">
            <i data-lucide="user-cog" class="w-5 h-5 group-hover:scale-110 transition-smooth"></i>
            Usuarios
        </a>
        <?php endif; ?>

        <a href="#" class="flex items-center gap-3.5 px-4 py-3.5 text-sm font-medium rounded-2xl text-slate-500 hover:bg-white/5 hover:text-white transition-smooth group opacity-60">
            <i data-lucide="settings" class="w-5 h-5 group-hover:scale-110 transition-smooth"></i>
            Configuración
        </a>
    </nav>

    <!-- Sidebar Bottom: Profile Summary -->
    <div class="p-6 border-t border-white/5 bg-white/5 backdrop-blur-md">
        <div class="flex items-center gap-3 px-1">
            <div class="w-10 h-10 border border-white/10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-lg shadow-black/20">
                <?php echo htmlspecialchars($user_initials); ?>
            </div>
            <div class="flex flex-col flex-1 min-w-0">
                <span class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($user_name ?? ''); ?></span>
                <span class="text-[10px] text-slate-400 font-medium truncate"><?php echo htmlspecialchars($user_email ?? ''); ?></span>
            </div>
            <a href="<?php echo $app_url ?? ''; ?>/logout" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-500 hover:bg-rose-500/10 hover:text-rose-400 transition-smooth">
                <i data-lucide="log-out" class="w-4.5 h-4.5"></i>
            </a>
        </div>
    </div>
</aside>

