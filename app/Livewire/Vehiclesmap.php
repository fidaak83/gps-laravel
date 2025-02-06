<?php

namespace App\Livewire;

use Livewire\Component;

class Vehiclesmap extends Component
{
    public function render()
    {
        $user = ["name" => "khan"];
        return view('livewire.vehiclesmap', compact('user'));
    }
}
