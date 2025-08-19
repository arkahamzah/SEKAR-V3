<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_sertifikat_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pejabat', 100);
            $table->string('jabatan', 100);
            $table->string('signature_file')->comment('Nama file gambar tanda tangan');
            $table->date('start_date')->comment('Tanggal mulai berlaku');
            $table->date('end_date')->comment('Tanggal akhir berlaku');
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_sertifikat_signatures');
    }
};