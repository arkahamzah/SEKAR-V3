<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Generate monthly iuran records on the 1st of each month at 00:01
        $schedule->command('iuran:generate-monthly')
                 ->monthlyOn(1, '00:01')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onSuccess(function () {
                     \Log::info('Monthly iuran generation completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('Monthly iuran generation failed');
                 });

        // Update overdue payments daily at 06:00
        $schedule->command('iuran:generate-monthly --force')
                 ->dailyAt('06:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->environments(['production']); // Only run in production

        // Optional: Generate previous months' records if missing (maintenance task)
        // This can be run manually or weekly for data integrity
        $schedule->command('iuran:generate-monthly --year=' . now()->subMonth()->year . ' --month=' . now()->subMonth()->month)
                 ->weekly()
                 ->sundays()
                 ->at('02:00')
                 ->withoutOverlapping()
                 ->environments(['production']);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}