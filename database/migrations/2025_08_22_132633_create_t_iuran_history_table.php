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
        Schema::create('t_iuran_history', function (Blueprint $table) {
            $table->id('ID');
            $table->string('N_NIK', 30);
            $table->string('JENIS', 20);
            $table->string('NOMINAL_LAMA', 20)->nullable();
            $table->string('NOMINAL_BARU', 20);
            $table->string('STATUS_PROSES', 20)->default('PENDING');
            $table->dateTime('TGL_PERUBAHAN');
            $table->dateTime('TGL_PROSES')->nullable();
            $table->dateTime('TGL_IMPLEMENTASI')->nullable();
            $table->string('KETERANGAN', 255)->nullable();
            $table->string('CREATED_BY', 30);
            $table->dateTime('CREATED_AT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_iuran_history');
    }
};