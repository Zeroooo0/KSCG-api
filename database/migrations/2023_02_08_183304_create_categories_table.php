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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            //0=Kata 1=Kumite
            $table->tinyInteger('kata_or_kumite');
            $table->string('category_name')->nullable();
            //Male=1 Femail=2 Bouth=3
            $table->tinyInteger('gender');
            $table->date('date_from');
            $table->date('date_to');
            //years in number
            $table->integer('years_from');
            $table->integer('years_to');
            $table->boolean('is_official')->default('1');
            // 0=Solo 1=Team 
            $table->boolean('solo_or_team');
            $table->integer('match_lenght');
            $table->boolean('status');
            $table->boolean('repesaz')->default(0);
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
        Schema::dropIfExists('categories');
    }
};
