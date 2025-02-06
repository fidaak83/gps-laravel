<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class VehicleLocationTracker extends Component
{
    public $vehicle;

    // The event listener setup directly in the class
    protected $listeners = [
        'VehicleLocationUpdated' => 'updateLocation',
    ];

    /**
     * Handle the vehicle location update event.
     *
     * @param array $data
     */
    public function updateLocation($data)
    {
        // Log received data for debugging
        Log::info('Received vehicle location update:', ['vehicle_data' => $data]);
        dd($data);
        // Update vehicle data (assuming $data contains a 'vehicle' key)
        $this->vehicle = $data['vehicle'];
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
