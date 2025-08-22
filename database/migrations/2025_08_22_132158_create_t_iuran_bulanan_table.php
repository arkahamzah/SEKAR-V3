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
        Schema::create('t_iuran_bulanan', function (Blueprint $table) {
            $table->id('ID');
            $table->string('N_NIK', 30);
            $table->string('TAHUN', 4);
            $table->string('BULAN', 2);
            $table->string('IURAN_WAJIB', 20)->default('0');
            $table->string('IURAN_SUKARELA', 20)->default('0');
            $table->string('TOTAL_IURAN', 20)->default('0');
            $table->enum('STATUS', ['LUNAS', 'BELUM_BAYAR', 'TERLAMBAT'])->default('BELUM_BAYAR');
            $table->dateTime('TGL_BAYAR')->nullable();
            $table->string('CREATED_BY', 30);
            $table->string('UPDATED_BY', 30)->nullable();
            $table->timestamps();

            $table->unique(['N_NIK', 'TAHUN', 'BULAN'], 't_iuran_bulanan_n_nik_tahun_bulan_unique');
            $table->index(['TAHUN', 'BULAN'], 't_iuran_bulanan_tahun_bulan_index');
            $table->index('STATUS', 't_iuran_bulanan_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_iuran_bulanan');
    }
};