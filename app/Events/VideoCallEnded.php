<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $fromUserId;
    public $fromUserName;
    public $toUserId;

    /**
     * Create a new event instance.
     */
    public function __construct($roomId, $fromUserId, $fromUserName, $toUserId)
    {
        $this->roomId = $roomId;
        $this->fromUserId = $fromUserId;
        $this->fromUserName = $fromUserName;
        $this->toUserId = $toUserId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->toUserId),
        ];
    }
}
