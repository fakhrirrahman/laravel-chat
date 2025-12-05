<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('video-call.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $id === (int) $user->id;
});