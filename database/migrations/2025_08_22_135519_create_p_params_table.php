<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('p_params', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('NOMINAL_IURAN_WAJIB', 50)->nullable();
            $table->string('NOMINAL_BANPERS', 50)->nullable();
            $table->string('CREATED_BY', 255)->nullable();
            $table->string('TAHUN', 4)->nullable();
            $table->char('IS_AKTIF', 2)->nullable();
            $table->timestamps();
        });

        // Memasukkan data awal langsung ke dalam tabel
        DB::table('p_params')->insert([
            [
                'ID' => 1,
                'NOMINAL_IURAN_WAJIB' => '25000',
                'NOMINAL_BANPERS' => '25500',
                'CREATED_BY' => '',
                'CREATED_AT' => '2025-07-22 09:20:34',
                'TAHUN' => '2025',
                'IS_AKTIF' => '1'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('p_params');
    }
};