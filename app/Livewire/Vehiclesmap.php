<?php

namespace App\Livewire;

use Livewire\Component;

class Vehiclesmap extends Component
{
    public $customerid;
    public function mount($id)
    {
        $this->customerid = $id;
    }
    public function render()
    {

        return view('livewire.vehiclesmap');
    }
}
