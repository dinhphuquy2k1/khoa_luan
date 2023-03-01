<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaThisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ca_thi', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ma_ca_thi');
            $table->unsignedBigInteger('id_ki_thi');
            $table->foreign('id_ki_thi')->references('id')->on('ki_thi')->onDelete('cascade');
            $table->string('mo_ta')->nullable();
            $table->datetime('thoi_gian');
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
        Schema::dropIfExists('ca_thi');
    }
}
