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

    public function book(Request $request){
        $tripId = $request->input('trip_id');
        $seatId = $request->input('seat_id');

        // validate request body
        if(is_null($request->input('trip_id')) || is_null($request->input('seat_id'))){
            $result = [
                'message' => "seat_id or trip_id cannot be null"
            ];
            return response(json_encode($result), 400);
        }

        $trip = Trip::find($tripId);
        // validate trip exists
        if(is_null($trip)){
            $result = [
                'message' => "No trip with trip_id ".$tripId
            ];
            return response(json_encode($result), 404);
        }

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

        $tripStartStation = CrossOverStations::firstWhere([
                                    ['station_id', $startStation->id],
                                    ['trip_id', $tripId]    
                                ]);
        $endTripStation = CrossOverStations::firstWhere([
                                    ['station_id', '=', $endStation->id],
                                    ['trip_id', '=', $tripId],
                                ]);

        // check if user has chosen an available seat across all his/her trip
        if ($this->isSuitableTrip($tripStartStation, $endStation)){
            $availableSeats = $this->findAvailableSeats($startStation, $endStation, $tripStartStation);
            if (in_array($seatId, $availableSeats)){
                // mark seat as unavailable for all user trip (crossed-by stations)
                $tripStationsIds = $this->getUserTripStations($tripStartStation, $endTripStation)
                                        ->pluck('id');
                                        
                $this->bookSeat($seatId, $tripStationsIds);

                $result = [
                    'trip' => [
                        'id' => $trip->id,
                        'name' => $trip->name
                    ],
                    'seat_id' => $seatId
                ];
                return response(json_encode($result), 201);
            }
            else{
                $result = [
                    'message' => "Seat ".$seatId." is not available in this trip"
                ];
                return response(json_encode($result), 400);    
            }
        }
        else {
            $result = [
                'message' => "Trip '".$trip->name."' does not cross over '".$request->query('start')."' then '".$request->query('end')."'"
            ];
            return response(json_encode($result), 400);
        }
    }

    private function bookSeat($seatId, $tripStationsIds){
        AvailableSeats::where('seat_id', $seatId)
                        ->whereIn('tripStation_id', $tripStationsIds)
                        ->update(['available' => false]);
    }
}
