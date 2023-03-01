<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChitietcathisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chitietcathi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ca_thi');
            $table->foreign('id_ca_thi')->references('id')->on('ca_thi')->onDelete('cascade');

            $table->unsignedBigInteger('id_de_thi');
            $table->foreign('id_de_thi')->references('id')->on('excel_exam_bank')->onDelete('cascade');
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
        Schema::dropIfExists('chitietcathi');
    }
}
