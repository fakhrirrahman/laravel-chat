@php
    $isOwnMessage = $message->user_id === auth()->id();
    $avatarColor = $isOwnMessage ? 'bg-green-500' : 'bg-indigo-500';
@endphp

<div class="flex items-start space-x-3 animate-fade-in">
    <div class="w-8 h-8 {{ $avatarColor }} rounded-full flex items-center justify-center flex-shrink-0">
        <span class="text-white text-sm font-medium">{{ strtoupper(substr($message->user->name, 0, 1)) }}</span>
    </div>
    <div class="flex-1">
        <div class="flex items-center space-x-2">
            <span class="font-semibold text-gray-900">{{ $message->user->name }}</span>
            <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
            @if($isOwnMessage)
                <span class="text-xs text-green-600 font-medium">You</span>
            @endif
        </div>
        <div class="mt-1 p-3 bg-white rounded-lg shadow-sm border border-gray-200">
            <p class="text-gray-800">{{ $message->message }}</p>
        </div>
    </div>
</div>
