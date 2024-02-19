<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotelimagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotelimages', function (Blueprint $table) {
            $table->string('type_code');
            $table->string('type_description');
            $table->string('path');
            $table->integer('order');
            $table->integer('visual_order');
            $table->integer('hotel_code');
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
        Schema::dropIfExists('hotelimages');
    }
}
