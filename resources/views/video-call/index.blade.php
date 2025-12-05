<x-app-layout>
    <div class="py-6">
        <h1 class="text-2xl font-bold">Video Call</h1>

        <script>
            window.userId = {{ auth()->id() }};
            window.userName = "{{ auth()->user()->name }}";
        </script>

        <div class="mt-4">
            <h2 class="text-lg font-semibold">Select User to Call</h2>
            <div id="user-list" class="mb-4">
                @foreach (\App\Models\User::where('id', '!=', auth()->id())->get() as $user)
                    <button class="user-button px-4 py-2 bg-gray-700 rounded mr-2 mb-2" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                        {{ $user->name }}
                    </button>
                @endforeach
            </div>

            <button id="start-call" class="px-4 py-2 bg-blue-600 text-white rounded">Start Call</button>

            <input id="room-id-input" class="border px-2 py-1" placeholder="Room ID" />
            <button id="join-call" class="px-4 py-2 bg-green-600 text-white rounded">Join Call</button>

            <button id="end-call" class="px-4 py-2 bg-red-600 text-white rounded hidden">End</button>

            <div id="invite-notification" class="hidden mt-4 p-4 bg-yellow-100 border">
                <p id="invite-message"></p>
                <button id="accept-invite" class="px-4 py-2 bg-green-600 text-white rounded mr-2">Accept</button>
                <button id="decline-invite" class="px-4 py-2 bg-red-600 text-white rounded">Decline</button>
            </div>

            <div id="video-call-container" class="hidden mt-4">
                <video id="local-video" autoplay playsinline muted></video>
                <video id="remote-video" autoplay playsinline></video>
            </div>
        </div>
    </div>

    @vite('resources/js/video-call.js')
</x-app-layout>
