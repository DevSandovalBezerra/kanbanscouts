<div class="h-full flex flex-col p-8 bg-surface/50"
     data-board-id="<?php echo $board_id; ?>"
     data-company-id="<?php echo $company_id; ?>">

    <!-- Board Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight"><?php echo htmlspecialchars($board_name); ?></h2>
                <div class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-indigo-100">ATIVO</div>
            </div>
            <div class="flex items-center gap-2 text-slate-500 text-sm">
                <i data-lucide="folder" class="w-4 h-4"></i>
                <span class="font-medium"><?php echo htmlspecialchars($project_name); ?></span>
                <span class="mx-1 text-slate-300">/</span>
                <span class="font-medium text-slate-400">Linha do Tempo: <?php echo date('d M') . ' – ' . date('d M', strtotime('+30 days')); ?></span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-2xl font-bold flex items-center gap-2 transition-smooth shadow-xl shadow-indigo-100 hover:scale-[1.02] active:scale-95">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Nova Tarefa
            </button>
            <button class="bg-white border border-slate-200 hover:border-slate-300 px-4 py-2.5 rounded-2xl text-slate-700 font-bold flex items-center gap-2 transition-smooth shadow-sm">
                <i data-lucide="filter" class="w-5 h-5"></i>
                Filtros
            </button>
        </div>
    </div>

    <!-- Board Main Area -->
    <div class="flex-1 overflow-x-auto overflow-y-hidden flex gap-8 pb-6 custom-scrollbar" id="kanban-board">
        <?php foreach ($columns as $idx => $column):
            $bgColors     = ['bg-rose-50/60','bg-amber-50/60','bg-sky-50/60','bg-purple-50/60'];
            $accentColors = ['bg-rose-500','bg-amber-500','bg-sky-500','bg-purple-500'];
            $bg     = $bgColors[$idx % count($bgColors)];
            $accent = $accentColors[$idx % count($accentColors)];
        ?>
        <div class="w-80 min-w-[20rem] flex flex-col <?php echo $bg; ?> rounded-[32px] border border-slate-100/50 shadow-sm" data-column-id="<?php echo $column->id; ?>">
            <div class="p-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full <?php echo $accent; ?>"></div>
                    <h3 class="font-outfit text-base font-bold text-slate-800 uppercase tracking-wide cursor-pointer"
                        data-action="rename-column"
                        role="button"
                        tabindex="0"><?php echo htmlspecialchars($column->name); ?></h3>
                    <span class="text-[11px] font-bold bg-white/80 text-slate-500 px-2.5 py-0.5 rounded-full shadow-sm count">0</span>
                </div>
            </div>
            <div class="flex-1 px-4 py-2 overflow-y-auto space-y-4 task-list min-h-[300px]" id="column-<?php echo $column->id; ?>">
                <!-- Tasks injected by JS -->
            </div>
            <div class="p-4">
                <button onclick="openCreateModal(<?php echo $column->id; ?>)" class="w-full p-3 text-sm font-bold text-slate-500 hover:text-indigo-600 hover:bg-white/80 rounded-2xl flex items-center justify-center gap-2 transition-smooth group">
                    <i data-lucide="plus" class="w-5 h-5 group-hover:rotate-90 transition-smooth"></i>
                    Adicionar Tarefa
                </button>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Add Column -->
        <div class="w-80 min-w-[20rem] flex items-stretch py-4">
            <div class="w-full flex items-center justify-center border-2 border-dashed border-slate-200 rounded-[32px] hover:border-indigo-400 hover:bg-white transition-all group cursor-pointer"
                 data-action="add-column" role="button" tabindex="0">
                <div class="text-center group-hover:scale-105 transition-transform">
                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="plus-circle" class="w-6 h-6 text-slate-400 group-hover:text-indigo-600 transition-smooth"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-400 group-hover:text-indigo-600">Nova Coluna</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  CREATE TASK MODAL                                      -->
