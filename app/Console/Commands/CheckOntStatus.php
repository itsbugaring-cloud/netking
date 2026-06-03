<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Setting;
use App\Services\AcsService;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOntStatus extends Command
{
    protected $signature = 'netking:check-ont-status';
    protected $description = 'Check all ONT status via GenieACS and send WhatsApp alert if device goes offline';

    public function handle(AcsService $acs, WhatsAppService $wa): int
    {
        // Check if alerts are enabled
        $alertEnabled = Setting::get('alert_ont_enabled', 'true') === 'true';
        if (!$alertEnabled) {
            $this->info('ONT alerts disabled in settings. Skipping.');
            return 0;
        }

        $adminPhone = Setting::get('alert_admin_phone', '');

        $this->info('Fetching devices from GenieACS...');

        try {
            $rawDevices = $acs->getDevices(500);
        } catch (\Exception $e) {
            $this->error('GenieACS unreachable: ' . $e->getMessage());
            Log::error('CheckOntStatus: GenieACS unreachable — ' . $e->getMessage());
            return 1;
        }

        $devices = collect($rawDevices)->map(fn($d) => $acs->parseDevice($d))->keyBy('serial');
        $this->info("Found {$devices->count()} devices in GenieACS.");

        // Get all customers with ONT serial
        $customers = Customer::with(['area', 'partner'])
            ->whereNotNull('ont_sn')
            ->where('ont_sn', '!=', '')
            ->where('status', 'active')
            ->get();

        $this->info("Checking {$customers->count()} active customers with ONT...");

        $newlyOffline = [];
        $newlyOnline = [];

        foreach ($customers as $customer) {
            $sn = $customer->ont_sn;
            $device = $devices->get($sn);
            $currentOnline = $device ? ($device['online'] ?? false) : false;

            // Get previous status from cache
            $cacheKey = "ont_status:{$sn}";
            $previousOnline = Cache::get($cacheKey);

            // Store current status
            Cache::put($cacheKey, $currentOnline, now()->addHours(1));

            // Log to database
            DB::table('ont_status_logs')->insert([
                'ont_sn'      => $sn,
                'customer_id' => $customer->id,
                'is_online'   => $currentOnline,
                'wan_ip'      => $device['wan_ip'] ?? null,
                'checked_at'  => now(),
            ]);

            // Detect newly offline (was online, now offline)
            if ($previousOnline === true && $currentOnline === false) {
                $newlyOffline[] = $customer;
            }

            // Detect newly online (was offline, now online)
            if ($previousOnline === false && $currentOnline === true) {
                $newlyOnline[] = $customer;
            }
        }

        // Send alerts for newly offline devices
        foreach ($newlyOffline as $customer) {
            $area = $customer->area?->name ?? 'Unknown';
            $partner = $customer->partner;

            $message = "⚠️ *ONT OFFLINE Alert*\n\n";
            $message .= "Customer: *{$customer->name}*\n";
            $message .= "Area: {$area}\n";
            $message .= "ONT SN: `{$customer->ont_sn}`\n";
            $message .= "Waktu: " . now()->format('d/m/Y H:i') . "\n\n";
            $message .= "ONT pelanggan ini baru saja offline. Mohon segera cek.";

            // Send to admin
            if ($adminPhone) {
                $result = $wa->sendMessage($adminPhone, $message);
                if ($result['success']) {
                    $this->info("✓ Alert sent to admin for {$customer->name}");
                }
            }

            // Send to partner
            if ($partner && $partner->phone) {
                $partnerMsg = "⚠️ *ONT Pelanggan OFFLINE*\n\n";
                $partnerMsg .= "Customer: *{$customer->name}*\n";
                $partnerMsg .= "Area: {$area}\n";
                $partnerMsg .= "Waktu: " . now()->format('d/m/Y H:i') . "\n\n";
                $partnerMsg .= "Mohon cek koneksi pelanggan Anda.";

                $wa->sendMessage($partner->phone, $partnerMsg);
            }

            Log::warning("ONT OFFLINE: {$customer->name} (SN: {$customer->ont_sn}, Area: {$area})");
        }

        // Log newly online (no alert needed, just info)
        foreach ($newlyOnline as $customer) {
            $this->info("↑ ONT back online: {$customer->name}");
            Log::info("ONT ONLINE: {$customer->name} (SN: {$customer->ont_sn})");
        }

        // Cleanup old logs (keep 7 days)
        $deleted = DB::table('ont_status_logs')
            ->where('checked_at', '<', now()->subDays(7))
            ->delete();

        $this->info("Summary: {$customers->count()} checked, " . count($newlyOffline) . " newly offline, " . count($newlyOnline) . " back online, {$deleted} old logs cleaned.");

        return 0;
    }
}
