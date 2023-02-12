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
        Schema::create('compatitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->string('kscg_compatitor_id');
            $table->string('name');
            $table->string('last_name');
            //Male=1 Femail=2 
            $table->tinyInteger('gender');
            $table->string('jmbg');
            $table->unsignedBigInteger('belt_id');
            $table->date('date_of_birth');
            $table->decimal('weight', 4, 2);
            $table->boolean('status')->default(false);
            $table->foreign('club_id')
                ->nullable()
                ->references('id')
                ->on('clubs');
            $table->foreign('belt_id')
                ->references('id')
                ->on('belts');
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
        Schema::dropIfExists('compatitors');
    }
};
