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
        Schema::create('t_sekar_pengurus', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('N_NIK', 30)->nullable();
            $table->string('V_SHORT_POSISI', 100)->nullable();
            $table->string('V_SHORT_UNIT', 100)->nullable();
            $table->string('DPP', 100)->nullable();
            $table->string('DPW', 255)->nullable();
            $table->string('DPD', 255)->nullable();
            $table->integer('ID_ROLES')->nullable();
            $table->string('CREATED_BY', 100)->nullable();
            $table->string('UPDATED_BY', 50)->nullable();
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
        Schema::dropIfExists('t_sekar_pengurus');
    }
};