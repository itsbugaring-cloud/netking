<?php

namespace App\Jobs;

use App\Models\Olt;
use App\Services\OltService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOltJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max attempts before marking failed.
     */
    public int $tries = 2;

    /**
     * Timeout in seconds (OLT telnet can be slow).
     */
    public int $timeout = 300;

    public function __construct(
        protected Olt $olt
    ) {}

    public function handle(): void
    {
        Log::info("SyncOltJob: start sync for OLT [{$this->olt->id}] {$this->olt->name}");

        // Mark as actively syncing
        $this->olt->update([
            'sync_status'  => 'syncing',
            'sync_message' => 'Sedang mengambil data ONT dari perangkat...',
        ]);

        try {
            $result = OltService::for($this->olt)->syncAll();

            if (isset($result['error'])) {
                $this->olt->update([
                    'sync_status'  => 'failed',
                    'sync_message' => "Sync gagal: {$result['error']}",
                ]);

                Log::error("SyncOltJob: failed for OLT {$this->olt->id}: {$result['error']}");
                return;
            }

            $this->olt->update([
                'sync_status'  => 'done',
                'sync_message' => "Berhasil sync {$result['total']} ONTs — {$result['created']} baru, {$result['updated']} diperbarui.",
                'synced_at'    => now(),
            ]);

            Log::info("SyncOltJob: done for OLT {$this->olt->id}: total={$result['total']}, created={$result['created']}, updated={$result['updated']}");

        } catch (\Throwable $e) {
            $this->olt->update([
                'sync_status'  => 'failed',
                'sync_message' => "Sync gagal (exception): {$e->getMessage()}",
            ]);

            Log::error("SyncOltJob: exception for OLT {$this->olt->id}: {$e->getMessage()}");

            // Re-throw so the job is marked as failed in the jobs table
            throw $e;
        }
    }

    /**
     * Called after all retries exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        $this->olt->update([
            'sync_status'  => 'failed',
            'sync_message' => "Gagal setelah {$this->tries} percobaan: {$exception->getMessage()}",
        ]);

        Log::error("SyncOltJob: permanently failed for OLT {$this->olt->id}: {$exception->getMessage()}");
    }
}
