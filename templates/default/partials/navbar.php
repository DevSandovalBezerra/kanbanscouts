<?php
// Compute avatar initials from user_name
$_navName    = $user_name   ?? 'U';
$_navEmail   = $user_email  ?? '';
$_navAvatar  = $user_avatar ?? '';
$_navIsAdmin = $user_is_admin ?? false;
$_navAppUrl  = $app_url ?? '';

$_nameParts   = array_filter(explode(' ', $_navName));
$_navInitials = '';
if (count($_nameParts) >= 2) {
    $_navInitials = strtoupper(mb_substr(array_values($_nameParts)[0], 0, 1) . mb_substr(array_values($_nameParts)[1], 0, 1));
} elseif (count($_nameParts) === 1) {
    $_navInitials = strtoupper(mb_substr(array_values($_nameParts)[0], 0, 2));
} else {
    $_navInitials = 'U';
}
?>
<header class="h-16 flex items-center justify-between px-6 bg-white border-b border-slate-200 sticky top-0 z-20 shadow-sm glass">
    <div class="flex items-center flex-1 max-w-xl relative">
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
            <input type="text" id="global-search" placeholder="Pesquise projetos, quadros ou pessoas..." class="w-full h-10 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 placeholder:text-slate-400 transition-all">
            <kbd class="absolute right-3 top-1/2 -translate-y-1/2 px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] text-slate-400 font-bold hidden sm:block">⌘K</kbd>
        </div>

        <!-- Search Results Dropdown -->
        <div id="search-results" class="hidden absolute top-12 left-0 w-full bg-white border border-slate-100 rounded-2xl shadow-2xl z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="p-2 space-y-1" id="search-results-list"></div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                Pressione Enter para ver tudo
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-4 pl-4">
        <div class="hidden sm:flex items-center gap-1 bg-indigo-50/50 px-3 py-1.5 rounded-full border border-indigo-100">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest whitespace-nowrap">Online em tempo real</span>
        </div>

        <a href="<?php echo htmlspecialchars($_navAppUrl); ?>/messages" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-all relative">
            <span class="material-symbols-outlined text-[24px]">chat_bubble_outline</span>
            <span class="absolute right-2 top-2 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
        </a>

        <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-all relative">
            <span class="material-symbols-outlined text-[24px]">notifications</span>
        </button>

        <div class="h-8 w-px bg-slate-200 mx-1"></div>

        <!-- Profile Dropdown -->
        <div class="relative" id="profile-menu-container">
            <button id="profile-menu-btn"
                    class="flex items-center gap-3 cursor-pointer focus:outline-none"
                    aria-haspopup="true" aria-expanded="false">
                <div class="hidden sm:flex flex-col text-right truncate max-w-[150px]">
                    <span id="nav-user-name" class="text-xs font-bold text-slate-900 leading-none truncate">
                        <?php echo htmlspecialchars($_navName); ?>
                    </span>
                    <span class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5">
                        <?php echo $_navIsAdmin ? 'Administrador' : 'Membro'; ?>
                    </span>
                </div>
                <div class="w-10 h-10 border-2 border-slate-100 rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden flex-shrink-0">
                    <?php if ($_navAvatar !== ''): ?>
                        <img id="nav-avatar-img"
                             src="<?php echo htmlspecialchars($_navAvatar); ?>"
                             alt="Avatar"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div id="nav-avatar-initials"
                             class="w-full h-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                            <?php echo htmlspecialchars($_navInitials); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </button>

            <!-- Dropdown Menu -->
            <div id="profile-dropdown"
                 class="hidden absolute right-0 top-14 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden animate-in fade-in zoom-in duration-150 origin-top-right">

                <!-- User info header -->
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-800 truncate">
                        <?php echo htmlspecialchars($_navName); ?>
                    </p>
                    <p class="text-xs text-slate-400 truncate">
                        <?php echo htmlspecialchars($_navEmail); ?>
                    </p>
                </div>

                <!-- Menu items -->
                <div class="py-1">
                    <button onclick="openProfileModal()"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">manage_accounts</span>
                        Meu Perfil
                    </button>

                    <?php if ($_navIsAdmin): ?>
                    <a href="<?php echo htmlspecialchars($_navAppUrl); ?>/admin/users"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">admin_panel_settings</span>
                        Gerenciar Usuários
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Logout -->
                <div class="border-t border-slate-100 py-1">
                    <a href="<?php echo htmlspecialchars($_navAppUrl); ?>/logout"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // ── Global Search ────────────────────────────────────────────────────────
    const searchInput  = document.getElementById('global-search');
    const resultsPanel = document.getElementById('search-results');
    const resultsList  = document.getElementById('search-results-list');

    searchInput.addEventListener('input', async (e) => {
        const q = e.target.value;
        if (q.length < 2) { resultsPanel.classList.add('hidden'); return; }

        try {
            const url = '<?php echo htmlspecialchars($_navAppUrl); ?>/api/search?q=' + encodeURIComponent(q);
            const response = await fetch(url);
            const data = await response.json();

            if (data.results && data.results.length > 0) {
                resultsList.innerHTML = '';
                data.results.forEach(res => {
                    const item = document.createElement('a');
                    item.href = '<?php echo htmlspecialchars($_navAppUrl); ?>/boards?id=' + res.id;
                    item.className = 'flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group';
                    item.innerHTML = `
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm font-bold">${res.type === 'board' ? 'view_kanban' : 'task'}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-900">${res.name}</p>
                            <p class="text-[10px] text-slate-400 capitalize">${res.type === 'board' ? 'Quadro' : 'Tarefa'}</p>
                        </div>`;
                    resultsList.appendChild(item);
                });
                resultsPanel.classList.remove('hidden');
            } else {
                resultsPanel.classList.add('hidden');
            }
        } catch (err) { console.error(err); }
    });

    // ── Profile Dropdown ─────────────────────────────────────────────────────
    const profileBtn      = document.getElementById('profile-menu-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = !profileDropdown.classList.contains('hidden');
        profileDropdown.classList.toggle('hidden', isOpen);
        profileBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('profile-menu-container').contains(e.target)) {
            profileDropdown.classList.add('hidden');
            profileBtn.setAttribute('aria-expanded', 'false');
        }
        if (!searchInput.contains(e.target) && !resultsPanel.contains(e.target)) {
            resultsPanel.classList.add('hidden');
        }
    });
</script>
