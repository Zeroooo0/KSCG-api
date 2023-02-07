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
        Schema::create('rolleable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('special_personals_id');
            $table->string('title');
            $table->string('rolle');
            //Prestanak funkcije trenera
            $table->timestamp('active_untill')->nullable();
            $table->foreign('special_personals_id')
                ->references('id')
                ->on('special_personals');
            $table->morphs('rolleable');
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
        Schema::dropIfExists('rolleable');
    }
};
