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

    public function showAvailableSeats(Request $request)
    {
        $result = [];
        $statusCode = 200;

        $startStation = Station::firstWhere('name', $request->query('start'));
        $endStation = Station::firstWhere('name', $request->query('end'));

        $possibleTrips = CrossOverStations::select('trip_id', 'order_in_trip')
                                ->where('station_id', '=', $startStation->id)
                                ->get();

        foreach ($possibleTrips as $tripStation) {
            if ($this->isSuitableTrip($tripStation, $endStation)){
                $availableSeats = $this->findAvailableSeats($startStation, $endStation, $tripStation);
                if(!empty($availableSeats)){
                    $trip = Trip::find($tripStation->trip_id);
                    $resultPerTrip = [
                        'trip' => [
                            'id' => $trip->id,
                            'name' => $trip->name
                        ],
                        'number_of_avaialable_seats' => count($availableSeats),
                        'available_seats' => $availableSeats
                    ];
                    array_push($result, $resultPerTrip);
                }
            }
        }

        if (empty($result)){
            $result = [
                'message' => "No avaialable seats currently"
            ];
            $statusCode = 404;
        }
        return response(json_encode($result), $statusCode);
    }

    private function isSuitableTrip($tripStation, $endStation)
    {
        $tripIds = CrossOverStations::select('trip_id')
                                    ->where([
                                        ['station_id', '=', $endStation->id],
                                        ['trip_id', '=', $tripStation->trip_id],
                                        ['order_in_trip', '>', $tripStation->order_in_trip]
                                    ])
                                    ->get();
        if (empty($tripIds->all()))
            return false;
        else return true;
    }

    private function findAvailableSeats($startStation, $endStation, $tripStation){
        // find available seats at first station in user's trip
        $availableSeats = CrossOverStations::where([
                                ['station_id', $startStation->id],
                                ['trip_id', $tripStation->trip_id],
                            ])->first()
                            ->availableSeats()
                            ->pluck('seat_id');

        // last station in user's trip
        $endTripStation = CrossOverStations::firstWhere([
                                ['station_id', '=', $endStation->id],
                                ['trip_id', '=', $tripStation->trip_id],
                            ]);

        // get user's trip stations
        $cross_over_stations = $this->getUserTripStations($tripStation, $endTripStation);;

        // find common available seats through all users stations
        foreach ($cross_over_stations as $station) {
            $newAvailableSeats = $station->availableSeats()
                                        ->pluck('seat_id');
            $availableSeats = $availableSeats->intersect($newAvailableSeats);
        }
        
        return array_values($availableSeats->all());
    }

    private function getUserTripStations($startTripStation, $endTripStation){
        return CrossOverStations::where([
            ['trip_id', '=', $startTripStation->trip_id],
            ['order_in_trip', '>=' , $startTripStation->order_in_trip],
            ['order_in_trip', '<=' , $endTripStation->order_in_trip]
        ])->get();
    }

