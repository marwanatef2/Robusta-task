<?php

namespace Database\Seeders;

use App\Models\CrossOverStations;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    private $trips = [
        [
            "Cairo", "Fayum", "Beni Suef", "Minya", "Asyut", "Sohag", "Qena", "Luxor", "Aswan"
        ],
        [
            "Matrouh", "Alexandria", "Kafr ElSheikh", "Gharbia", "Monufia", "Beheira", "Giza", "Cairo"
        ],
        [
            'Matrouh', 'Alexandria', 'Cairo', 'Fayum', 'Beni Suef'
        ],
        [
            'Cairo', 'Suez', 'Ismailia', 'PortSaid'
        ],
        [
            'Damietta', 'Dakahlia', 'Sharqia', 'Qalyubia', 'Giza', 'Cairo', 'Alexandria', 'Matrouh'
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->trips as $trip) {
            $this->addTrip($trip);
        }
    }

    private function addTrip($stations){
        $startStationName = $stations[0];
        $startStation = Station::firstWhere('name',$startStationName);
        $endStationName = $stations[count($stations)-1];
        $endStation = Station::firstWhere('name', $endStationName);
        
        // create new trip 
        $trip = new Trip();
        $trip->startStation()->associate($startStation);
        $trip->endStation()->associate($endStation);
        $trip->name = $startStation->name.'-'.$endStation->name;
        $trip->save();

        $order = 1;
        foreach ($stations as $stationName) {
            $station = Station::firstWhere('name', $stationName);
            $trip->crossOverStations()->attach($station, ['order_in_trip' => $order++]);
        }
        foreach ($trip->crossOverStations as $tripStation) {
            $tripStationId = $tripStation->pivot->id;
            $this->addSeats($tripStationId);
        }
    }

    private function addSeats($tripStationId){
        for ($i=1; $i <= 12; $i++) {
            $seat = Seat::find($i);
            $tripStation = CrossOverStations::find($tripStationId);
            $tripStation->seats()->attach($seat, ['available' => true]);
        }
    }
}
