<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Scheduled commands

// [REMOVED] Invoice system replaced by payments
// [REMOVED] Auto-suspend command removed — invoice system replaced by payments

// Daily database backup at 03:00 WIB
Schedule::command('backup:database')
    ->dailyAt('03:00')
    ->timezone('Asia/Jakarta');

// Auto-sync semua OLT setiap 30 menit untuk update data ONT, rx_power, dan status
Schedule::command('olt:sync-all')
    ->everyThirtyMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(10)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/olt-sync.log'));
