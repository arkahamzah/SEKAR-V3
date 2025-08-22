<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            KaryawanTableSeeder::class,
            IuranTableSeeder::class,
            SekarPengurusTableSeeder::class,
            SekarRolesTableSeeder::class,
            SekarJajaranTableSeeder::class,
            MappingDpdTableSeeder::class,
            MasterPrefixUnitTableSeeder::class,
        ]);
    }
}