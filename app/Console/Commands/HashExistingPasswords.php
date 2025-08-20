<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
    // Deskripsi diperbarui agar lebih akurat
    protected $description = 'Set a new hashed password ("Telkom") for all users in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai proses hashing password untuk semua pengguna...');

        // Ambil total pengguna untuk progress bar
        $totalUsers = User::count();
        if ($totalUsers === 0) {
            $this->warn('Tidak ada pengguna di dalam database untuk diproses.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();

        // Proses SEMUA pengguna dalam chunk untuk efisiensi memori
        User::chunkById(200, function ($users) use ($progressBar) {
            foreach ($users as $user) {
                // Langsung hash password menjadi 'Telkom' tanpa memeriksa password lama
                $user->password = Hash::make('Telkom');
                $user->save();

                // Majukan progress bar setelah setiap pengguna diproses
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->info(''); // Memberi baris baru setelah progress bar selesai
        $this->info('======================================');
        $this->info('Proses hashing password telah selesai untuk semua pengguna.');
        return 0;
    }
}