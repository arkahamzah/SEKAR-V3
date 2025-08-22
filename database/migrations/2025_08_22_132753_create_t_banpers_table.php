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
        Schema::create('t_banpers', function (Blueprint $table) {
            $table->id('ID');
            $table->string('N_NIK', 30);
            $table->string('NOMINAL_BANPERS', 20)->default('0');
            $table->string('TAHUN', 4);
            $table->enum('STATUS', ['AKTIF', 'TIDAK_AKTIF'])->default('AKTIF');
            $table->string('CREATED_BY', 30);
            $table->string('UPDATED_BY', 30)->nullable();
            $table->timestamps();

            $table->index(['N_NIK', 'TAHUN'], 't_banpers_n_nik_tahun_index');
            $table->index('STATUS', 't_banpers_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_banpers');
    }
};