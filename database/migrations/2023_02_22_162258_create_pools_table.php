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
        Schema::create('pools', function (Blueprint $table) {
            $table->id();
            //morph connection for this 2 id
            $table->foreignId('compatition_id')->references('id')->on('compatitions')->onDelete('cascade');
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            //Calculated by back end, depends on number of compatitors min 4 compatitors max 64
            $table->string('pool_type');//['FM', 'SF', 'G', 'RG', 'G3', 'G4', 'G5', 'g', 's', 'b']
            $table->tinyInteger('pool');
            $table->integer('group');
            //0=SCHEDULED 1=active 2=finished
            $table->tinyInteger('state')->default(0);
            $table->time('start_time');
            $table->bigInteger('winner_id')->nullable()->references('id')->on('registrations')->nullOnDelete();
            $table->bigInteger('looser_id')->nullable()->references('id')->on('registrations')->nullOnDelete();
            //morph registration or team
            $table->foreignId('registration_one')->nullable()->references('id')->on('registrations')->nullOnDelete();
            $table->foreignId('registration_two')->nullable()->references('id')->on('registrations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pools');
    }
};
