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
        Schema::create('t_konsultasi_komentar', function (Blueprint $table) {
            $table->id('ID');
            $table->unsignedBigInteger('ID_KONSULTASI');
            $table->string('N_NIK', 30);
            $table->text('KOMENTAR');
            $table->enum('PENGIRIM_ROLE', ['USER', 'ADMIN'])->default('USER');
            $table->string('CREATED_BY', 30);
            $table->string('UPDATED_BY', 30)->nullable()->comment('NIK yang mengupdate komentar');
            $table->timestamps();

            $table->index('ID_KONSULTASI', 'idx_komentar_konsultasi');
            $table->index('PENGIRIM_ROLE', 'idx_komentar_role');
            $table->index('N_NIK', 'idx_komentar_nik');
            $table->index('created_at', 'idx_komentar_created');

            $table->foreign('ID_KONSULTASI')->references('ID')->on('t_konsultasi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_konsultasi_komentar');
    }
};