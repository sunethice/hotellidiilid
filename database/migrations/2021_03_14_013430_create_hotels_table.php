<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->string('hotel_code');
            $table->string('name');
            $table->string('description');
            $table->double('longitude');
            $table->double('latitude');
            $table->string('country_code');
            $table->string('iso_code');
            $table->string('state_code');
            $table->string('destination_code');
            $table->string('zone_code');
            $table->string('category_code');
            $table->string('address_number');
            $table->string('address_street');
            $table->string('address_city');
            $table->string('address_postal_code');
            $table->boolean('active');
            $table->string('email');
            $table->json('phones');
            $table->json('boards');
            $table->json('rooms');
            $table->json('facilities');
            $table->string('client_id');
            $table->string('client_id');
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
        Schema::dropIfExists('hotels');
    }
}
