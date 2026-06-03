<?php

namespace App\Jobs;

use App\Services\AcsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshAllAcsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Only try once — ACS refresh is fire-and-forget per device.
     */
    public int $tries = 1;

    /**
     * Allow up to 10 minutes for large fleets (500 devices).
     */
    public int $timeout = 600;

    public function __construct(
        protected int $limit = 500
    ) {}

    public function handle(AcsService $acs): void
    {
        Log::info("RefreshAllAcsJob: fetching up to {$this->limit} devices");

        Cache::put('acs_refresh_status', [
            'status'    => 'running',
            'started_at'=> now()->toIso8601String(),
            'count'     => 0,
            'failed'    => 0,
            'message'   => 'Sedang mengirim refresh ke device online...',
        ], now()->addHours(1));

        try {
            $rawDevices = $acs->getDevices($this->limit);
            $count  = 0;
            $failed = 0;

            foreach ($rawDevices as $d) {
                $id = $d['_id'] ?? null;
                if (!$id || str_starts_with($id, 'DISCOVERY')) {
                    continue;
                }

                $parsed = $acs->parseDevice($d);
                if (!$parsed['online']) {
                    continue; // Only refresh online devices
                }

                $ok = $acs->refresh($id);
                $ok ? $count++ : $failed++;
            }

            $message = "Refresh task dikirim ke {$count} device online." . ($failed ? " ({$failed} gagal)" : '');

            Cache::put('acs_refresh_status', [
                'status'      => 'done',
                'finished_at' => now()->toIso8601String(),
                'count'       => $count,
                'failed'      => $failed,
                'message'     => $message,
            ], now()->addHours(1));

            Log::info("RefreshAllAcsJob: done — refreshed={$count}, failed={$failed}");

        } catch (\Throwable $e) {
            Cache::put('acs_refresh_status', [
                'status'  => 'failed',
                'message' => "Refresh gagal: {$e->getMessage()}",
            ], now()->addHours(1));

            Log::error("RefreshAllAcsJob: exception: {$e->getMessage()}");
            throw $e;
        }
    }
}
