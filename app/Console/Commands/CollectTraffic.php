<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Customer;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CollectTraffic extends Command
{
    protected $signature = 'traffic:collect';
    protected $description = 'Collect PPPoE session traffic data from all routers';

    public function handle(): int
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->get();

        foreach ($areas as $area) {
            try {
                $mikrotik = MikroTikService::forArea($area);
                $sessions = $mikrotik->getActiveSessions();
                if (!($sessions['success'] ?? false)) continue;

                $customers = Customer::where('area_id', $area->id)
                    ->whereNotNull('pppoe_user')
                    ->get()
                    ->keyBy('pppoe_user');

                foreach ($sessions['data'] as $session) {
                    $username = $session['name'] ?? '';
                    $customer = $customers->get($username);
                    if (!$customer) continue;

                    $bytesIn = (int)($session['bytes-in'] ?? 0);
                    $bytesOut = (int)($session['bytes-out'] ?? 0);

                    $cacheKey = "traffic_baseline_{$area->id}_{$username}";
                    $baseline = Cache::get($cacheKey, ['in' => 0, 'out' => 0]);

                    // Calculate delta (handle counter reset)
                    $deltaIn = $bytesIn >= $baseline['in'] ? $bytesIn - $baseline['in'] : $bytesIn;
                    $deltaOut = $bytesOut >= $baseline['out'] ? $bytesOut - $baseline['out'] : $bytesOut;

                    // Store new baseline
                    Cache::put($cacheKey, ['in' => $bytesIn, 'out' => $bytesOut], now()->addHours(2));

                    // Skip if zero delta (first run or no traffic)
                    if ($deltaIn === 0 && $deltaOut === 0) continue;

                    $today = today()->toDateString();

                    // Workaround for updateOrInsert with DB::raw:
                    // Try update first, if 0 rows affected then insert
                    $affected = DB::table('traffic_daily')
                        ->where('customer_id', $customer->id)
                        ->where('date', $today)
                        ->update([
                            'bytes_in' => DB::raw("bytes_in + {$deltaIn}"),
                            'bytes_out' => DB::raw("bytes_out + {$deltaOut}"),
                        ]);

                    if ($affected === 0) {
                        DB::table('traffic_daily')->insert([
                            'customer_id' => $customer->id,
                            'date' => $today,
                            'bytes_in' => $deltaIn,
                            'bytes_out' => $deltaOut,
                            'area_id' => $area->id,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Traffic collect failed for area {$area->name}: {$e->getMessage()}");
            }
        }

        $this->info('Traffic collection complete.');
        return self::SUCCESS;
    }
}
