<div class="flex flex-col h-[600px]">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
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

    <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
        @foreach ($messages as $msg)
            <x-chat-message :message="$msg" />
        @endforeach
    </div>

    <div class="p-4 border-t border-gray-200 bg-white">
        <x-chat-input />
    </div>
</div>
