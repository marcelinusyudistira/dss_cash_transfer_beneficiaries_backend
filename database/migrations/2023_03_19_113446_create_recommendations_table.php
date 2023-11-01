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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id('id_rekomendasi');
            $table->foreignId('id_alternatif');
            $table->string('kode')->unique();
            $table->string('nama_alternatif');
            $table->string('nik_alternatif');
            $table->string('alamat_alternatif');
            $table->string('pekerjaan_alternatif');
            $table->float('total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recommendations');
    }
};
