<?php

namespace App\Models;

class CompleteTrip{
    public $name;
    public $stations;
    
    public function __construct($tripName, $stations) {
        $this->name = $tripName;
        $this->stations = $stations;
    }
}