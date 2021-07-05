<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AvailableSeats;
use App\Models\CompleteTrip;
use App\Models\CrossOverStations;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;

class SeatsController extends Controller
{
    public function showAvailableSeats(Request $request)
    {
        $result = [];
        $statusCode = 200;

        // validate query strings
        if(is_null($request->query('start')) || is_null($request->query('end'))){
            $result = [
                'message' => "trip start or end cannot be null"
            ];
            return response(json_encode($result), 400);
        }

        $startStation = Station::firstWhere('name', $request->query('start'));
        // validate start station
        if (is_null($startStation)){
            $result = [
                'message' => "No station match start station '".$request->query('start')."'"
            ];
            return response(json_encode($result), 404);
        }

        $endStation = Station::firstWhere('name', $request->query('end'));
        // validate end station
        if (is_null($endStation)){
            $result = [
                'message' => "No station match end station '".$request->query('end')."'"
            ];
            return response(json_encode($result), 404);
        }        

        // find possible trips passing by start station
        $possibleTrips = CrossOverStations::select('trip_id', 'order_in_trip')
                                ->where('station_id', '=', $startStation->id)
                                ->get();
        if(empty($possibleTrips->all())){
            $result = [
                'message' => "No trip passes by the start station '".$request->query('start')."'"
            ];
            return response(json_encode($result), 404);
        }
        
        $suitableTripsCount = 0;
        foreach ($possibleTrips as $tripStation) {
            if ($this->isSuitableTrip($tripStation, $endStation)){
                $suitableTripsCount++;

                // find avaialable seats (if any) in this suitable trip
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

        if($suitableTripsCount == 0){
            $result = [
                'message' => "No avaialable trips pass by the end station '".$request->query('start')."'"
            ];
            $statusCode = 404;
        }
        elseif (empty($result)){
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
}
