<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeThisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('de_thi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ca_thi');
            $table->foreign('id_ca_thi')->references('id')->on('ca_thi')->onDelete('cascade');
            $table->bigInteger('ma_de_thi');
            $table->string('mo_ta')->nullable();
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
        Schema::dropIfExists('de_thi');
    }
}
