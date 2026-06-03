<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AcsService;

class CustomerOntController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();
        $acs = app(AcsService::class);
        $device = null;

        // Use ont_sn from customer record, or fall back to ont model's serial_number
        $effectiveSn = $customer->ont_sn ?: ($customer->ont?->serial_number ?? null);

        if ($effectiveSn) {
            $devices = $acs->getDevices(200, 0, $effectiveSn);
            $rawDevice = $this->resolveFirstDevice($devices);

            if ($rawDevice) {
                $device = $acs->parseDevice($rawDevice);
                $device['_raw_id'] = $rawDevice['_id'] ?? null;
                $acs->refresh($rawDevice['_id']);
            }
        }

        $payload = null;

        if ($device) {
            $status = strtolower((string) ($device['status'] ?? ''));
            $payload = [
                'status'        => ($device['status'] ?? '') ?: null,
                'online'        => $status === 'online' || $status === 'active',
                'brand'         => ($device['brand'] ?? '') ?: null,
                'model'         => (isset($device['model']) && $device['model'] !== 'Unknown' ? $device['model'] : '') ?: null,
                'serial_number' => ($device['serial_number'] ?? '') ?: $customer->ont_sn,
                'wan_ip'        => ($device['wan_ip'] ?? '') ?: ($customer->remote_ip ?: null),
                'ssid'          => ($device['ssid'] ?? '') ?: null,
                'signal'        => ($device['rx_power'] ?? '') ?: (($device['signal'] ?? '') ?: null),
                'rx_power'      => ($device['rx_power'] ?? '') ?: (($device['signal'] ?? '') ?: null),
                'uptime'        => ($device['uptime'] ?? '') ?: null,
                '_raw_id'       => $device['_raw_id'] ?? null,
            ];
        }

        return response()->json([
            'data' => $payload,
            'device' => $device,
            'ont_sn' => $customer->ont_sn,
        ]);
    }

    private function resolveFirstDevice(array $devices): ?array
    {
        if (isset($devices[0]) && is_array($devices[0])) {
            return $devices[0];
        }

        foreach (['data', 'items', 'results', 'devices'] as $key) {
            if (isset($devices[$key]) && is_array($devices[$key]) && isset($devices[$key][0]) && is_array($devices[$key][0])) {
                return $devices[$key][0];
            }
        }

        if (isset($devices['_id'])) {
            return $devices;
        }

        return null;
    }

    public function reboot(Request $request)
    {
        $customer = $request->user();
        $acs = app(AcsService::class);

        $effectiveSn = $customer->ont_sn ?: ($customer->ont?->serial_number ?? null);
        if (!$effectiveSn) {
            return response()->json(['message' => 'No ONT registered'], 400);
        }

        $devices = $acs->getDevices(200, 0, $effectiveSn);
        if (empty($devices) || !isset($devices[0]['_id'])) {
            return response()->json(['message' => 'ONT device offline or not found'], 404);
        }

        $success = $acs->reboot($devices[0]['_id']);

        if ($success) {
            return response()->json(['message' => 'Reboot command sent successfully']);
        }

        return response()->json(['message' => 'Failed to send reboot command'], 500);
    }

    public function updateWifi(Request $request)
    {
        $request->validate([
            'ssid' => 'required|string|min:4|max:32',
            'password' => 'nullable|string|min:8|max:63',
        ]);

        $customer = $request->user();
        $acs = app(AcsService::class);

        $effectiveSn = $customer->ont_sn ?: ($customer->ont?->serial_number ?? null);
        if (!$effectiveSn) {
            return response()->json(['message' => 'No ONT registered'], 400);
        }

        $devices = $acs->getDevices(200, 0, $effectiveSn);
        if (empty($devices) || !isset($devices[0]['_id'])) {
            return response()->json(['message' => 'ONT device offline or not found'], 404);
        }

        $deviceId = $devices[0]['_id'];
        $success = $acs->setSsid($deviceId, $request->ssid, $request->password);

        if ($success) {
            $acs->refresh($deviceId);
            return response()->json(['message' => 'WiFi settings updated successfully']);
        }

        return response()->json(['message' => 'Failed to update WiFi settings'], 500);
    }
}
