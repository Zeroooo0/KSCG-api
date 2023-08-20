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
        Schema::create('time_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compatition_id')->references('id')->on('compatitions')->onDelete('cascade');
            $table->foreignId('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->tinyInteger('tatami_no');
            $table->integer('order_no');
            $table->time('eto_start');
            $table->time('eto_finish');
            //0=null 1=started 2=finished
            $table->tinyInteger('status')->default(0);
            $table->time('started_time')->nullable();
            $table->time('finish_time')->nullable();
            $table->integer('pairs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_tables');
    }
};
