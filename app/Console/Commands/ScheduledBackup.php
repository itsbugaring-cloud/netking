<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\RouterBackup;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScheduledBackup extends Command
{
    protected $signature = 'backup:routers {--type=text : Backup type (text or binary)}';
    protected $description = 'Create scheduled backups for all routers';

    public function handle(): int
    {
        $type = $this->option('type');
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        $success = 0;
        $failed = 0;

        foreach ($areas as $area) {
            try {
                $mikrotik = MikroTikService::forArea($area);
                $timestamp = now()->format('Ymd_His');
                $areaSlug = str_replace(' ', '-', strtolower($area->name));

                if ($type === 'binary') {
                    $backupName = "scheduled_{$areaSlug}_{$timestamp}";
                    $result = $mikrotik->createBackup($backupName);

                    if (!$result['success']) {
                        $this->error("Failed: {$area->name} — {$result['error']}");
                        $failed++;
                        continue;
                    }

                    sleep(2);
                    $fileInfo = $mikrotik->getFileContents($result['filename']);

                    RouterBackup::create([
                        'area_id' => $area->id,
                        'filename' => $result['filename'],
                        'type' => 'binary',
                        'size_bytes' => (int)($fileInfo['data']['size'] ?? 0),
                        'notes' => 'Scheduled backup',
                    ]);
                } else {
                    $result = $mikrotik->createExport();

                    if (!$result['success']) {
                        $this->error("Failed: {$area->name} — {$result['error']}");
                        $failed++;
                        continue;
                    }

                    $exportContent = '';
                    if (is_array($result['data'])) {
                        $exportContent = implode("\n", array_map(function ($line) {
                            return is_array($line) ? implode(' ', $line) : (string)$line;
                        }, $result['data']));
                    } else {
                        $exportContent = (string)$result['data'];
                    }

                    $filename = "scheduled_{$areaSlug}_{$timestamp}.rsc";
                    $storagePath = "backups/{$area->id}/{$filename}";
                    Storage::disk('local')->put($storagePath, $exportContent);

                    RouterBackup::create([
                        'area_id' => $area->id,
                        'filename' => $filename,
                        'type' => 'text',
                        'size_bytes' => strlen($exportContent),
                        'notes' => 'Scheduled backup',
                    ]);
                }

                $success++;
                $this->info("OK: {$area->name}");
            } catch (\Throwable $e) {
                Log::error("Scheduled backup failed for {$area->name}: {$e->getMessage()}");
                $this->error("Failed: {$area->name} — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Backup complete. Success: {$success}, Failed: {$failed}");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
