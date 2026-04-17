<div class="h-full flex flex-col p-8 space-y-6 max-w-7xl mx-auto overflow-hidden">
    <section>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-outfit text-3xl font-bold text-slate-900 tracking-tight">Mensagens</h2>
                <p class="text-slate-500 text-sm">Comunique-se com seus colegas de equipe em tempo real.</p>
            </div>
        </div>
    </section>

    <div class="flex-1 bg-white rounded-[40px] border border-slate-100 shadow-sm overflow-hidden flex divide-x divide-slate-50">

        <!-- Sidebar: Conversations -->
        <div class="w-80 flex flex-col bg-slate-50/30">
            <div class="p-6">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
                    <input type="text" placeholder="Buscar conversas..."
                           class="w-full h-10 pl-10 pr-4 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 font-medium">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-2 space-y-1">
                <?php if (empty($conversations)): ?>
                    <div class="text-center py-12 px-4">
                        <i data-lucide="message-square" class="w-12 h-12 text-slate-200 mx-auto block mb-2"></i>
                        <p class="text-slate-400 text-xs">Nenhuma conversa ainda.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv):
                        $initials = implode('', array_map(
                            fn($w) => mb_strtoupper(mb_substr($w, 0, 1)),
                            array_slice(explode(' ', trim($conv['peer_name'] ?? 'U')), 0, 2)
                        ));
                        $unread = (int) ($conv['unread'] ?? 0);
                        $time   = $conv['last_time'] ? date('H:i', strtotime($conv['last_time'])) : '';
                    ?>
                    <button class="w-full flex items-center gap-3 p-4 rounded-3xl hover:bg-white hover:shadow-sm transition-all group text-left"
                            onclick="openConversation(<?php echo (int)$conv['peer_id']; ?>, <?php echo htmlspecialchars(json_encode($conv['peer_name']), ENT_QUOTES); ?>)">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-0.5">
                                <span class="text-sm font-bold text-slate-900 truncate"><?php echo htmlspecialchars($conv['peer_name'] ?? ''); ?></span>
                                <span class="text-[10px] text-slate-400 flex-shrink-0 ml-2"><?php echo htmlspecialchars($time); ?></span>
                            </div>
                            <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($conv['last_msg'] ?? ''); ?></p>
                        </div>
                        <?php if ($unread > 0): ?>
                            <div class="bg-indigo-600 text-white text-[9px] font-bold w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0">
                                <?php echo $unread; ?>
                            </div>
                        <?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col" id="chat-area">
            <!-- Empty state -->
            <div id="chat-empty" class="flex-1 flex items-center justify-center text-center">
                <div>
                    <i data-lucide="messages-square" class="w-16 h-16 text-slate-200 mx-auto block mb-3"></i>
                    <p class="text-slate-400 font-medium">Selecione uma conversa</p>
                    <p class="text-slate-300 text-xs mt-1">ou inicie uma nova mensagem</p>
                </div>
            </div>

            <!-- Active chat (hidden until conversation selected) -->
            <div id="chat-active" class="flex-1 flex flex-col hidden">
                <div class="p-5 flex items-center gap-3 border-b border-slate-50">
                    <div id="chat-peer-avatar" class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-sm"></div>
                    <div>
                        <h4 id="chat-peer-name" class="text-sm font-bold text-slate-900"></h4>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-8 space-y-4 bg-slate-50/20" id="chat-messages"></div>

                <div class="p-6 bg-white border-t border-slate-50">
                    <div class="flex items-center gap-3 bg-slate-50 p-2 rounded-[28px] border border-slate-100 focus-within:border-indigo-200 transition-colors">
                        <input type="text" id="chat-input" placeholder="Escreva sua mensagem..."
                               class="flex-1 bg-transparent border-none focus:outline-none text-sm font-medium text-slate-700 px-2"
                               onkeydown="if(event.key==='Enter') sendMessage()">
                        <button onclick="sendMessage()" class="w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center hover:bg-indigo-700 transition-smooth shadow-md shadow-indigo-100">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let activePeerId = null;
const APP_URL = <?php echo json_encode(($app_url ?? '') . '/index.php'); ?>;
const MY_NAME  = <?php echo json_encode($user_name ?? ''); ?>;

function getInitials(name) {
    return name.trim().split(' ').slice(0,2).map(w => w[0]?.toUpperCase() || '').join('');
}

function openConversation(peerId, peerName) {
    activePeerId = peerId;
    document.getElementById('chat-empty').classList.add('hidden');
    document.getElementById('chat-active').classList.remove('hidden');
    document.getElementById('chat-peer-name').textContent   = peerName;
    document.getElementById('chat-peer-avatar').textContent = getInitials(peerName);
    loadMessages(peerId);
}

async function loadMessages(peerId) {
    const box = document.getElementById('chat-messages');
    box.innerHTML = '<p class="text-xs text-slate-300 text-center">Carregando…</p>';
    try {
        const r    = await fetch(`${APP_URL}/api/messages?peer_id=${peerId}`);
        const msgs = await r.json();
        box.innerHTML = '';
        (Array.isArray(msgs) ? msgs : []).forEach(m => appendMessage(m));
        box.scrollTop = box.scrollHeight;
    } catch { box.innerHTML = '<p class="text-xs text-red-300 text-center">Erro ao carregar.</p>'; }
}

function appendMessage(m) {
    const box   = document.getElementById('chat-messages');
    const isOwn = m.is_own;
    const div   = document.createElement('div');
    div.className = `flex items-end gap-2 ${isOwn ? 'flex-row-reverse' : ''}`;
    div.innerHTML = `
        <div class="w-7 h-7 rounded-lg ${isOwn ? 'bg-indigo-600' : 'bg-slate-200'} flex items-center justify-center text-[10px] font-bold ${isOwn ? 'text-white' : 'text-slate-600'} flex-shrink-0">
            ${isOwn ? getInitials(MY_NAME) : getInitials(m.sender_name || '?')}
        </div>
        <div class="max-w-[70%] ${isOwn ? 'bg-indigo-600 text-white rounded-3xl rounded-tr-none' : 'bg-white border border-slate-100 text-slate-700 rounded-3xl rounded-tl-none'} p-3 shadow-sm">
            <p class="text-sm leading-relaxed">${m.content.replace(/</g,'&lt;')}</p>
            <span class="text-[9px] ${isOwn ? 'text-indigo-300' : 'text-slate-300'} font-bold mt-1 block ${isOwn ? 'text-right' : ''}">${m.time || ''}</span>
        </div>`;
    box.appendChild(div);
}

async function sendMessage() {
    if (!activePeerId) return;
    const input   = document.getElementById('chat-input');
    const content = input.value.trim();
    if (!content) return;
    input.value = '';
    try {
        const r = await fetch(`${APP_URL}/api/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
            body: JSON.stringify({ receiver_id: activePeerId, content }),
        });
        const msg = await r.json();
        if (!msg.error) {
            appendMessage({ ...msg, is_own: true, content });
            document.getElementById('chat-messages').scrollTop = 99999;
        }
    } catch {}
}
</script>
