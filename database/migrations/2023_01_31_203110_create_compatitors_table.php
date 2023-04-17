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

            $table->string('kscg_compatitor_id');
            $table->string('name');
            $table->string('last_name');
            //Male=1 Femail=2 
            $table->tinyInteger('gender');
            $table->string('jmbg');
            $table->string('country');
            $table->date('date_of_birth');
            $table->decimal('weight', 6, 2);
            $table->boolean('status')->default(false);
            $table->foreignId('club_id')
                ->nullable()
                ->references('id')
                ->on('clubs')
                ->nullOnDelete();
            $table->foreignId('belt_id')
                ->nullable()
                ->references('id')
                ->on('belts')
                ->nullOnDelete();
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
