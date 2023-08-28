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
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('deadline');
            $table->date('start');
            $table->string('country');
            $table->string('city');
            $table->string('address');
            $table->string('host');
            // licenceSeminar educationSeminar
            $table->string('seminar_type');
            $table->boolean('has_judge');
            $table->boolean('has_compatitor');
            $table->boolean('has_coach');
            $table->decimal('price_judge');
            $table->decimal('price_compatitor');
            $table->decimal('price_coach');
            $table->boolean('is_hidden')->default('0');
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
        Schema::dropIfExists('seminars');
    }
};
