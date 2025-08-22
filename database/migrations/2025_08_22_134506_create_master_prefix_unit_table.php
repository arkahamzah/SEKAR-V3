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
        Schema::create('master_prefix_unit', function (Blueprint $table) {
            $table->string('KODE_PREFIX', 20)->primary();
            $table->string('V_SHORT_UNIT', 150)->nullable();
            $table->string('V_SHORT_POSISI', 150)->nullable();
            $table->string('C_KODE_POSISI', 60)->nullable();
            $table->string('V_SHORT_DIVISI', 150)->nullable();
            $table->string('V_BAND_POSISI', 4)->nullable();
            $table->string('C_KODE_DIVISI', 50)->nullable();
            $table->string('C_PERSONNEL_AREA', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_prefix_unit');
    }
};
