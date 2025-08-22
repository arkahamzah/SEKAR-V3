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
        Schema::create('t_sekar_jajaran', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('N_NIK', 50)->nullable();
            $table->string('V_NAMA_KARYAWAN', 150)->nullable();
            $table->string('ID_JAJARAN', 255)->nullable();
            $table->dateTime('START_DATE')->nullable();
            $table->dateTime('END_DATE')->nullable();
            $table->char('CREATED_BY', 30)->nullable();
            $table->string('IS_AKTIF', 2)->nullable();
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
        Schema::dropIfExists('t_sekar_jajaran');
    }
};