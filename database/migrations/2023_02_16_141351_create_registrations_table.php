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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compatition_id')->references('id')->on('compatitions')->onDelete('cascade');
            $table->foreignId('club_id')->nullable()->references('id')->on('clubs')->nullOnDelete();
            $table->foreignId('compatitor_id')->nullable()->references('id')->on('compatitors')->nullOnDelete();
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->references('id')->on('teams')->uniqid();
            $table->boolean('team_or_single');
            $table->boolean('kata_or_kumite');
            $table->boolean('status')->default(true);
            $table->tinyInteger('position')->nullable();
            $table->tinyInteger('count')->default(0);
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
        Schema::dropIfExists('registrations');
    }
};