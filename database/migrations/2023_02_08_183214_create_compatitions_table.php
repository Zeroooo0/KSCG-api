<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compatitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->string('city');
            $table->string('address');
            $table->dateTime('start_time_date');
            $table->dateTime('registration_deadline');
            $table->decimal('price_single')->nullable();
            $table->decimal('price_team')->nullable();
            $table->boolean('status')->default(0);
            $table->tinyInteger('tatami_no');
            $table->boolean('registration_status')->default(1);
            $table->string('host_name');
            $table->integer('points_multiplier')->nullable();
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
        Schema::dropIfExists('compatitions');
    }
};
