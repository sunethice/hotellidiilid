<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->string('reference');
            $table->string('client_reference');
            $table->string('status');
            $table->boolean('cancellation_policy');
            $table->boolean('modification_policy');
            $table->string("used_hb_key");
            $table->string('holder_name');
            $table->string('holder_surname');
            $table->string('remark');
            $table->double('total_net');
            $table->double('net_with_markup');
            $table->double('pending_amount');
            $table->string('currency');
            $table->string('hotel');
            $table->string('check_in');
            $table->string('check_out');
            $table->string('contact_details');
            $table->string('client_id');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
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
        Schema::dropIfExists('bookings');
    }
}
