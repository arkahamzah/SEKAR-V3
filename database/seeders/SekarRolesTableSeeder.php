<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SekarRolesTableSeeder extends Seeder
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
        DB::table('t_sekar_roles')->truncate();

        $roles_data = array(
          array('ID' => '1','NAME' => 'ADM','DESC' => 'Administrator','IS_AKTIF' => '1'),
          array('ID' => '2','NAME' => 'ADMIN_DPP','DESC' => 'Admin DPP','IS_AKTIF' => '1'),
          array('ID' => '3','NAME' => 'ADMIN_DPW','DESC' => 'Admin DPW','IS_AKTIF' => '1'),
          array('ID' => '4','NAME' => 'ADMIN_DPD','DESC' => 'Admin DPD','IS_AKTIF' => '1')
        );
        
        // Masukkan data ke dalam tabel
        DB::table('t_sekar_roles')->insert($roles_data);

        // Aktifkan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}