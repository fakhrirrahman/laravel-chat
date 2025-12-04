<form id="chat-form" class="flex items-end space-x-3">
    <div class="flex-1">
        <x-text-input id="message" type="text" class="w-full" placeholder="Type your message..." wire:ignore />
    </div>
    <x-primary-button type="submit" class="flex-shrink-0">Send</x-primary-button>
</form>
