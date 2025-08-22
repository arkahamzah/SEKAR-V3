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
        Schema::create('t_ex_anggota', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('N_NIK', 60)->nullable();
            $table->string('V_NAMA_KARYAWAN', 100)->nullable();
            $table->string('V_SHORT_POSISI', 150)->nullable();
            $table->string('V_SHORT_DIVISI', 150)->nullable();
            $table->dateTime('TGL_MASUK')->nullable();
            $table->dateTime('TGL_KELUAR')->nullable();
            $table->string('DPP', 50)->nullable();
            $table->string('DPW', 50)->nullable();
            $table->string('DPD', 50)->nullable();
            $table->string('V_KOTA_GEDUNG', 100)->nullable();
            $table->string('CREATED_BY', 20)->nullable();
            $table->string('ALASAN_KELUAR', 255)->nullable();
            $table->decimal('IURAN_WAJIB_TERAKHIR', 15, 2)->nullable();
            $table->decimal('IURAN_SUKARELA_TERAKHIR', 15, 2)->nullable();
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
        Schema::dropIfExists('t_ex_anggota');
    }
};