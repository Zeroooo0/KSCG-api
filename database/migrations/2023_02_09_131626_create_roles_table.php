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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('special_personals_id');
            $table->string('title');
            //Special personal Rolle Trener / Sudija
            $table->tinyInteger('role');
            //Prestanak funkcije trenera
            $table->timestamp('active_untill')->nullable();
            $table->foreign('special_personals_id')
                ->references('id')
                ->on('special_personals');
            $table->morphs('roleable');
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
        Schema::dropIfExists('roles');
    }
};
