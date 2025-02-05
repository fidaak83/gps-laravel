<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class VehicleLocationTracker extends Component
{
    public $vehicle;

    protected $listeners = ['echo:vehicle-location,VehicleLocationUpdated' => 'updateLocation'];

    public function updateLocation($data)
    {
        dd($data); 
        $this->vehicle = $data;
    }

    public function render()
    {
        return view('livewire.vehicle-location-tracker');
    }
}