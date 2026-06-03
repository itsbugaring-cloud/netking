<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Create a daily MySQL database backup';

    public function handle()
    {
        $filename = 'backup-' . date('Y-m-d_His') . '.sql.gz';
        $path = storage_path('app/backups/' . $filename);

        // Ensure backup directory exists
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $db = config('database.connections.mysql');

        $cmd = sprintf(
            'mysqldump -u%s -p%s -h%s --port=%s %s 2>/dev/null | gzip > %s',
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            escapeshellarg($db['port'] ?? '3306'),
            escapeshellarg($db['database']),
            escapeshellarg($path)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error("Backup failed with exit code {$returnCode}");
            return 1;
        }

        $size = round(filesize($path) / 1024, 1);
        $this->info("✅ Backup created: {$filename} ({$size} KB)");

        // Clean up old backups (keep last 7 days)
        $this->cleanOldBackups(7);

        return 0;
    }

    private function cleanOldBackups(int $keepDays)
    {
        $dir = storage_path('app/backups');
        $cutoff = now()->subDays($keepDays)->timestamp;
        $deleted = 0;

        foreach (glob($dir . '/backup-*.sql.gz') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("🗑 Cleaned {$deleted} old backup(s) older than {$keepDays} days");
        }
    }
}
