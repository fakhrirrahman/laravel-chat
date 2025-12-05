<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Realtime Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div data-user-name="{{ auth()->user()->name }}" data-user-id="{{ auth()->user()->id }}">
                    <x-chat-box :messages="$messages" :users="$users" />
                </div>
            </div>
        </div>
    </div>

    <script>
        window.userId = {{ auth()->id() }};
        window.userName = "{{ auth()->user()->name }}";
    </script>

    @vite('resources/js/chat-init.js')
    @vite('resources/js/video-call.js')

</x-app-layout>