<!-- ═══════════════════════════════════════════════════════ -->
<div id="create-task-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-lg rounded-[40px] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-outfit text-xl font-bold text-slate-900">Nova Tarefa</h3>
            <button onclick="closeCreateModal()" class="w-10 h-10 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-2xl flex items-center justify-center transition-smooth">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="create-task-form" class="p-8 space-y-5 overflow-y-auto max-h-[75vh]">
            <input type="hidden" name="column_id" id="create-task-col-id">

            <div class="space-y-2">
                <label class="label-xs">Título da Tarefa</label>
                <input type="text" name="title" required placeholder="O que precisa ser feito?"
                       class="form-input">
            </div>

            <div class="space-y-2">
                <label class="label-xs">Descrição</label>
                <div id="create-desc-editor" class="quill-editor bg-slate-50 border border-slate-100 rounded-2xl" style="min-height:100px"></div>
                <input type="hidden" name="description" id="create-desc-input">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="label-xs">Prioridade</label>
                    <select name="priority" class="form-input">
                        <option value="low">Baixa</option>
                        <option value="medium" selected>Média</option>
                        <option value="high">Alta</option>
                        <option value="critical">Crítica</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="label-xs">Story Points</label>
                    <select name="story_points" class="form-input">
                        <option value="">—</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="5">5</option>
                        <option value="8">8</option>
                        <option value="13">13</option>
                        <option value="21">21</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="label-xs">Prazo</label>
                    <input type="date" name="deadline" class="form-input">
                </div>
                <div class="space-y-2">
                    <label class="label-xs">Responsável</label>
                    <select name="assigned_to" id="create-assignee" class="form-input">
                        <option value="">— Nenhum —</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeCreateModal()" class="flex-1 py-3.5 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition-colors">Cancelar</button>
                <button type="submit" class="flex-[1.5] py-3.5 bg-indigo-600 text-white rounded-2xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Criar Tarefa</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!--  TASK DETAIL MODAL                                      -->
