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
        Schema::create('t_banpers_history', function (Blueprint $table) {
            $table->id('ID');
            $table->string('N_NIK', 30);
            $table->string('NOMINAL_LAMA', 20)->nullable();
            $table->string('NOMINAL_BARU', 20);
            $table->string('TAHUN', 4);
            $table->enum('STATUS_PROSES', ['PENDING', 'PROCESSED', 'IMPLEMENTED'])->default('PENDING');
            $table->dateTime('TGL_PERUBAHAN');
            $table->dateTime('TGL_PROSES')->nullable();
            $table->dateTime('TGL_IMPLEMENTASI')->nullable();
            $table->string('KETERANGAN', 255)->nullable();
            $table->string('CREATED_BY', 30);
            $table->dateTime('CREATED_AT');

            $table->index(['N_NIK', 'TAHUN'], 't_banpers_history_n_nik_tahun_index');
            $table->index('STATUS_PROSES', 't_banpers_history_status_proses_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_banpers_history');
    }
};