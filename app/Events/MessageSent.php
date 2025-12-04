<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $message;
    public $user;
    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->user = $message->user;
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'message' => $this->message,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat')
        ];
    }
}
