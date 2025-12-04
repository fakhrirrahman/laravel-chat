
export function initializeChat() {
    const chatBox = document.getElementById('chat-box');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message');
    const onlineCount = document.getElementById('online-count');

    if (!chatBox || !form || !input) return;

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    scrollToBottom();

    Echo.join('chat')
        .here(users => {
            onlineCount.textContent = `${users.length} online`;
        })
        .joining(user => {
            onlineCount.textContent = `${parseInt(onlineCount.textContent) + 1} online`;
        })
        .leaving(user => {
            onlineCount.textContent = `${Math.max(0, parseInt(onlineCount.textContent) - 1)} online`;
        })
        .listen('MessageSent', (e) => {
            const messageText = typeof e.message === 'string' ? e.message : e.message.message;
            const userName = typeof e.user_name === 'string' ? e.user_name : (e.user_name?.name ?? 'Unknown');

            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start space-x-3 animate-fade-in';
            messageDiv.innerHTML = `
            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-sm font-medium">${userName.charAt(0)}</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <span class="font-semibold text-gray-900">${userName}</span>
                    <span class="text-xs text-gray-500">just now</span>
                </div>
                <div class="mt-1 p-3 bg-white rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-800">${messageText}</p>
                </div>
            </div>
        `;
            chatBox.appendChild(messageDiv);
            scrollToBottom();
        });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!input.value.trim()) return;

        const message = input.value.trim();
        try {
            const response = await fetch("/chat/send", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ message })
            });
            if (response.ok) input.value = '';
        } catch (err) { console.error(err); }
    });

    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });
}
