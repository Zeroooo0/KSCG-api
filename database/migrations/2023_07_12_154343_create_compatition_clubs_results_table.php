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
        Schema::create('compatition_clubs_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compatition_id')->references('id')->on('compatitions')->onDelete('cascade');
            $table->foreignId('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->integer('gold_medals')->nullable();
            $table->integer('silver_medals')->nullable();
            $table->integer('bronze_medals')->nullable();
            $table->integer('points')->nullable();
            $table->integer('no_compatitors')->nullable();
            $table->integer('no_teams')->nullable();
            $table->integer('no_singles')->nullable();
            $table->integer('total_price')->nullable();
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
        Schema::dropIfExists('compatition_clubs_results');
    }
};
