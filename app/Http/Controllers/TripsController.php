<?php

namespace App\Http\Controllers;

use App\Models\AvailableSeats;
use App\Models\CompleteTrip;
use App\Models\CrossOverStations;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripsController extends Controller
{
    public function add(Request $request){
        $order = 1;
        $inputStations = $request->input('stations');

        // fetch start & end stations
        $startStationName = $inputStations[0];
        $startStation = Station::firstWhere('name',$startStationName);
        $endStationName = $inputStations[count($inputStations)-1];
        $endStation = Station::firstWhere('name', $endStationName);
        
        // create new trip 
        $trip = new Trip();
        $trip->startStation()->associate($startStation);
        $trip->endStation()->associate($endStation);
        $trip->name = $startStation->name.'-'.$endStation->name;
        $trip->save();
        
        // add all stations to trip [start-end] with 12 available seats at each station
        
        foreach ($inputStations as $stationName) {
            $station = Station::firstWhere('name', $stationName);
            $trip->crossOverStations()->attach($station, ['order_in_trip' => $order++]);
        }
        foreach ($trip->crossOverStations as $tripStation) {
            $tripStationId = $tripStation->pivot->id;
            $this->addSeats($tripStationId);
        }

        // prepare created trip to return
        $createdTrip = new CompleteTrip($trip->name, $trip->stations());

        return response(json_encode($createdTrip), 201);
    }

    private function addSeats($tripStationId){
        for ($i=1; $i <= 12; $i++) {
            $seat = Seat::find($i);
            $tripStation = CrossOverStations::find($tripStationId);
            $tripStation->seats()->attach($seat, ['available' => true]);
        }
    }

