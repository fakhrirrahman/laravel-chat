<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallSignaling implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $signalData;
    public $fromUserId;
    
    public function __construct($roomId, $signalData, $fromUserId)
    {
        $this->roomId = $roomId;
        $this->signalData = $signalData;
        $this->fromUserId = $fromUserId;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('video-call.' . $this->roomId),
        ];
    }
}
