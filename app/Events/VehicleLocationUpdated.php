<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VehicleLocationUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $vehicle;

    public function __construct($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    public function broadcastOn()
{
    Log::info('Broadcasting event on channel: vehicle-location', ['vehicle' => $this->vehicle]);
    return new Channel('vehicle-location');
}

public function broadcastAs()
{
    Log::info('Event name: VehicleLocationUpdated');
    return 'VehicleLocationUpdated';
}

}
