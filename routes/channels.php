<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| 
*/

// event for connection requests
Broadcast::channel('App.Models.User.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});

