<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $attributes = [
        'name' => "Trip test",
    ];

    // protected $hidden = ['pivot'];

    public function startStation(){
        return $this->belongsTo(Station::class, 'startStation_id');
    }

    public function endStation(){
        return $this->belongsTo(Station::class, 'endStation_id');
    }

    public function crossOverStations(){
        // return $this->belongsToMany(Station::class)->using(TripsStations::class)->withTimestamps();
        return $this->belongsToMany(Station::class, 'cross_over_stations')->withPivot(['order_in_trip', 'id'])->withTimestamps();
    }

    public function stations(){
        $stations = $this->crossOverStations()->orderByPivot('order_in_trip')->get(['name'])->all();
        // array_unshift($stations, $this->startStation()->first(['name']));
        // array_push($stations, $this->endStation()->first(['name']));
        return $stations;
    }
}
