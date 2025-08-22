<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Pastikan untuk mengimpor DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Membuat struktur tabel
        Schema::create('m_jajaran', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('NAMA_JAJARAN', 255)->nullable();
            $table->string('IS_AKTIF', 255)->nullable();
        });

        // Memasukkan data awal langsung ke dalam tabel
        DB::table('m_jajaran')->insert([
            ['ID' => 1, 'NAMA_JAJARAN' => 'KETUA UMUM', 'IS_AKTIF' => '1'],
            ['ID' => 3, 'NAMA_JAJARAN' => 'SEKRETARIS JENDRAL', 'IS_AKTIF' => '1'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_jajaran');
    }
};