<div class="h-full p-8 space-y-10 max-w-7xl mx-auto">
    <!-- Header Section -->
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                   <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Equipo y Contactos</h2>
                   <div class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-emerald-100">Disponible</div>
                </div>
                <p class="text-slate-500 text-base">Visualize os membros da sua equipe e colabore em tempo real.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4.5 h-4.5"></i>
                    <input type="text" placeholder="Buscar contactos..." class="w-64 h-12 pl-10 pr-4 bg-white border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium shadow-sm">
                </div>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-smooth shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    Invitar
                </button>
            </div>
        </div>
    </section>

    <!-- Contacts Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($contacts ?? [] as $contact): ?>
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all p-6 text-center group">
            <div class="relative inline-block mb-4">
                <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-slate-100 mb-2 mx-auto group-hover:scale-105 transition-smooth duration-300">
                    <img src="<?php echo $contact->avatar; ?>" alt="<?php echo $contact->name; ?>" class="w-full h-full object-cover">
                </div>
                <div class="absolute bottom-2 right-0 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white"></div>
            </div>
            
            <h4 class="font-outfit font-bold text-slate-900 text-lg mb-1 truncate"><?php echo $contact->name; ?></h4>
            <p class="text-xs text-slate-500 font-medium mb-4"><?php echo $contact->email; ?></p>
            
            <div class="flex items-center justify-center gap-2 mb-6">
                <span class="px-2 py-0.5 bg-slate-50 text-slate-500 text-[10px] font-bold uppercase rounded-md">Membro</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button class="flex items-center justify-center gap-2 p-2 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-smooth group/btn">
                    <i data-lucide="message-square" class="w-4.5 h-4.5"></i>
                </button>
                <button class="flex items-center justify-center gap-2 p-2 bg-slate-50 text-slate-400 rounded-xl hover:bg-slate-200 hover:text-slate-700 transition-smooth">
                    <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($contacts)): ?>
            <div class="col-span-full py-20 text-center bg-slate-50/50 border-2 border-dashed border-slate-200 rounded-[40px]">
                <i data-lucide="user-x" class="w-12 h-12 text-slate-200 mx-auto block"></i>
                <p class="text-slate-400 mt-2 font-medium">Ningún miembro encontrado en su empresa.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
