<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Ont;
use App\Models\Package;
use App\Services\AcsService;
use App\Services\ProvisioningService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerCustomerController extends Controller
{
    public function packages(Request $request)
    {
        $partner = $request->user();

        $packages = Package::query()
            ->where('is_active', true)
            ->where('area_id', $partner->area_id)
            ->orderBy('speed_down')
            ->orderBy('name')
            ->get()
            ->map(fn (Package $package) => [
                'id' => $package->id,
                'name' => $package->name,
                'speed_down' => $package->speed_down,
                'speed_up' => $package->speed_up,
                'speed_label' => $package->speed_label,
                'price' => $package->price,
                'formatted_price' => $package->formatted_price,
                'mikrotik_profile' => $package->mikrotik_profile,
            ])
            ->values();

        return response()->json([
            'data' => $packages,
        ]);
    }

    public function index(Request $request)
    {
        $partner = $request->user();

        $query = Customer::where('area_id', $partner->area_id)
            ->with(['package:id,name', 'area:id,name', 'ont:id,customer_id,status,serial_number']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('pppoe_user', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($request->get('per_page', 100)), 500);
        $customers = $query->orderBy('name')->paginate($perPage);
        $customers->setCollection(
            $this->buildCustomerListPayload($customers->getCollection())
        );

        return response()->json($customers);
    }

    public function store(Request $request, ProvisioningService $provisioningService)
    {
        $partner = $request->user();

        if (!$partner->area_id) {
            return response()->json([
                'message' => 'Partner belum terhubung ke area. Hubungi admin untuk melengkapi area partner.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'package_id' => 'required|integer|exists:packages,id',
            'pppoe_user' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'pppoe_user')->where(
                    fn ($query) => $query->where('area_id', $partner->area_id)
                ),
            ],
            'pppoe_pass' => 'required|string|min:6|max:255',
            'portal_password' => 'required|string|min:6|max:255',
            'ont_sn' => 'nullable|string|max:255',
            'local_address' => 'nullable|string|max:45',
        ]);

        $package = Package::query()
            ->where('id', $validated['package_id'])
            ->where('is_active', true)
            ->where('area_id', $partner->area_id)
            ->first();

        if (!$package) {
            return response()->json([
                'message' => 'Paket tidak tersedia untuk area partner ini.',
            ], 422);
        }

        $customer = $provisioningService->provisionCustomer([
            'partner_id' => $partner->id,
            'area_id' => $partner->area_id,
            'package_id' => $package->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'pppoe_user' => trim($validated['pppoe_user']),
            'pppoe_pass' => $validated['pppoe_pass'],
            'portal_password' => $validated['portal_password'],
            'ont_sn' => $validated['ont_sn'] ?? null,
            'local_address' => $validated['local_address'] ?? null,
        ]);

        $customer->load(['package', 'area']);

        return response()->json([
            'message' => 'Pelanggan berhasil ditambahkan. Secret PPPoE sedang dibuat otomatis.',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'status' => $customer->status,
                'pppoe_user' => $customer->pppoe_user,
                'remote_ip' => $customer->remote_ip,
                'package' => $customer->package?->name,
                'area' => $customer->area?->name,
            ],
        ], 201);
    }

    public function show(Request $request, Customer $customer)
    {
        $this->authorizePartnerCustomer($request, $customer);

        $customer->load(['package', 'area', 'ont', 'odp']);
        $ontData = $this->resolveOntData($customer);

        return response()->json([
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'pppoe_user' => $customer->pppoe_user,
                'ont_sn' => $customer->ont_sn,
                'remote_ip' => $customer->remote_ip,
                'status' => $customer->status,
                'package' => $customer->package?->name,
                'area' => $customer->area?->name,
                'ont' => $ontData,
            ],
        ]);
    }

    public function signal(Request $request, Customer $customer, AcsService $acs)
    {
        $this->authorizePartnerCustomer($request, $customer);

        $customer->load(['area', 'ont']);

        $effectiveSn = $customer->ont_sn ?? $customer->ont?->serial_number;
        if (!$effectiveSn) {
            $rxPower = $customer->ont?->rx_power;
            return response()->json([
                'success' => (bool) $rxPower,
                'source'  => 'database',
                'data'    => $rxPower ? [
                    'rx_power'      => (float) $rxPower,
                    'rx_power_str'  => number_format((float) $rxPower, 2) . ' dBm',
                    'quality'       => $this->signalQuality((float) $rxPower),
                    'serial_number' => $customer->ont?->serial_number,
                    'model'         => $customer->ont?->model,
                    'status'        => $customer->ont?->status,
                ] : null,
                'message' => $rxPower ? null : 'Serial ONT tidak terdaftar untuk pelanggan ini.',
            ]);
        }

        try {
            $device = $this->findOntDevice($acs, $effectiveSn);
            if (!$device) {
                $rxPower = $customer->ont?->rx_power;
                return response()->json([
                    'success' => (bool) $rxPower,
                    'source'  => 'database',
                    'data'    => $rxPower ? [
                        'rx_power'      => (float) $rxPower,
                        'rx_power_str'  => number_format((float) $rxPower, 2) . ' dBm',
                        'quality'       => $this->signalQuality((float) $rxPower),
                        'serial_number' => $effectiveSn,
                        'model'         => $customer->ont?->model,
                        'status'        => 'offline',
                    ] : null,
                    'message' => 'ONT tidak ditemukan di ACS.' . ($rxPower ? ' Menampilkan data terakhir.' : ''),
                ]);
            }

            $parsed   = $acs->parseDevice($device);
            $rxRaw    = $parsed['rx_power'] ?? $parsed['signal'] ?? $customer->ont?->rx_power;
            $rxPower  = $rxRaw !== null ? (float) str_replace(' dBm', '', $rxRaw) : null;

            return response()->json([
                'success' => true,
                'source'  => 'acs_live',
                'data'    => [
                    'rx_power'      => $rxPower,
                    'rx_power_str'  => $rxPower !== null ? number_format($rxPower, 2) . ' dBm' : null,
                    'quality'       => $rxPower !== null ? $this->signalQuality($rxPower) : 'unknown',
                    'serial_number' => $parsed['serial_number'] ?? $customer->ont_sn,
                    'model'         => $parsed['model'] ?? $customer->ont?->model,
                    'brand'         => $parsed['brand'] ?? null,
                    'status'        => $parsed['status'] ?? null,
                    'uptime'        => $parsed['uptime'] ?? null,
                    'wan_ip'        => $parsed['wan_ip'] ?? null,
                    'ssid'          => $parsed['ssid'] ?? null,
                    'area'          => $customer->area?->name,
                    'customer_name' => $customer->name,
                    'pppoe_user'    => $customer->pppoe_user,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal membaca sinyal: ' . $e->getMessage()], 500);
        }
    }

    private function signalQuality(float $rxPower): string
    {
        if ($rxPower > -10) return 'too_strong';   // > -10 dBm: terlalu kuat
        if ($rxPower >= -25) return 'good';         // -10 ~ -25: bagus
        if ($rxPower >= -27) return 'fair';         // -25 ~ -27: cukup
        if ($rxPower >= -30) return 'weak';         // -27 ~ -30: lemah
        return 'critical';                          // < -30 dBm: kritis
    }

    public function rebootOnt(Request $request, Customer $customer, AcsService $acs)
    {
        $this->authorizePartnerCustomer($request, $customer);

        $effectiveSn = $customer->ont_sn ?? $customer->ont?->serial_number;
        if (!$effectiveSn) {
            return response()->json(['message' => 'Pelanggan belum memiliki serial ONT.'], 400);
        }

        $device = $this->findOntDevice($acs, $effectiveSn);
        if (!$device || !isset($device['_id'])) {
            return response()->json(['message' => 'ONT sedang offline atau tidak ditemukan di ACS.'], 404);
        }

        $success = $acs->reboot($device['_id']);

        if ($success) {
            return response()->json(['message' => 'Perintah reboot berhasil dikirim ke ONT.']);
        }

        return response()->json(['message' => 'Gagal mengirim perintah reboot ke ONT.'], 500);
    }

    public function updateWifi(Request $request, Customer $customer, AcsService $acs)
    {
        $this->authorizePartnerCustomer($request, $customer);

        $validated = $request->validate([
            'ssid' => 'required|string|min:4|max:32',
            'password' => 'nullable|string|min:8|max:63',
        ]);

        $effectiveSn = $customer->ont_sn ?? $customer->ont?->serial_number;
        if (!$effectiveSn) {
            return response()->json(['message' => 'Pelanggan belum memiliki serial ONT.'], 400);
        }

        $device = $this->findOntDevice($acs, $effectiveSn);
        if (!$device || !isset($device['_id'])) {
            return response()->json(['message' => 'ONT sedang offline atau tidak ditemukan di ACS.'], 404);
        }

        $deviceId = $device['_id'];
        $success = $acs->setSsid($deviceId, $validated['ssid'], $validated['password']);

        if ($success) {
            $acs->refresh($deviceId);
            return response()->json(['message' => 'Pengaturan WiFi berhasil diperbarui.']);
        }

        return response()->json(['message' => 'Gagal memperbarui WiFi ONT.'], 500);
    }

    private function authorizePartnerCustomer(Request $request, Customer $customer): void
    {
        $partner = $request->user();

        abort_if((int) $customer->area_id !== (int) $partner->area_id, 403, 'Unauthorized');
    }

    private function buildCustomerListPayload(Collection $customers): Collection
    {
        $serials = $customers
            ->map(fn (Customer $customer) => $this->normalizeSerial($customer->ont_sn))
            ->filter()
            ->unique()
            ->values();

        $ontBySerial = Ont::query()
            ->whereIn('serial_number', $serials->all())
            ->get()
            ->keyBy(fn (Ont $ont) => $this->normalizeSerial($ont->serial_number));

        $acsBySerial = collect();
        if ($serials->isNotEmpty()) {
            try {
                $devices = app(AcsService::class)->getDevices(500, 0, "", \App\Services\AcsService::PROJECTION_SUMMARY);
                $acsBySerial = collect($devices)
                    ->map(fn (array $device) => app(AcsService::class)->parseDevice($device))
                    ->filter(function (array $device) use ($serials) {
                        return $serials->contains($this->normalizeSerial($device['serial_number'] ?? $device['serial'] ?? null));
                    })
                    ->keyBy(fn (array $device) => $this->normalizeSerial($device['serial_number'] ?? $device['serial'] ?? null));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $customers->map(function (Customer $customer) use ($ontBySerial, $acsBySerial) {
            $serial = $this->normalizeSerial($customer->ont_sn);
            $ontModel = $serial ? $ontBySerial->get($serial) : $customer->ont;
            $acs = $serial ? $acsBySerial->get($serial) : null;

            $ontPayload = null;
            if ($ontModel || $acs) {
                // $acs can be null when serial not found in ACS — use empty array as safe fallback
                $a = $acs ?? [];
                $ontPayload = [
                    'status'        => ($a['status'] ?? null) ?: $ontModel?->status,
                    'serial_number' => ($a['serial_number'] ?? null) ?: ($ontModel?->serial_number ?? $customer->ont_sn),
                    'wan_ip'        => ($a['wan_ip'] ?? null) ?: ($customer->remote_ip ?: null),
                    'ssid'          => ($a['ssid'] ?? null) ?: null,
                    'signal'        => $a['rx_power'] ?? $ontModel?->rx_power ?? null,
                    'uptime'        => ($a['uptime'] ?? null) ?: null,
                    'model'         => (isset($a['model']) && $a['model'] !== 'Unknown' ? $a['model'] : null) ?? $ontModel?->model ?? null,
                ];
            }

            return [
                'id' => $customer->id,
                'partner_id' => $customer->partner_id,
                'area_id' => $customer->area_id,
                'odp_id' => $customer->odp_id,
                'odp_port' => $customer->odp_port,
                'package_id' => $customer->package_id,
                'name' => $customer->name,
                'pppoe_user' => $customer->pppoe_user,
                'package_price' => $customer->package_price,
                'remote_ip' => $customer->remote_ip,
                'local_address' => $customer->local_address,
                'ont_sn' => $customer->ont_sn,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'status' => $customer->status,
                'last_login_at' => $customer->last_login_at,
                'error_message' => $customer->error_message,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
                'package' => $customer->package ? [
                    'id' => $customer->package->id,
                    'name' => $customer->package->name,
                ] : null,
                'area' => $customer->area ? [
                    'id' => $customer->area->id,
                    'name' => $customer->area->name,
                ] : null,
                'ont' => $ontPayload,
            ];
        })->values();
    }

    private function resolveOntData(Customer $customer): ?array
    {
        // Ont model doesn't have wan_ip/ssid/uptime — use customer->remote_ip as WAN IP fallback
        $fallback = [
            'status'        => $customer->ont?->status ?? null,
            'serial_number' => $customer->ont?->serial_number ?? $customer->ont_sn,
            'wan_ip'        => $customer->remote_ip ?: null,
            'ssid'          => null,
            'signal'        => $customer->ont?->rx_power ?? null,
            'uptime'        => null,
            'model'         => null,
            'cable_distance' => $customer->ont?->distance ?? null,
        ];

        // Use ont_sn from customer field OR from linked Ont model
        $effectiveSn = $customer->ont_sn ?? $customer->ont?->serial_number;
        if (!$effectiveSn) {
            return $fallback;
        }

        try {
            $acs = app(AcsService::class);
            $device = $this->findOntDevice($acs, $effectiveSn);
            if ($device) {
                $parsed = $acs->parseDevice($device);

                // Trigger ACS refresh so next request gets fresh data
                if (isset($device['_id'])) {
                    try { $acs->refresh($device['_id']); } catch (\Throwable) {}
                }

                // Use ?: (not ??) so empty strings fall back properly
                return [
                    'status'        => $parsed['status'] ?: $fallback['status'],
                    'serial_number' => $parsed['serial_number'] ?: ($parsed['serial'] ?: $customer->ont_sn),
                    'wan_ip'        => $parsed['wan_ip'] ?: $fallback['wan_ip'],
                    'ssid'          => $parsed['ssid'] ?: $fallback['ssid'],
                    'signal'        => $parsed['rx_power'] ?? $fallback['signal'],
                    'uptime'        => $parsed['uptime'] ?: $fallback['uptime'],
                    'model'         => ($parsed['model'] !== 'Unknown' ? $parsed['model'] : null) ?: $fallback['model'],
                    'cable_distance' => $customer->ont?->distance ?? null,
                ];
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $fallback;
    }

    private function findOntDevice(AcsService $acs, string $ontSn): ?array
    {
        $serial = trim($ontSn);
        if ($serial === '') {
            return null;
        }

        $devices = $acs->getDevices(200, 0, $serial);
        if (empty($devices)) {
            return null;
        }

        $normalized = strtolower($serial);

        foreach ($devices as $device) {
            $candidate = strtolower((string) data_get($device, '_deviceId._SerialNumber', ''));
            $id = strtolower((string) ($device['_id'] ?? ''));

            if ($candidate === $normalized || str_contains($id, $normalized)) {
                return $device;
            }
        }

        return $devices[0];
    }

    private function normalizeSerial(?string $serial): ?string
    {
        $normalized = strtoupper(trim((string) $serial));

        return $normalized !== '' ? $normalized : null;
    }
}
