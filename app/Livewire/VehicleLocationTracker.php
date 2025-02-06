<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class VehicleLocationTracker extends Component
{
    public $vehicle;

    // Listening for the broadcasted event
    protected $listeners = [
        'vehicle-location.VehicleLocationUpdated' => 'updateLocation', // Listen for this event name
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

        // Assuming $data contains the necessary info
        // $data will have the 'vehicle' key, based on the structure you're broadcasting
        $this->vehicle = $data['vehicle'];  // This should update the vehicle data with what was broadcasted
    }

    /**
     * Render the Livewire view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.vehicle-location-tracker', [
            'vehicle' => $this->vehicle,  // Ensure vehicle data is passed to the view
        ]);
    }
}
