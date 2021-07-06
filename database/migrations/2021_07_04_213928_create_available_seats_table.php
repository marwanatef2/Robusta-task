<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailableSeatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('available_seats', function (Blueprint $table) {
            $table->id();
            $table->boolean('available');
            $table->foreignId('seat_id')->constrained('seats');
            $table->foreignId('tripStation_id')->constrained('cross_over_stations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('available_seats');
    }
}
