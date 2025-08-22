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
        Schema::create('t_konsultasi', function (Blueprint $table) {
            $table->id('ID');
            $table->string('N_NIK', 30);
            $table->enum('JENIS', ['ADVOKASI', 'ASPIRASI']);
            $table->string('KATEGORI_ADVOKASI', 100)->nullable();
            $table->enum('TUJUAN', ['DPP', 'DPW', 'DPD', 'GENERAL']);
            $table->string('TUJUAN_SPESIFIK', 100)->nullable();
            $table->string('JUDUL', 200);
            $table->text('DESKRIPSI');
            $table->enum('STATUS', ['OPEN', 'IN_PROGRESS', 'CLOSED', 'RESOLVED'])->default('OPEN');
            $table->string('CREATED_BY', 30);
            $table->string('UPDATED_BY', 30)->nullable();
            $table->string('CLOSED_BY', 30)->nullable();
            $table->dateTime('CLOSED_AT')->nullable();
            $table->timestamps();

            $table->index('STATUS', 'idx_konsultasi_status');
            $table->index('TUJUAN', 'idx_konsultasi_tujuan');
            $table->index('JENIS', 'idx_konsultasi_jenis');
            $table->index(['N_NIK', 'STATUS'], 'idx_konsultasi_nik_status');
            $table->index('created_at', 'idx_konsultasi_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_konsultasi');
    }
};
