<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Realtime Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Chat Container -->
                    <div class="flex flex-col h-[600px]">
                        <!-- Chat Header -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Group Chat</h3>
                                    <p class="text-sm text-gray-500" id="online-count">Loading...</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-sm text-gray-600">Live</span>
                            </div>
                        </div>

                        <!-- Messages Container -->
                        <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                            @foreach($messages as $msg)
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-sm font-medium">{{ substr($msg->user->name, 0, 1) }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-gray-900">{{ $msg->user->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-1 p-3 bg-white rounded-lg shadow-sm border border-gray-200">
                                        <p class="text-gray-800">{{ $msg->message }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Message Input -->
                        <div class="p-4 border-t border-gray-200 bg-white">
                            <form id="chat-form" class="flex items-end space-x-3">
                                <div class="flex-1">
                                    <x-text-input
                                        id="message"
                                        type="text"
                                        class="w-full"
                                        placeholder="Type your message..."
                                        wire:ignore
                                    />
                                </div>
                                <x-primary-button type="submit" class="flex-shrink-0">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Send
                                </x-primary-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        @vite(['resources/js/app.js']);

    <script>
        // Wait for Echo to be available
        function initializeChat() {
            if (typeof Echo === 'undefined') {
                setTimeout(initializeChat, 100);
                return;
            }

            const chatBox = document.getElementById('chat-box');
            const form = document.getElementById('chat-form');
            const input = document.getElementById('message');
            const onlineCount = document.getElementById('online-count');

            // Auto-scroll to bottom
            function scrollToBottom() {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            // Initial scroll
            scrollToBottom();

            // Listen to Reverb Presence Channel
            Echo.join('chat')
                .here(users => {
                    onlineCount.textContent = `${users.length} online`;
                    console.log('Online users:', users);
                })
                .joining(user => {
                    onlineCount.textContent = `${parseInt(onlineCount.textContent) + 1} online`;
                    console.log(user.name + ' joined');
                })
                .leaving(user => {
                    onlineCount.textContent = `${Math.max(0, parseInt(onlineCount.textContent) - 1)} online`;
                    console.log(user.name + ' left');
                })
                .listen('MessageSent', (e) => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'flex items-start space-x-3 animate-fade-in';
                    messageDiv.innerHTML = `
                        <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-sm font-medium">${e.user_name.charAt(0)}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-900">${e.user_name}</span>
                                <span class="text-xs text-gray-500">just now</span>
                            </div>
                            <div class="mt-1 p-3 bg-white rounded-lg shadow-sm border border-gray-200">
                                <p class="text-gray-800">${e.message}</p>
                            </div>
                        </div>
                    `;
                    chatBox.appendChild(messageDiv);
                    scrollToBottom();
                });

            // Handle form submission
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!input.value.trim()) return;

                const message = input.value.trim();

                try {
                    const response = await fetch("{{ route('chat.send') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: message })
                    });

                    if (response.ok) {
                        input.value = '';
                    } else {
                        console.error('Failed to send message');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                }
            });

            // Add enter key support
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });
        }

        // Initialize chat when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeChat);
        } else {
            initializeChat();
        }
    </script>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-app-layout>
