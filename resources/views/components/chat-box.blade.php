<div class="flex flex-col h-[600px]">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Group Chat</h3>
                <p class="text-sm text-gray-500" id="online-count">Loading...</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button x-data x-on:click="$dispatch('open-modal', 'choose-user-video-call')"
                class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-700">
                Video Call
            </button>
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

<x-modal name="choose-user-video-call" maxWidth="2xl">
    <div class="p-6">
        <h2 class="text-lg font-semibold mb-4">Pilih User untuk Video Call</h2>

        <div class="mb-4">
            <label for="user-select" class="block text-sm font-medium text-gray-700">Pilih User:</label>
            <select id="user-select" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- Pilih User --</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" data-name="{{ $u->name }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end space-x-2">
            <button id="start-video-call-btn" class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-700">
                Mulai Video Call
            </button>
            <button type="button" x-on:click="$dispatch('close-modal', 'choose-user-video-call')"
                class="px-4 py-2 bg-gray-500 text-white rounded-md">
                Tutup
            </button>
        </div>
    </div>
</x-modal>

<div id="invite-notification" class="hidden fixed bottom-4 right-4 p-4 bg-yellow-100 border rounded shadow-lg z-50">
    <p id="invite-message"></p>
    <button id="accept-invite" class="px-4 py-2 bg-green-600 text-white rounded mr-2">Accept</button>
    <button id="decline-invite" class="px-4 py-2 bg-red-600 text-white rounded">Decline</button>
</div>

<div id="video-call-container" class="hidden fixed bottom-4 right-4 w-80 h-60 bg-black rounded shadow-lg z-50">
    <video id="local-video" autoplay playsinline muted class="w-full h-full object-cover rounded"></video>
    <video id="remote-video" autoplay playsinline class="w-full h-full object-cover rounded hidden"></video>
    <button id="end-call" class="absolute top-2 right-2 px-2 py-1 bg-red-600 text-white rounded text-sm">End</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('start-video-call-btn').addEventListener('click', function() {
        const select = document.getElementById('user-select');
        const userId = select.value;
        const userName = select.options[select.selectedIndex].getAttribute('data-name');
        if (userId && userName) {
            window.inviteUser(userId, userName);
            // Close modal
            document.dispatchEvent(new CustomEvent('close-modal', { detail: 'choose-user-video-call' }));
        } else {
            alert('Pilih user terlebih dahulu');
        }
    });
});
</script>