<!-- ═══════════════════════════════════════════════════════ -->
<div id="task-modal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-start justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white w-full max-w-4xl rounded-[32px] shadow-2xl my-8 overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">

        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-slate-100 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div id="modal-badges" class="flex items-center gap-2 mb-2 flex-wrap"></div>
                <!-- Título: modo leitura -->
                <div id="modal-title-wrap" class="flex items-start gap-2 group/title">
                    <h3 id="modal-title" class="font-outfit text-2xl font-bold text-slate-900 leading-tight flex-1"></h3>
                    <button onclick="startTitleEdit()"
                            class="opacity-0 group-hover/title:opacity-100 transition-smooth flex-shrink-0 mt-1 p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600"
                            title="Editar título">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                    </button>
                </div>
                <!-- Título: modo edição -->
                <div id="modal-title-edit" class="hidden">
                    <input id="modal-title-input" type="text"
                           class="w-full px-2 py-1 font-outfit text-2xl font-bold text-slate-900 border-b-2 border-indigo-400 bg-transparent focus:outline-none"
                           onkeydown="handleTitleKey(event)"/>
                    <div class="flex gap-2 mt-2">
                        <button onclick="saveTitle()" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-colors">Salvar</button>
                        <button onclick="cancelTitleEdit()" class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">Cancelar</button>
                    </div>
                </div>
            </div>
            <button onclick="closeModal()" class="flex-shrink-0 w-10 h-10 bg-slate-50 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-2xl flex items-center justify-center transition-smooth">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body: two-column layout -->
        <div class="flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-slate-100">

            <!-- LEFT: Description + Checklist + Attachments + Comments -->
            <div class="flex-1 p-8 space-y-8 overflow-y-auto max-h-[70vh]">

                <!-- Description -->
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="section-title mb-0">Descrição</h4>
                        <button id="desc-edit-btn" onclick="toggleDescEditor()" class="btn-ghost-xs">
                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Editar
                        </button>
                    </div>
                    <!-- View mode -->
                    <div id="modal-desc" class="prose-sm text-slate-600 text-sm leading-relaxed min-h-[2rem]"></div>
                    <!-- Edit mode (hidden) -->
                    <div id="desc-editor-wrap" class="hidden">
                        <div id="modal-desc-editor" class="quill-editor bg-slate-50 border border-slate-100 rounded-2xl" style="min-height:120px"></div>
                        <div class="flex gap-2 mt-2">
                            <button id="save-desc-btn" onclick="saveDescription()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">Salvar</button>
                            <button onclick="toggleDescEditor(false)" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">Cancelar</button>
                        </div>
                    </div>
                </section>

                <!-- Checklists -->
                <section id="section-checklists">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="section-title mb-0">Checklists</h4>
                        <button onclick="addChecklist()" class="btn-ghost-xs">
                            <i data-lucide="plus" class="w-4 h-4"></i> Novo checklist
                        </button>
                    </div>
                    <div id="checklists-container" class="space-y-5"></div>
                </section>

                <!-- Attachments -->
                <section id="section-attachments">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="section-title mb-0">Anexos</h4>
                        <label class="btn-ghost-xs cursor-pointer">
                            <i data-lucide="upload" class="w-4 h-4"></i> Enviar arquivo
                            <input type="file" id="attachment-input" class="hidden" multiple>
                        </label>
                    </div>
                    <!-- Drop zone -->
                    <div id="attachment-dropzone" class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center text-slate-400 text-sm hover:border-indigo-300 hover:bg-indigo-50/30 transition-smooth mb-4 cursor-pointer">
                        <i data-lucide="cloud-upload" class="w-8 h-8 mx-auto mb-2 block"></i>
                        Arraste arquivos aqui ou clique para enviar
                    </div>
                    <div id="attachments-container" class="space-y-2"></div>
                </section>

                <!-- Comments -->
                <section id="section-comments">
                    <h4 class="section-title">Comentários</h4>
                    <div id="comments-container" class="space-y-4 mb-4"></div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i>
                        </div>
                        <div class="flex-1">
                            <textarea id="comment-input" rows="2" placeholder="Escreva um comentário..."
                                      class="form-input resize-none text-sm w-full"></textarea>
                            <button onclick="submitComment()" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">
                                Comentar
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- RIGHT: Meta sidebar -->
            <div class="lg:w-72 flex-shrink-0 p-8 space-y-6">

                <!-- Labels -->
                <section>
                    <h4 class="section-title">Labels</h4>
                    <div id="labels-container" class="flex flex-wrap gap-2 mb-3"></div>
                    <div class="flex gap-2">
                        <select id="label-select" class="form-input text-xs flex-1 h-9 py-0"></select>
                        <button onclick="attachLabel()" class="w-9 h-9 bg-indigo-600 text-white rounded-xl flex items-center justify-center hover:bg-indigo-700 transition-smooth flex-shrink-0">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </button>
                    </div>
                </section>

                <!-- Meta fields -->
                <section class="space-y-4">
                    <h4 class="section-title">Detalhes</h4>

                    <div class="meta-row">
                        <i data-lucide="flag" class="w-4 h-4 text-slate-400 mt-0.5"></i>
                        <div>
                            <div class="meta-label">Prioridade</div>
                            <select id="meta-priority-select" onchange="updatePriority(this.value)"
                                    class="text-xs font-bold bg-transparent border border-slate-200 rounded-lg px-2 py-1 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition-all mt-0.5">
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                                <option value="critical">Crítica</option>
                            </select>
                        </div>
                    </div>

                    <div class="meta-row">
                        <i data-lucide="zap" class="w-4 h-4 text-slate-400 mt-0.5"></i>
                        <div>
                            <div class="meta-label">Story Points</div>
                            <div id="meta-sp" class="text-sm font-bold text-slate-700"></div>
                        </div>
                    </div>

                    <div class="meta-row">
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400 mt-0.5"></i>
                        <div class="flex-1">
                            <div class="meta-label">Prazo</div>
                            <div class="flex items-center gap-1 mt-0.5">
                                <input type="date" id="meta-deadline-input"
                                       onchange="updateDeadline(this.value)"
                                       class="text-sm font-medium text-slate-700 bg-transparent border border-slate-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition-all cursor-pointer"/>
                                <button onclick="clearDeadline()" id="clear-deadline-btn"
                                        class="hidden text-slate-300 hover:text-rose-400 transition-smooth flex-shrink-0"
                                        title="Remover prazo">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="meta-row">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400 mt-0.5"></i>
                        <div class="flex-1">
                            <div class="meta-label">Responsável</div>
                            <select id="meta-assignee" onchange="updateAssignee(this.value)"
                                    class="w-full text-sm font-medium text-slate-700 bg-transparent border-none outline-none cursor-pointer hover:text-indigo-600 transition-colors">
                                <option value="">— Nenhum —</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Dependencies -->
                <section id="section-deps">
                    <h4 class="section-title">Dependências</h4>

                    <div class="mb-2">
                        <div class="meta-label mb-1">Bloqueado por</div>
                        <div id="deps-blocked-by" class="space-y-1 text-xs text-slate-500"></div>
                    </div>
                    <div class="mb-3">
                        <div class="meta-label mb-1">Bloqueando</div>
                        <div id="deps-blocking" class="space-y-1 text-xs text-slate-500"></div>
                    </div>
                    <div class="flex gap-2">
                        <input id="dep-task-input" type="number" placeholder="ID da tarefa"
                               class="form-input text-xs flex-1 h-9 py-0">
                        <button onclick="addDependency()" class="w-9 h-9 bg-slate-100 hover:bg-indigo-100 text-slate-600 rounded-xl flex items-center justify-center transition-smooth flex-shrink-0">
                            <i data-lucide="link-2" class="w-5 h-5"></i>
                        </button>
                    </div>
                </section>

                <!-- History anchor (collapsed) -->
                <section>
                    <h4 class="section-title">Histórico</h4>
                    <div id="history-container" class="space-y-2 text-xs text-slate-400 max-h-40 overflow-y-auto"></div>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
/* Utilities referenced in template */
.label-xs       { @apply text-[10px] font-bold text-slate-400 uppercase tracking-widest block ml-1; }
.form-input     { @apply w-full h-12 px-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition-all font-medium; }
.section-title  { @apply text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3; }
.meta-row       { @apply flex items-start gap-3; }
.meta-label     { @apply text-[10px] font-bold text-slate-400 uppercase tracking-widest; }
.btn-ghost-xs   { @apply flex items-center gap-1 text-xs font-bold text-slate-400 hover:text-indigo-600 transition-colors; }

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar { height: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
