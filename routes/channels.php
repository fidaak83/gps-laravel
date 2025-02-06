<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('gpsdata.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
