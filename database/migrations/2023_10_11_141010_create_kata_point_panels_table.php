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
        Schema::create('kata_point_panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->nullable()->references('id')->on('pools')->onDelete('cascade');
            $table->foreignId('registration_id')->nullable()->references('id')->on('registrations')->onDelete('cascade');
            $table->foreignId('pool_team_id')->nullable()->references('id')->on('pool_teams')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->references('id')->on('teams')->onDelete('cascade');
            $table->integer('judge');
            $table->decimal('points');
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
        Schema::dropIfExists('kata_point_panels');
    }
};
