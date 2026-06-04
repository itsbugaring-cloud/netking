<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerOntController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        // [REMOVED] ACS/GenieACS feature removed — return database-only ONT info
        $payload = null;

        if ($customer->ont_sn || $customer->ont) {
            $ont = $customer->ont;
            $payload = [
                'status'        => $ont?->status,
                'online'        => $ont?->status === 'online',
                'brand'         => null,
                'model'         => $ont?->model,
                'serial_number' => $customer->ont_sn ?: $ont?->serial_number,
                'wan_ip'        => $customer->remote_ip ?: null,
                'ssid'          => null,
                'signal'        => $ont?->rx_power ? number_format((float) $ont->rx_power, 2) . ' dBm' : null,
                'rx_power'      => $ont?->rx_power ? number_format((float) $ont->rx_power, 2) . ' dBm' : null,
                'uptime'        => null,
                '_raw_id'       => null,
            ];
        }

        return response()->json([
            'data' => $payload,
            'device' => $payload,
            'ont_sn' => $customer->ont_sn,
        ]);
    }

    public function reboot(Request $request)
    {
        // [REMOVED] ACS/GenieACS feature removed
        return response()->json(['message' => 'Remote reboot is not available (ACS feature removed)'], 503);
    }

    public function updateWifi(Request $request)
    {
        // [REMOVED] ACS/GenieACS feature removed
        return response()->json(['message' => 'WiFi management is not available (ACS feature removed)'], 503);
    }
}
