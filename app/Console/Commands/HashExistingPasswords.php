<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class HashExistingPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hash-existing-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all users with plain text "Telkom" password and hash them correctly.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai proses hashing password...');

        // Proses pengguna dalam chunk untuk menghindari masalah memori
        User::where('password', 'Telkom')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $user->password = Hash::make('Telkom');
                $user->save();
                $this->info("Password untuk NIK: {$user->nik} berhasil di-hash.");
            }
        });

        $this->info('======================================');
        $this->info('Proses hashing password telah selesai.');
        return 0;
    }
}