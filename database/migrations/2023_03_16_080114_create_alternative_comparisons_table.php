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
        Schema::create('alternative_comparisons', function (Blueprint $table) {
            $table->id('id_perbandinganAlternatif');
            $table->foreignId('id_kriteria');
            $table->foreignId('id_alternatif');
            $table->unsignedInteger('id_alternatif2');
            $table->float('nilai');
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
        Schema::dropIfExists('alternative_comparisons');
    }
};
