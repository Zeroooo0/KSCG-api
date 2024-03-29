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
        Schema::create('special_personals', function (Blueprint $table) {
            $table->id();
            $table->foreign('user_id')->nullable()->reference('id')->on('users')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->string('name');
            $table->string('last_name');
            $table->string('email');
            $table->tinyInteger('gender');
            $table->string('country');
            $table->string('phone_number');
            //0=Nema zvanje 1=Sudija 2=Trener
            $table->tinyInteger('role');
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
        Schema::dropIfExists('special_personals');
    }
};