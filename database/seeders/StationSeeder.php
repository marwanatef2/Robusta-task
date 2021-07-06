<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Station::create([
            'name' => 'Matrouh',
        ]);
        Station::create([
            'name' => 'Alexandria',
        ]);
        Station::create([
            'name' => 'Kafr ElSheikh',
        ]);
        Station::create([
            'name' => 'Gharbia',
        ]);
        Station::create([
            'name' => 'Monufia',
        ]);
        Station::create([
            'name' => 'Beheira',
        ]);
        Station::create([
            'name' => 'Qalyubia',
        ]);
        Station::create([
            'name' => 'Sharqia',
        ]);
        Station::create([
            'name' => 'Dakahlia',
        ]);
        Station::create([
            'name' => 'Damietta',
        ]);
        Station::create([
            'name' => 'PortSaid',
        ]);
        Station::create([
            'name' => 'Ismailia',
        ]);
        Station::create([
            'name' => 'Suez',
        ]);
        Station::create([
            'name' => 'North Sinai',
        ]);
        Station::create([
            'name' => 'South Sinai',
        ]);
        Station::create([
            'name' => 'Giza',
        ]);
        Station::create([
            'name' => 'Cairo',
        ]);
        Station::create([
            'name' => 'Fayum',
        ]);
        Station::create([
            'name' => 'Beni Suef',
        ]);
        Station::create([
            'name' => 'Minya',
        ]);
        Station::create([
            'name' => 'Asyut',
        ]);
        Station::create([
            'name' => 'Sohag',
        ]);
        Station::create([
            'name' => 'Qena',
        ]);
        Station::create([
            'name' => 'Luxor',
        ]);
        Station::create([
            'name' => 'Aswan',
        ]);
    }
}
