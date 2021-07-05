<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CrossOverStations extends Pivot
{
    use HasFactory;

    protected $table = 'cross_over_stations';

    public $incrementing = true;

    public function seats(){
        // return $this->belongsToMany(Seat::class, 'available_seats', 'tripStation_id', 'seat_id')->using(AvailableSeats::class);
        return $this->belongsToMany(Seat::class, 'available_seats', 'tripStation_id', 'seat_id')->withPivot('available');
    }

    public function availableSeats(){
        return $this->belongsToMany(Seat::class, 'available_seats', 'tripStation_id', 'seat_id')->withPivot('id')->wherePivot('available', true);
    }
}
