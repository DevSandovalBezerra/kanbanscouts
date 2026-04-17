<?php
// Compute avatar initials from user_name
$_navName    = $user_name   ?? 'U';
$_navEmail   = $user_email  ?? '';
$_navAvatar  = $user_avatar ?? '';
$_navIsAdmin = $user_is_admin ?? false;
$_navAppUrl  = $app_url ?? '';

$_navWebRoot = preg_replace('~/index\.php$~', '', $_navAppUrl);
if ($_navWebRoot === null) $_navWebRoot = '';
if ($_navWebRoot !== '' && !str_ends_with($_navWebRoot, '/')) $_navWebRoot .= '/';

$_navAvatarUrl = '';
if (is_string($_navAvatar) && trim($_navAvatar) !== '') {
    $a = trim($_navAvatar);
    if (preg_match('~^https?://~i', $a)) {
        $_navAvatarUrl = $a;
    } elseif (str_starts_with($a, '/uploads/')) {
        $_navAvatarUrl = rtrim($_navWebRoot, '/') . $a;
    } elseif (str_starts_with($a, 'uploads/')) {
        $_navAvatarUrl = $_navWebRoot . $a;
    } elseif (!str_contains($a, '/')) {
        $_navAvatarUrl = $_navWebRoot . 'uploads/avatars/' . basename($a);
    } elseif (str_starts_with($a, '/')) {
        $_navAvatarUrl = $a;
    } else {
        $_navAvatarUrl = $_navWebRoot . $a;
    }
}

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
<header class="h-20 flex items-center justify-between px-8 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 sticky top-0 z-30 transition-smooth">
    <div class="flex items-center flex-1 max-w-xl relative">
        <div class="relative w-full group">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-4.5 h-4.5 group-focus-within:text-indigo-500 transition-smooth"></i>
            <input type="text" id="global-search" placeholder="Buscar projetos, quadros ou pessoas..." class="w-full h-11 pl-12 pr-4 bg-slate-50 border border-slate-200/60 rounded-2xl text-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500/30 focus:bg-white placeholder:text-slate-400 transition-smooth">
            <kbd class="absolute right-4 top-1/2 -translate-y-1/2 px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10px] text-slate-400 font-bold hidden sm:block shadow-sm">⌘K</kbd>
        </div>

        <!-- Search Results Dropdown -->
        <div id="search-results" class="hidden absolute top-12 left-0 w-full bg-white border border-slate-100 rounded-2xl shadow-2xl z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="p-2 space-y-1" id="search-results-list"></div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">
                Pressione Enter para ver tudo
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-4 pl-6">
        <div class="hidden lg:flex items-center gap-2 bg-indigo-50 px-3.5 py-1.5 rounded-full border border-indigo-100/50">
            <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] animate-pulse"></div>
            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest whitespace-nowrap">Conexão Ativa</span>
        </div>

        <a href="<?php echo htmlspecialchars($_navAppUrl); ?>/messages" class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-2xl transition-smooth relative group">
            <i data-lucide="message-square" class="w-5.5 h-5.5 group-hover:scale-110 transition-smooth"></i>
            <span class="absolute right-2.5 top-2.5 block h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
        </a>

        <button class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-2xl transition-smooth relative group">
            <i data-lucide="bell" class="w-5.5 h-5.5 group-hover:scale-110 transition-smooth"></i>
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
                <div class="w-10 h-10 border border-slate-200 rounded-2xl shadow-sm hover:shadow-xl hover:scale-105 transition-smooth overflow-hidden flex-shrink-0">
                    <?php if ($_navAvatarUrl !== ''): ?>
                        <img id="nav-avatar-img"
                             src="<?php echo htmlspecialchars($_navAvatarUrl); ?>"
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
                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                        Meu Perfil
                    </button>

                    <?php if ($_navIsAdmin): ?>
                    <a href="<?php echo htmlspecialchars($_navAppUrl); ?>/admin/users"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                        <i data-lucide="shield-check" class="w-4 h-4 text-slate-400"></i>
                        Gerenciar Usuários
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Logout -->
                <div class="border-t border-slate-100 py-1">
                    <a href="<?php echo htmlspecialchars($_navAppUrl); ?>/logout"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
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
                            <i data-lucide="${res.type === 'board' ? 'kanban' : 'file-text'}" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-900">${res.name}</p>
                            <p class="text-[10px] text-slate-400 capitalize">${res.type === 'board' ? 'Quadro' : 'Tarefa'}</p>
                        </div>`;
                    resultsList.appendChild(item);
                });
                lucide.createIcons();
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
