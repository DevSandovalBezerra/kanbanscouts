<?php
function docIconAndColor(string $mime): array {
    if (str_starts_with($mime, 'image/'))       return ['image',          'sky'];
    if ($mime === 'application/pdf')             return ['file-text',      'rose'];
    if (str_contains($mime, 'word'))             return ['file-text',      'blue'];
    if (str_starts_with($mime, 'text/'))         return ['file-text',      'slate'];
    return ['paperclip', 'slate'];
}

function formatSize(int $bytes): string {
    if ($bytes < 1024)     return $bytes . ' B';
    if ($bytes < 1048576)  return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>
<div class="h-full p-8 space-y-10 max-w-7xl mx-auto">
    <section>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Documentos del Proyecto</h2>
                    <div class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-amber-100">Repositorio</div>
                </div>
                <p class="text-slate-500 text-base">Arquivos anexados às tarefas do seu espaço de trabalho.</p>
            </div>
        </div>
    </section>

    <?php if (empty($attachments)): ?>
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <i data-lucide="folder" class="w-16 h-16 text-slate-200 mb-4"></i>
            <h3 class="font-outfit text-xl font-bold text-slate-400">Ningún documento aún</h3>
            <p class="text-slate-400 text-sm mt-2">Anexe arquivos a uma tarefa para vê-los aqui.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($attachments as $att):
                [$icon, $color] = docIconAndColor($att['mime_type']);
                $ext = strtoupper(pathinfo($att['filename'], PATHINFO_EXTENSION) ?: '—');
            ?>
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all p-6 group">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-<?php echo $color; ?>-50 rounded-2xl flex items-center justify-center text-<?php echo $color; ?>-600 border border-<?php echo $color; ?>-100 group-hover:scale-110 transition-smooth">
                        <i data-lucide="<?php echo $icon; ?>" class="w-6 h-6"></i>
                    </div>
                </div>

                <h4 class="font-bold text-slate-800 text-sm truncate mb-1" title="<?php echo htmlspecialchars($att['filename']); ?>">
                    <?php echo htmlspecialchars($att['filename']); ?>
                </h4>
                <p class="text-[10px] text-slate-400 truncate mb-1">
                    Tarea: <?php echo htmlspecialchars($att['task_title'] ?? '—'); ?>
                </p>
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $ext; ?></span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span class="text-[10px] font-bold text-slate-400"><?php echo formatSize((int)$att['size_bytes']); ?></span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span class="text-[10px] text-slate-400"><?php echo htmlspecialchars($att['uploader_name'] ?? ''); ?></span>
                </div>

                <div class="flex gap-2">
                    <a href="<?php echo htmlspecialchars(($base_path ?? '') . '/uploads/' . $att['filepath']); ?>"
                       download="<?php echo htmlspecialchars($att['filename']); ?>"
                       class="flex-1 py-2 text-center bg-slate-50 text-slate-500 rounded-xl text-[10px] font-bold uppercase transition-all hover:bg-indigo-50 hover:text-indigo-600">
                        Download
                    </a>
                    <?php if (str_starts_with($att['mime_type'], 'image/')): ?>
                    <a href="<?php echo htmlspecialchars(($base_path ?? '') . '/uploads/' . $att['filepath']); ?>"
                       target="_blank"
                       class="flex-1 py-2 text-center border border-slate-100 text-slate-400 rounded-xl text-[10px] font-bold uppercase transition-all hover:border-indigo-100 hover:text-indigo-600">
                        Abrir
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
