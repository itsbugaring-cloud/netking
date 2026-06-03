<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Olt;
use App\Models\Ont;
use App\Models\AdminNotification;
use App\Models\ActivityLog;
use App\Services\OltService;
use Illuminate\Support\Facades\Log;

class SyncAllOlts extends Command
{
    protected $signature   = 'olt:sync-all {--olt= : Sync only a specific OLT ID}';
    protected $description = 'Auto-sync all OLTs and alert if ONTs go offline';

    public function handle(): int
    {
        $query = Olt::query();

        if ($id = $this->option('olt')) {
            $query->where('id', $id);
        }

        $olts = $query->get();

        if ($olts->isEmpty()) {
            $this->warn('No OLTs found.');
            return 0;
        }

        $this->info("Starting auto-sync for {$olts->count()} OLT(s) at " . now()->format('H:i:s'));

        $totalCreated = 0;
        $totalUpdated = 0;
        $offlineAlerts = [];

        foreach ($olts as $olt) {
            $this->line("  → Syncing [{$olt->name}] ({$olt->brand} / {$olt->ip_address})...");

            // Mark as syncing
            $olt->update(['sync_status' => 'syncing', 'sync_message' => 'Sedang mengambil data ONT...']);

            try {
                // Snapshot of which ONTs were online BEFORE this sync
                $prevOnline = Ont::where('olt_id', $olt->id)
                    ->where('status', 'online')
                    ->pluck('id', 'serial_number')
                    ->toArray();

                $service = new OltService($olt);
                $result  = $service->syncAll();

                $created = $result['created'] ?? 0;
                $updated = $result['updated'] ?? 0;
                $total   = $result['total']   ?? 0;
                $error   = $result['error']   ?? null;

                if ($error) {
                    $this->error("     ✗ Error: {$error}");
                    $olt->update([
                        'sync_status'  => 'failed',
                        'sync_message' => "Gagal: {$error}",
                    ]);
                    continue;
                }

                $olt->update([
                    'sync_status'  => 'done',
                    'sync_message' => "Berhasil sync {$total} ONTs — {$created} baru, {$updated} diperbarui.",
                    'synced_at'    => now(),
                ]);

                $this->info("     ✓ {$total} ONTs — {$created} new, {$updated} updated");
                $totalCreated += $created;
                $totalUpdated += $updated;

                // Detect newly-offline ONTs (were online before, now offline)
                $nowOffline = Ont::where('olt_id', $olt->id)
                    ->where('status', 'offline')
                    ->whereIn('serial_number', array_keys($prevOnline))
                    ->get();

                foreach ($nowOffline as $ont) {
                    $offlineAlerts[] = "[{$olt->name}] ONT {$ont->serial_number}" .
                        ($ont->description ? " ({$ont->description})" : '') .
                        " went OFFLINE";
                    $this->warn("     ⚠ ONT {$ont->serial_number} went offline!");
                }

            } catch (\Throwable $e) {
                $this->error("     ✗ Exception: {$e->getMessage()}");
                Log::error("olt:sync-all failed for OLT [{$olt->name}]: " . $e->getMessage());
                $olt->update([
                    'sync_status'  => 'failed',
                    'sync_message' => "Exception: " . substr($e->getMessage(), 0, 200),
                ]);
            }

            // Small pause between OLTs to avoid flooding the network
            sleep(2);
        }

        // Send a single grouped notification if any ONTs went offline
        if (!empty($offlineAlerts)) {
            $count   = count($offlineAlerts);
            $preview = implode("\n", array_slice($offlineAlerts, 0, 5));
            if ($count > 5) $preview .= "\n...and " . ($count - 5) . " more";

            try {
                AdminNotification::notify(
                    'offline',
                    "⚠ {$count} ONT(s) Offline",
                    $preview,
                    'bx-wifi-off',
                    'red'
                );
                ActivityLog::log('alert', "Auto-sync: {$count} ONT(s) went offline.\n{$preview}");
            } catch (\Throwable $e) {
                // Don't fail sync just because notification failed
            }
        }

        $this->info("Done. Total: {$totalCreated} new, {$totalUpdated} updated.");
        return 0;
    }
}
