<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class VehicleLocationTracker extends Component
{
    public $vehicle;

    // Listen for the 'location' event broadcast on the 'gps' channel
    protected $listeners = [
        'location' => 'updateLocation',
    ];

    /**
     * Handle the event and update the vehicle location.
     *
     * @param array $data
     */
    public function updateLocation($data)
    {
        // Log received data for debugging
        Log::info('Received vehicle location update:', ['vehicle_data' => $data]);

        // The 'vehicle' data is passed in under the 'location' key from the broadcast
        if (isset($data['location'])) {
            $this->vehicle = $data['location']; // Update the vehicle data
        } else {
            Log::warning('No location data found in the event.');
        }
    }

    /**
     * Render the Livewire view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.vehicle-location-tracker', [
            'vehicle' => $this->vehicle,
        ]);
    }
}
