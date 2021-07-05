<?php

use App\Http\Controllers\TripsController;
use App\Models\CrossOverStations;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/users', function ()
{
    // $station1 = Station::create(['name' => "Cairo"]);
    // $station2 = Station::create(['name' => "Alex"]);
    // $station3 = Station::create(['name' => "Matroh"]);

    // $station1 = Station::find(1);
    // $station3 = Station::find(3);

    // $trip = new Trip();
    // $trip->startStation()->associate($station1);
    // $trip->endStation()->associate($station3);
    // $trip->name = $trip->startStation->name.'-'.$trip->endStation->name;
    // $trip->save();

    // $station1 = Station::create(['name' => "Aswan"]);
    // $station2 = Station::create(['name' => "Luxor"]);
    // $station = Station::find(2);

    // $trip = Trip::find(1);
    // $trip->crossOverStations()->attach($station, ['order_in_trip' => 1]);

    // return $trip->stations();

    return CrossOverStations::all();

    // return User::all(['name', 'email']);
    // return DB::select('select name, email from users');
});

Route::post('/trips', [TripsController::class, 'add']);

Route::get('/trips', [TripsController::class, 'showAvailableSeats']);

Route::post('/trips/seats', [TripsController::class, 'book']);

Route::get('/test', function (Request $request){
    // return Trip::find(2)->crossOverStations()->get(['name']);
    // return Trip::find(2)->stations();
    // CrossOverStations::truncate();
    // Trip::truncate();

    // $station1 = Station::create(['name' => "Cairo"]);
    // $station2 = Station::create(['name' => "Alex"]);
    // $station3 = Station::create(['name' => "Matroh"]);
    //     $station1 = Station::create(['name' => "Aswan"]);
    // $station2 = Station::create(['name' => "Luxor"]);

    for ($i=0; $i < 12; $i++) { 
        $seat = new Seat();
        $seat->save();
    }
    
    return "good";
});