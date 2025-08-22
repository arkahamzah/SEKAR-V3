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
        Schema::create('t_sertifikat_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pejabat', 100);
            $table->string('jabatan', 100);
            $table->string('signature_file', 255)->comment('Nama file gambar tanda tangan');
            $table->date('start_date')->comment('Tanggal mulai berlaku');
            $table->date('end_date')->comment('Tanggal akhir berlaku');
            $table->string('created_by', 255)->nullable();
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
        Schema::dropIfExists('t_sertifikat_signatures');
    }
};
