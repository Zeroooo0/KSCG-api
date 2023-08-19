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
        Schema::create('special_personnel_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->references('id')->on('special_personals')->onDelete('cascade');
            $table->string('name_of_parent');
            $table->string('jmbg');
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('address');
            $table->string('landline_phone')->nullable();
            $table->string('belt');
            $table->date('belt_acquired');
            $table->string('certificate');
            $table->string('certificate_id');
            $table->date('certificate_acquired');
            $table->string('certificate_issuer');
            $table->boolean('policy_confirmation');
            //required for coach
            $table->string('for_categories')->nullable();
            //required for judge
            $table->string('judge_title')->nullable();
            $table->date('judge_title_acquired')->nullable();
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
        Schema::dropIfExists('special_personnel_forms');
    }
};
