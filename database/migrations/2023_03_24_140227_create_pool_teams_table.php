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
        Schema::create('pool_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compatition_id')->references('id')->on('compatitions')->onDelete('cascade');
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            //Calculated by back end, depends on number of compatitors min 4 compatitors max 64
            $table->string('pool_type');//['P', 'R', 'G', 'S', 'B', 'p', 'r', 'g', 's', 'b'] P=pool, R=repesaz G=gold S=silver B=bronze
            $table->tinyInteger('pool');
            $table->integer('group');
            $table->boolean('status')->default(false);
            $table->foreignId('team_one')->nullable()->references('id')->on('teams')->nullOnDelete();
            $table->foreignId('team_two')->nullable()->references('id')->on('teams')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pool_teams');
    }
};
