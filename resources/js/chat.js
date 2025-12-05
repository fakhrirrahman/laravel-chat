export function initializeChat() {
    const chatBox = document.getElementById("chat-box");
    const form = document.getElementById("chat-form");
    const input = document.getElementById("message");
    const onlineCount = document.getElementById("online-count");

    if (!chatBox || !form || !input) return;

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function addMessageToChat(message, userName, isOwnMessage = false) {
        const safeMessage = message || 'Empty message';
        const safeUserName = userName || 'Unknown User';
        const userInitial = safeUserName.charAt(0).toUpperCase() || '?';
        
        const messageDiv = document.createElement("div");
        messageDiv.className = "flex items-start space-x-3 animate-fade-in";
        
        const avatarColor = isOwnMessage ? "bg-green-500" : "bg-indigo-500";
        
        messageDiv.innerHTML = `
            <div class="w-8 h-8 ${avatarColor} rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-sm font-medium">${userInitial}</span>
            </div>
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <span class="font-semibold text-gray-900">${safeUserName}</span>
                    <span class="text-xs text-gray-500">just now</span>
                    ${isOwnMessage ? '<span class="text-xs text-green-600 font-medium">You</span>' : ''}
                </div>
                <div class="mt-1 p-3 bg-white rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-800">${safeMessage}</p>
                </div>
            </div>
        `;
        
        chatBox.appendChild(messageDiv);
        scrollToBottom();
    }

    scrollToBottom();

    let onlineUsers = [];

    Echo.join("chat")
        .here((users) => {
            onlineUsers = users.map(u => u.id);
            window.onlineUsers = onlineUsers;
            onlineCount.textContent = `${users.length} online`;
        })
        .joining((user) => {
            onlineUsers.push(user.id);
            window.onlineUsers = onlineUsers;
            onlineCount.textContent = `${
                parseInt(onlineCount.textContent) + 1
            } online`;
        })
        .leaving((user) => {
            onlineUsers = onlineUsers.filter(id => id !== user.id);
            window.onlineUsers = onlineUsers;
            onlineCount.textContent = `${Math.max(
                0,
                parseInt(onlineCount.textContent) - 1
            )} online`;
        })
        .listen("MessageSent", (e) => {
            
            let messageText;
            if (typeof e.message === 'string') {
                messageText = e.message;
            } else if (e.message && e.message.message) {
                messageText = e.message.message;
            } else {
                messageText = 'Unknown message';
            }
            
            const userName = e.user_name || 'Unknown User';
            
            if (!messageText || messageText === 'undefined') {
                console.error('Invalid message text received:', messageText);
                return;
            }
            
            const currentUserId = document.querySelector('[data-user-id]')?.getAttribute('data-user-id');
            const isOwnMessage = currentUserId && parseInt(e.user_id) === parseInt(currentUserId);
            
            addMessageToChat(messageText, userName, isOwnMessage);
        });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!input.value.trim()) return;

        const message = input.value.trim();
        const currentUserName = document.querySelector('[data-user-name]')?.getAttribute('data-user-name') || 'You';
        
        try {
            const response = await fetch("/chat/send", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: JSON.stringify({ message }),
            });
            
            if (response.ok) {
                input.value = "";
            } else {
                console.error('Failed to send message');
            }
        } catch (err) {
            console.error('Error sending message:', err);
        }
    });

    input.addEventListener("keypress", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event("submit"));
        }
    });
}
