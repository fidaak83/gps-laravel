<?php

namespace App\Livewire;

use Livewire\Component;

class Map extends Component
{
    public $customer_id; // Make customer_id public if needed elsewhere
    public $data; // Use a public property to hold the filtered vehicles

    public function mount()
    {
        // $this->customer_id = $customer_id; // Initialize customer_id
        $this->data = $this->getvehicles(); // Fetch and store the filtered vehicles
        // var_dump($this->data);
        // dd();
    }

    private function getvehicles()
    {
        $Vehicles = [
            [
                "imei" => "864636067510004",
                "timestamp" => 1738931082,
                "longitude" => 46.8023849,
                "latitude" => 24.6289866,
                "speed" => 10,
                "ignition" => 1,
                "belong" => 100
            ],
            [
                "imei" => "864636067519823",
                "timestamp" => 1738932082,
                "longitude" => 46.8049283,
                "latitude" => 24.6297471,
                "speed" => 15,
                "ignition" => 1,
                "belong" => 100
            ],
            [
                "imei" => "864636067519849",
                "timestamp" => 1738933082,
                "longitude" => 46.8056953,
                "latitude" => 24.6301319,
                "speed" => 20,
                "ignition" => 1,
                "belong" => 101
            ]
        ];

        return $Vehicles;

        // // Filter vehicles based on the customer_id
        // return array_filter($Vehicles, function ($vehicle) use ($customer_id) {
        //     return $vehicle['belong'] == $customer_id;
        // });
    }

    public function render()
    {
        // Debug the data being passed to the view
        // dd($this->data);

        return view('livewire.map', [
            'data' => $this->data, // Pass the filtered vehicles to the view
        ]);
    }
}