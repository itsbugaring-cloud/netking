<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Scheduled commands

// Schedule monthly invoice generation on the 1st of each month at 00:00
Schedule::command('invoices:generate')
    ->monthlyOn(1, '00:00')
    ->timezone('Asia/Jakarta');

// Auto-suspend customers with overdue invoices (daily at 02:00 WIB)
// DINONAKTIFKAN SEMENTARA - bisnis sudah disiapkan untuk due date tanggal 20
// dan eligible suspend mulai tanggal 21, tetapi automation belum diaktifkan.
// Schedule::command('customers:suspend-overdue')
//     ->dailyAt('02:00')
//     ->timezone('Asia/Jakarta');

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
