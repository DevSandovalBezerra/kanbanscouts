<div class="h-full p-8 space-y-10 max-w-7xl mx-auto">
    <!-- Header Section -->
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                   <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Calendario del Equipo</h2>
                   <div class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-blue-100">Próximos Eventos</div>
                </div>
                <p class="text-slate-500 text-base">Agende reuniões, marcos de projeto e prazos importantes.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="openEventModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 transition-smooth shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                    <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                    Nuevo Evento
                </button>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Calendar Grid Placeholder (Simple JS-less for now) -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-100 shadow-sm p-8 overflow-hidden relative">
                 <div class="flex items-center justify-between mb-8">
                     <h3 class="font-outfit text-xl font-bold text-slate-900"><?php echo date('F Y'); ?></h3>
                     <div class="flex gap-2">
                        <button class="p-2 hover:bg-slate-50 rounded-xl transition-colors"><span class="material-symbols-outlined">chevron_left</span></button>
                        <button class="p-2 hover:bg-slate-50 rounded-xl transition-colors"><span class="material-symbols-outlined">chevron_right</span></button>
                     </div>
                 </div>
                 
                 <div class="grid grid-cols-7 gap-px bg-slate-100 rounded-3xl overflow-hidden border border-slate-100">
                    <?php 
                    $days = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                    foreach ($days as $day): ?>
                        <div class="bg-slate-50 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $day; ?></div>
                    <?php endforeach; ?>
                    
                    <?php 
                    $firstDay = date('w', strtotime(date('Y-m-01')));
                    $daysInMonth = date('t');
                    for ($i = 0; $i < ($firstDay + $daysInMonth); $i++): 
                        $dayNum = $i - $firstDay + 1;
                        $isCurrentMonth = ($dayNum > 0);
                        $isToday = $dayNum == date('j');
                    ?>
                        <div class="bg-white aspect-square p-2 border-slate-50 relative group cursor-pointer hover:bg-indigo-50/30 transition-colors">
                            <?php if ($isCurrentMonth): ?>
                                <span class="text-xs font-bold <?php echo $isToday ? 'bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-md' : 'text-slate-400 group-hover:text-indigo-600'; ?>">
                                    <?php echo $dayNum; ?>
                                </span>
                                
                                <!-- Mocked event pill if events match (simplified) -->
                                <?php foreach ($events ?? [] as $event): 
                                    if (date('j', strtotime($event->startTime)) == $dayNum && date('m', strtotime($event->startTime)) == date('m')): ?>
                                        <div class="mt-1 h-1.5 w-1.5 rounded-full bg-indigo-500 mx-auto"></div>
                                    <?php break; endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                 </div>
            </div>
        </div>

        <!-- Agenda Column -->
        <div class="space-y-6">
            <h3 class="font-outfit text-xl font-bold text-slate-900 border-l-4 border-indigo-600 pl-4">Agenda del Día</h3>
            
            <div class="space-y-4">
                <?php foreach ($events ?? [] as $event): ?>
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                     <div class="flex items-start gap-4">
                         <div class="flex flex-col items-center justify-center w-14 h-14 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                             <span class="text-[10px] font-bold uppercase"><?php echo date('M', strtotime($event->startTime)); ?></span>
                             <span class="text-lg font-bold leading-none"><?php echo date('d', strtotime($event->startTime)); ?></span>
                         </div>
                         <div class="flex-1 min-w-0">
                             <h4 class="font-bold text-slate-800 text-sm truncate"><?php echo $event->title; ?></h4>
                             <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-1 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                <?php echo date('H:i', strtotime($event->startTime)); ?> - <?php echo date('H:i', strtotime($event->endTime)); ?>
                             </p>
                             <p class="text-xs text-slate-500 line-clamp-2 mt-2 leading-relaxed"><?php echo $event->description; ?></p>
                         </div>
                     </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($events)): ?>
                    <div class="p-10 text-center bg-slate-50/50 rounded-[32px] border-2 border-dashed border-slate-100">
                        <i data-lucide="calendar-x" class="w-12 h-12 text-slate-200 mx-auto block"></i>
                        <p class="text-slate-400 mt-2 text-sm italic">Ningún evento programado para hoy.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="event-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-[500px] rounded-[40px] shadow-2xl overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-300">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                    <i data-lucide="calendar-check" class="w-6 h-6"></i>
                </div>
                <div>
                     <h3 class="font-outfit text-xl font-bold text-slate-900">Novo Agendamento</h3>
                     <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Calendario del Equipo</p>
                </div>
            </div>
            <button onclick="closeEventModal()" class="w-10 h-10 bg-white text-slate-400 hover:text-slate-600 rounded-2xl transition-smooth flex items-center justify-center shadow-sm">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="event-form" class="p-10 space-y-6">
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1">Título do Evento</label>
                <input type="text" id="event-title" required placeholder="Ex: Reunião de Daily, Review Geral" class="w-full h-14 px-5 bg-slate-50 border border-slate-100 rounded-2xl text-base focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1">Início</label>
                    <input type="datetime-local" id="event-start" required class="w-full h-14 px-5 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1">Fim</label>
                    <input type="datetime-local" id="event-end" required class="w-full h-14 px-5 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1">Descrição</label>
                <textarea id="event-desc" placeholder="Detalhes do encontro..." rows="3" class="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl text-base focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium resize-none"></textarea>
            </div>
            
            <div class="pt-4 flex gap-3">
                <button type="button" onclick="closeEventModal()" class="flex-1 py-4 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" class="flex-[2] py-4 bg-indigo-600 text-white rounded-2xl text-sm font-bold shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all hover:scale-[1.02] active:scale-95">Agendar Agora</button>
            </div>
        </form>
    </div>
</div>

<script>
    const BASE = '<?php echo $app_url ?? ''; ?>';

    function openEventModal() {
        document.getElementById('event-modal').classList.remove('hidden');
    }

    function closeEventModal() {
        document.getElementById('event-modal').classList.add('hidden');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    async function readJsonResponse(response) {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch {
            return { ok: false, error: text?.slice(0, 200) || 'Resposta inválida do servidor.' };
        }
    }

    document.getElementById('event-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const title = document.getElementById('event-title').value;
        const start_time = document.getElementById('event-start').value;
        const end_time = document.getElementById('event-end').value;
        const description = document.getElementById('event-desc').value;

        const url = BASE + '/api/events/create';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrfToken()
                },
                body: JSON.stringify({ title, start_time, end_time, description })
            });
            const result = await readJsonResponse(response);
            if (response.ok && result.ok) {
                await Swal.fire({ title: 'Agendado', text: 'Evento criado com sucesso.', icon: 'success' });
                location.reload();
                return;
            }
            await Swal.fire({ title: 'Erro', text: result.error || 'Falha ao criar evento.', icon: 'error' });
        } catch (e) {
            console.error(e);
            await Swal.fire({ title: 'Erro', text: 'Falha na comunicação com o servidor.', icon: 'error' });
        }
    });
</script>
