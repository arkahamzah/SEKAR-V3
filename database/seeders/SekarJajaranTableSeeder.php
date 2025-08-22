<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SekarJajaranTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nonaktifkan pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Kosongkan tabel
        DB::table('t_sekar_jajaran')->truncate();

        $jajaran_data = array(
          array('ID' => '1','N_NIK' => '880001','V_NAMA_KARYAWAN' => 'NASHRI AMAN RAFA','ID_JAJARAN' => '1','START_DATE' => '2025-01-01 00:00:00','END_DATE' => '2028-01-01 00:00:00','CREATED_BY' => 'SYSTEM','CREATED_AT' => '2025-08-13 11:24:56','IS_AKTIF' => '1'),
          array('ID' => '2','N_NIK' => '880002','V_NAMA_KARYAWAN' => 'WAHYU MUHAMMAD IQBAL','ID_JAJARAN' => '3','START_DATE' => '2025-01-01 00:00:00','END_DATE' => '2028-01-01 00:00:00','CREATED_BY' => 'SYSTEM','CREATED_AT' => '2025-08-13 11:24:56','IS_AKTIF' => '1')
        );
        
        // Masukkan data ke dalam tabel
        DB::table('t_sekar_jajaran')->insert($jajaran_data);

        // Aktifkan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}