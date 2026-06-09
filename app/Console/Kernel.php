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
        // Auto-sync all OLTs every 30 minutes, in the background
        // so it doesn't block other scheduled tasks.
        $schedule->command('olt:sync-all')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping(10)    // Skip if a previous run is still going (max 10 min overlap lock)
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/olt-sync.log'));

        // [REMOVED] ACS/GenieACS refresh — feature removed

        // [REMOVED] Invoice system replaced by payments
        // $schedule->command('invoices:generate')
        //          ->monthlyOn(1, '07:00')
        //          ->withoutOverlapping()
        //          ->appendOutputTo(storage_path('logs/invoice-generate.log'));

        // Collect traffic data from all routers every 5 minutes
        $schedule->command('traffic:collect')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/traffic-collect.log'));

        // Aggregate daily traffic into monthly and clean old records
        $schedule->command('traffic:aggregate')
                 ->dailyAt('01:00')
                 ->appendOutputTo(storage_path('logs/traffic-aggregate.log'));

        // Scheduled router backup (text export) every Sunday at 02:00
        $schedule->command('backup:routers --type=text')
                 ->weeklyOn(0, '02:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/router-backup.log'));

        // Suspend otomatis pelanggan yang invoice-nya jatuh tempo + 7 hari (tiap hari pukul 08:00)
        // DINONAKTIFKAN SEMENTARA - 2026-04-16
        // $schedule->command('customers:suspend-overdue', ['--days=7'])
        //          ->dailyAt('08:00')
        //          ->withoutOverlapping()
        //          ->appendOutputTo(storage_path('logs/suspend-overdue.log'));

        // [REMOVED] Commission recap — feature removed
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
