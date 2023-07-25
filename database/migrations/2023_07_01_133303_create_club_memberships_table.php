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
        Schema::create('club_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->references('id')->on('clubs');
            //name can be yearlyMembership, beltsChange, midYearMembership
            $table->string('type');
            $table->boolean('is_paid')->nullable();
            $table->boolean('status')->nullable();
            $table->boolean('is_submited')->default(0);
            $table->decimal('membership_price')->nullable();
            $table->decimal('amount_to_pay')->nullable();
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
        Schema::dropIfExists('club_memberships');
    }
};
