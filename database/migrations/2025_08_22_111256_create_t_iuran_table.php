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
        Schema::create('t_iuran', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('N_NIK', 30)->nullable()->index();
            $table->string('IURAN_WAJIB', 20)->nullable();
            $table->string('IURAN_SUKARELA', 20)->nullable();
            $table->string('TAHUN', 4)->default('2025');
            $table->string('BULAN_TERAKHIR_BAYAR', 2)->nullable();
            $table->enum('STATUS_BAYAR', ['AKTIF', 'NUNGGAK', 'TIDAK_AKTIF'])->default('AKTIF')->index();
            $table->string('CREATED_BY', 30)->nullable();
            $table->string('UPDATE_BY', 30)->nullable();
            $table->timestamps(); // Ini akan membuat CREATED_AT dan UPDATED_AT
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_iuran');
    }
};