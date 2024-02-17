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
        Schema::create('competitor_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_membership_id')->references('id')->on('club_memberships')->onDelete('cascade');
            $table->foreignId('belt_id')->nullable()->references('id')->on('belts');
            $table->foreignId('competitor_id')->references('id')->on('compatitors')->onDelete('cascade');
            $table->decimal('membership_price')->nullable();
            $table->boolean('first_membership')->nullable();
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
        Schema::dropIfExists('competitor_memberships');
    }
};
