<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class VehicleLocationUpdated implements ShouldBroadcastNow
{
    public $vehicle;

    public function __construct($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    public function broadcastOn()
    {
        return new Channel('vehicle-location');
    }

}
