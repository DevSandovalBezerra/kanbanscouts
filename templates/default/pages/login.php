<div class="min-h-screen flex items-center justify-center bg-slate-50 p-4 sm:p-0">
    <!-- Main Container: split layout -->
    <div class="w-full max-w-[1000px] h-full sm:h-[600px] bg-white rounded-[32px] overflow-hidden shadow-2xl flex flex-col sm:flex-row">
        
        <!-- Left Column: Form -->
        <div class="flex-1 p-8 sm:p-16 flex flex-col justify-center">
            <!-- Logo area -->
            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i data-lucide="kanban" class="w-6 h-6"></i>
                </div>
                <span class="font-outfit text-xl font-bold text-slate-900 tracking-tight">Kanban<span class="text-indigo-600">Lite</span></span>
            </div>

            <div class="mb-8">
                <h1 class="font-outfit text-3xl font-bold text-slate-900 mb-2">Entre em sua conta</h1>
                <p class="text-slate-500 text-sm font-medium">Por favor, insira seus dados para continuar.</p>
            </div>

            <form id="login-form" class="space-y-5">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 block ml-1">E-mail</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                        <input type="email" id="email" required placeholder="Insira seu e-mail" class="w-full h-12 pl-12 pr-4 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-smooth font-medium placeholder:text-slate-300">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 block ml-1">Senha</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                        <input type="password" id="password" required placeholder="••••••••" class="w-full h-12 pl-12 pr-12 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-smooth font-medium placeholder:text-slate-300">
                        <button type="button" id="toggle-password" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-smooth">
                            <i data-lucide="eye" class="w-5 h-5" id="toggle-password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between px-1 mb-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="remember" class="text-xs font-bold text-slate-600 cursor-pointer">Lembrar por 30 dias</label>
                    </div>
                    <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Esqueci a senha</a>
                </div>

                <button type="submit" class="w-full py-3.5 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-100/50 hover:bg-indigo-700 hover:scale-[1.01] active:scale-95 transition-smooth">
                    Entrar
                </button>

                <div class="relative flex py-3 items-center">
                    <div class="flex-grow border-t border-slate-100"></div>
                    <span class="flex-shrink mx-4 text-[10px] font-bold text-slate-300 uppercase tracking-widest">OU</span>
                    <div class="flex-grow border-t border-slate-100"></div>
                </div>

                <button type="button" class="w-full py-3 border border-slate-200 text-slate-700 rounded-xl font-bold text-sm flex items-center justify-center gap-2 hover:bg-slate-50 transition-smooth">
                    <i data-lucide="id-card" class="w-5 h-5"></i>
                    Acessar com SmartCard
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-slate-400 font-medium">Ao entrar, você concorda com nossos <a href="#" class="text-slate-600 font-bold underline">Termos de Uso</a></p>
        </div>
        
        <!-- Right Column: Banner -->
        <div class="flex-1 bg-indigo-600 p-12 flex flex-col justify-between relative overflow-hidden hidden sm:flex">
             <!-- Abstract shapes for premium feel -->
             <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
             <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-indigo-500/50 rounded-full blur-3xl"></div>
             
             <div class="relative z-10 flex items-center gap-3">
                 <div class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-white border border-white/20">
                    <i data-lucide="box" class="w-6 h-6"></i>
                 </div>
                 <span class="text-white font-outfit font-bold text-xl tracking-tight">KanbanScout</span>
             </div>

             <div class="relative z-10">
                 <h2 class="text-white text-3xl font-outfit font-bold mb-4 leading-tight">Fortalecendo comunidades mais saudáveis</h2>
                 <p class="text-indigo-100 text-sm font-medium">Gestão inteligente e ágil para empresas de vanguarda.</p>
             </div>

             <!-- Illustration -->
             <div class="relative z-10 flex justify-center py-4">
                 <img src="<?php echo $base_path ?? ''; ?>/assets/img/undraw_booking_8vl5.svg" class="w-3/4 h-auto drop-shadow-2xl brightness-110 contrast-125" alt="Banner Illustration">
             </div>

        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    document.getElementById('toggle-password').addEventListener('click', (e) => {
        e.preventDefault();
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('toggle-password-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            passwordInput.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    });

    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.textContent = 'Verificando...';
        btn.disabled = true;
        btn.classList.add('opacity-50');

        try {
            const url = '<?php echo $app_url; ?>/api/auth/login';
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ email, password })
            });
            const result = await response.json();
            if (result.ok) {
                window.location.href = '<?php echo $app_url; ?>/';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Falha no Login',
                    text: result.error?.message || 'Credenciais inválidas ou erro no servidor.',
                    confirmButtonColor: '#6366f1'
                });
                btn.textContent = originalText;
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Erro!', 'Falha na conexão com o servidor.', 'error');
            btn.textContent = originalText;
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        }

    });
</script>
