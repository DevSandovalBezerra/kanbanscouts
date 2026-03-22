<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrf_token ?? ''; ?>">
    <title><?php echo $title ?? 'KanbanLite - Gestão Ágil'; ?></title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#6366F1',
                            light: '#EEF2FF',
                            dark: '#4F46E5'
                        },
                        surface: '#F8FAFC',
                        'sidebar-bg': '#FFFFFF',
                        'sidebar-active': '#F1F5F9'
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #1E293B; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24 }
        .sidebar-item-active { background-color: #F1F5F9; color: #6366F1; font-weight: 600; border-right: 4px solid #6366F1; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body class="h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <?php require self::resolvePath('partials.sidebar'); ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Navbar -->
        <?php require self::resolvePath('partials.navbar'); ?>
        
        <!-- Page Content -->
        <main class="flex-1 overflow-auto bg-surface">
            <?php echo $content; ?>
        </main>

        <!-- Footer (opcional na main) -->
        <?php // require self::resolvePath('partials.footer'); ?>
    </div>
    
    <?php if (isset($extra_js)) echo $extra_js; ?>

    <!-- ── Profile Modal ──────────────────────────────────────────────────── -->
    <?php
    $_pmName    = $user_name   ?? '';
    $_pmEmail   = $user_email  ?? '';
    $_pmAvatar  = $user_avatar ?? '';
    $_pmAppUrl  = $app_url     ?? '';

    $_pmParts    = array_filter(explode(' ', $_pmName));
    $_pmInitials = '';
    if (count($_pmParts) >= 2) {
        $_pmInitials = strtoupper(mb_substr(array_values($_pmParts)[0], 0, 1) . mb_substr(array_values($_pmParts)[1], 0, 1));
    } elseif (count($_pmParts) === 1) {
        $_pmInitials = strtoupper(mb_substr(array_values($_pmParts)[0], 0, 2));
    } else {
        $_pmInitials = 'U';
    }
    ?>
    <div id="profile-modal"
         class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 pt-6 pb-4">
                <h2 class="text-lg font-semibold text-slate-800">Meu Perfil</h2>
                <button onclick="closeProfileModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined text-slate-400 text-[20px]">close</span>
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-slate-100 px-6">
                <button onclick="switchProfileTab('profile')" id="tab-profile"
                        class="pb-3 mr-6 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600 transition-colors">
                    Perfil
                </button>
                <button onclick="switchProfileTab('password')" id="tab-password"
                        class="pb-3 text-sm font-medium text-slate-400 border-b-2 border-transparent transition-colors">
                    Senha
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">

                <!-- ── ABA PERFIL ──────────────────────────────────────────── -->
                <div id="tab-content-profile">

                    <!-- Avatar -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="relative group cursor-pointer"
                             onclick="document.getElementById('avatar-input').click()">
                            <?php if ($_pmAvatar !== ''): ?>
                                <img id="avatar-preview"
                                     src="<?php echo htmlspecialchars($_pmAvatar); ?>"
                                     class="w-20 h-20 rounded-full object-cover border-4 border-indigo-100"/>
                                <div id="avatar-initials-preview"
                                     class="hidden w-20 h-20 rounded-full bg-indigo-600 text-white text-2xl font-semibold flex items-center justify-center border-4 border-indigo-100">
                                    <?php echo htmlspecialchars($_pmInitials); ?>
                                </div>
                            <?php else: ?>
                                <img id="avatar-preview"
                                     src=""
                                     class="hidden w-20 h-20 rounded-full object-cover border-4 border-indigo-100"/>
                                <div id="avatar-initials-preview"
                                     class="w-20 h-20 rounded-full bg-indigo-600 text-white text-2xl font-semibold flex items-center justify-center border-4 border-indigo-100">
                                    <?php echo htmlspecialchars($_pmInitials); ?>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-white text-[20px]">photo_camera</span>
                            </div>
                        </div>
                        <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/webp" class="hidden"/>
                        <p class="text-xs text-slate-400 mt-2">JPG, PNG ou WebP · máx. 2MB</p>
                    </div>

                    <!-- Profile Form -->
                    <form id="profile-form" onsubmit="submitProfile(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nome</label>
                                <input type="text" id="profile-name" name="name"
                                       class="form-input w-full"
                                       value="<?php echo htmlspecialchars($_pmName); ?>"
                                       placeholder="Seu nome completo" required/>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Email</label>
                                <input type="email" id="profile-email" name="email"
                                       class="form-input w-full"
                                       value="<?php echo htmlspecialchars($_pmEmail); ?>"
                                       placeholder="seu@email.com" required/>
                            </div>
                            <div id="email-password-field" class="hidden">
                                <label class="block text-xs font-medium text-slate-500 mb-1">
                                    Senha atual
                                    <span class="text-slate-400 font-normal">(confirmar troca de email)</span>
                                </label>
                                <input type="password" id="profile-email-password" name="email_password"
                                       class="form-input w-full" placeholder="••••••••"/>
                            </div>
                            <div id="profile-error"
                                 class="hidden text-sm text-red-500 bg-red-50 rounded-xl px-3 py-2"></div>
                            <div id="profile-success"
                                 class="hidden text-sm text-green-600 bg-green-50 rounded-xl px-3 py-2">
                                Perfil atualizado com sucesso!
                            </div>
                            <button type="submit"
                                    class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Salvar alterações
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ── ABA SENHA ───────────────────────────────────────────── -->
                <div id="tab-content-password" class="hidden">
                    <form id="password-form" onsubmit="submitPassword(event)">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Senha atual</label>
                                <div class="relative">
                                    <input type="password" id="current-password" name="current_password"
                                           class="form-input w-full pr-10" placeholder="••••••••" required/>
                                    <button type="button"
                                            onclick="togglePwdVisibility('current-password', this)"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nova senha</label>
                                <div class="relative">
                                    <input type="password" id="new-password" name="new_password"
                                           class="form-input w-full pr-10" placeholder="••••••••" required
                                           oninput="validatePwdStrength(this.value)"/>
                                    <button type="button"
                                            onclick="togglePwdVisibility('new-password', this)"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                                <div class="mt-2 space-y-1">
                                    <div id="req-length" class="flex items-center gap-1.5 text-xs text-slate-400">
                                        <span class="material-symbols-outlined text-[14px]">circle</span>
                                        Mínimo 8 caracteres
                                    </div>
                                    <div id="req-upper" class="flex items-center gap-1.5 text-xs text-slate-400">
                                        <span class="material-symbols-outlined text-[14px]">circle</span>
                                        Uma letra maiúscula
                                    </div>
                                    <div id="req-number" class="flex items-center gap-1.5 text-xs text-slate-400">
                                        <span class="material-symbols-outlined text-[14px]">circle</span>
                                        Um número
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Confirmar nova senha</label>
                                <input type="password" id="confirm-password" name="confirm_password"
                                       class="form-input w-full" placeholder="••••••••" required/>
                            </div>
                            <div id="password-error"
                                 class="hidden text-sm text-red-500 bg-red-50 rounded-xl px-3 py-2"></div>
                            <div id="password-success"
                                 class="hidden text-sm text-green-600 bg-green-50 rounded-xl px-3 py-2">
                                Senha alterada com sucesso!
                            </div>
                            <button type="submit"
                                    class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Alterar senha
                            </button>
                        </div>
                    </form>
                </div>

            </div><!-- /tab contents -->
        </div>
    </div>

    <script>
    // ── Profile Modal JS ─────────────────────────────────────────────────────
    const _originalEmail = <?php echo json_encode($_pmEmail); ?>;

    function openProfileModal() {
        document.getElementById('profile-dropdown').classList.add('hidden');
        document.getElementById('profile-modal').classList.remove('hidden');
        switchProfileTab('profile');
    }

    function closeProfileModal() {
        document.getElementById('profile-modal').classList.add('hidden');
        document.getElementById('profile-form').reset();
        document.getElementById('password-form').reset();
        document.getElementById('email-password-field').classList.add('hidden');
        ['profile-error','profile-success','password-error','password-success'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
        });
        validatePwdStrength('');
    }

    // Close modal on overlay click
    document.getElementById('profile-modal').addEventListener('click', function(e) {
        if (e.target === this) closeProfileModal();
    });

    function switchProfileTab(tab) {
        ['profile', 'password'].forEach(t => {
            document.getElementById('tab-content-' + t).classList.toggle('hidden', t !== tab);
            const btn = document.getElementById('tab-' + t);
            if (t === tab) {
                btn.className = 'pb-3 mr-6 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600 transition-colors';
            } else {
                btn.className = 'pb-3 text-sm font-medium text-slate-400 border-b-2 border-transparent transition-colors';
            }
        });
        // fix mr-6 for profile tab only
        document.getElementById('tab-profile').classList.add('mr-6');
    }

    // Show/hide email-password field when email changes
    document.getElementById('profile-email').addEventListener('input', function () {
        const changed = this.value.trim() !== _originalEmail;
        document.getElementById('email-password-field').classList.toggle('hidden', !changed);
    });

    // Avatar preview
    document.getElementById('avatar-input').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            showMsg('profile-error', 'A imagem deve ter no máximo 2MB.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview  = document.getElementById('avatar-preview');
            const initials = document.getElementById('avatar-initials-preview');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            initials.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    });

    // Password strength indicator
    function validatePwdStrength(value) {
        const setReq = (id, ok) => {
            const el   = document.getElementById(id);
            const icon = el.querySelector('span');
            icon.textContent = ok ? 'check_circle' : 'circle';
            el.className = 'flex items-center gap-1.5 text-xs ' + (ok ? 'text-green-500' : 'text-slate-400');
        };
        setReq('req-length', value.length >= 8);
        setReq('req-upper',  /[A-Z]/.test(value));
        setReq('req-number', /\d/.test(value));
    }

    function togglePwdVisibility(inputId, btn) {
        const input  = document.getElementById(inputId);
        const isHide = input.type === 'password';
        input.type   = isHide ? 'text' : 'password';
        btn.querySelector('span').textContent = isHide ? 'visibility_off' : 'visibility';
    }

    function showMsg(id, msg) {
        const el = document.getElementById(id);
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    // ── Submit Profile (name + email + optional avatar) ───────────────────
    async function submitProfile(e) {
        e.preventDefault();
        ['profile-error','profile-success'].forEach(id => document.getElementById(id).classList.add('hidden'));

        const avatarInput = document.getElementById('avatar-input');

        // 1. Upload avatar if file selected
        if (avatarInput.files.length > 0) {
            const fd = new FormData();
            fd.append('avatar', avatarInput.files[0]);
            try {
                const res  = await fetch('<?php echo htmlspecialchars($_pmAppUrl); ?>/api/profile/avatar', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': getCsrf() },
                    body: fd,
                });
                const data = await res.json();
                if (!res.ok || !data.ok) {
                    showMsg('profile-error', data.error?.message ?? 'Erro ao enviar avatar.');
                    return;
                }
                updateNavAvatar(data.avatar_url);
            } catch (err) {
                showMsg('profile-error', 'Erro de rede ao enviar avatar.');
                return;
            }
        }

        // 2. Update name + email
        const body = {
            name:           document.getElementById('profile-name').value.trim(),
            email:          document.getElementById('profile-email').value.trim(),
            email_password: document.getElementById('profile-email-password').value || null,
        };
        try {
            const res  = await fetch('<?php echo htmlspecialchars($_pmAppUrl); ?>/api/profile/update', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body:    JSON.stringify(body),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                const detail = data.error?.details ?? {};
                const msg    = Object.values(detail).flat().join(' ') || data.error?.message || 'Erro ao salvar.';
                showMsg('profile-error', msg);
                return;
            }
            // Update nav display name
            const nameEl = document.getElementById('nav-user-name');
            if (nameEl) nameEl.textContent = data.name;
            showMsg('profile-success', 'Perfil atualizado com sucesso!');
        } catch (err) {
            showMsg('profile-error', 'Erro de rede.');
        }
    }

    // ── Submit Password ───────────────────────────────────────────────────
    async function submitPassword(e) {
        e.preventDefault();
        ['password-error','password-success'].forEach(id => document.getElementById(id).classList.add('hidden'));

        const newPwd     = document.getElementById('new-password').value;
        const confirmPwd = document.getElementById('confirm-password').value;

        if (newPwd !== confirmPwd) {
            showMsg('password-error', 'As senhas não coincidem.');
            return;
        }

        try {
            const res  = await fetch('<?php echo htmlspecialchars($_pmAppUrl); ?>/api/profile/password', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                body:    JSON.stringify({
                    current_password: document.getElementById('current-password').value,
                    new_password:     newPwd,
                    confirm_password: confirmPwd,
                }),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                const detail = data.error?.details ?? {};
                const msg    = Object.values(detail).flat().join(' ') || data.error?.message || 'Erro ao alterar senha.';
                showMsg('password-error', msg);
                return;
            }
            document.getElementById('password-form').reset();
            validatePwdStrength('');
            showMsg('password-success', 'Senha alterada com sucesso!');
        } catch (err) {
            showMsg('password-error', 'Erro de rede.');
        }
    }

    // ── Update avatar in navbar after upload ──────────────────────────────
    function updateNavAvatar(url) {
        const container = document.querySelector('#profile-menu-btn .flex-shrink-0');
        if (!container) return;
        container.innerHTML = `<img src="${url}" alt="Avatar" class="w-full h-full object-cover">`;
    }
    </script>
</body>
</html>
