<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->string('room_code');
            $table->string('type');
            $table->string('type_description');
            $table->string('characteristic');
            $table->string('characteristic_description');
            $table->integer('min_pax');
            $table->integer('max_pax');
            $table->integer('max_adults');
            $table->integer('max_children');
            $table->integer('min_adults');
            $table->string('description');
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
        Schema::dropIfExists('rooms');
    }
}
