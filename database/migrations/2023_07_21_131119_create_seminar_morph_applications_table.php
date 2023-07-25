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
        Schema::create('seminar_morph_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_id')->references('id')->on('seminars')->onDelete('cascade');
            $table->foreignId('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->morphs('applicable');
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
        Schema::dropIfExists('seminar_morph_applications');
    }
};
