<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-channel.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});