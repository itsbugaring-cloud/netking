<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ipam\IpamAuditLog;
use App\Models\Ipam\IpamOlt;
use App\Models\Ipam\IpamRouter;
use App\Models\Ipam\IpamSubnet;
use App\Models\Setting;
use App\Services\Ipam\BookmarkParserService;
use App\Services\Ipam\IpamAuditService;
use App\Services\Ipam\MikroTikScannerService;
use App\Services\Ipam\SubnetUtilizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IpamController extends Controller
{
    public function __construct(
        private MikroTikScannerService $scanner,
        private BookmarkParserService $bookmarkParser,
        private SubnetUtilizationService $subnetService,
    ) {}

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $totalRouters = IpamRouter::count();
        $connectedCount = IpamRouter::where('connection_status', 'connected')->count();
        $errorCount = IpamRouter::where('connection_status', 'error')->count();
        $totalSubnets = IpamSubnet::count();

        return view('admin.ipam.dashboard', compact(
            'totalRouters',
            'connectedCount',
            'errorCount',
            'totalSubnets',
        ));
    }

    // ─── Router Explorer ─────────────────────────────────────────────────────

    public function routers(Request $request): View
    {
        $query = IpamRouter::with('mappedOlt');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                  ->orWhere('wireguard_ip', 'like', "%{$search}%");
            });
        }

        $routers = $query->orderBy('device_name')->paginate(25);

        return view('admin.ipam.routers.index', compact('routers'));
    }

    public function storeRouter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_name' => 'required|string|max:255',
            'wireguard_ip' => 'required|ipv4|unique:ipam_routers,wireguard_ip',
            'auth_username' => 'nullable|string|max:255',
            'auth_password' => 'nullable|string|max:255',
        ]);

        $router = IpamRouter::create($validated);

        IpamAuditService::log('create', 'router', $router->id, "Created router: {$router->device_name} ({$router->wireguard_ip})");

        return back()->with('success', "Router {$router->device_name} berhasil ditambahkan.");
    }

    public function destroyRouter(IpamRouter $router): RedirectResponse
    {
        $name = $router->device_name;
        $id = $router->id;

        $router->delete();

        IpamAuditService::log('delete', 'router', $id, "Deleted router: {$name}");

        return back()->with('success', "Router {$name} berhasil dihapus.");
    }

    public function routerDetail(IpamRouter $router): View
    {
        $router->load([
            'ipPools',
            'addresses',
            'routes',
            'wireguardInterfaces',
            'wireguardPeers',
            'mappedOlt',
        ]);

        $olts = IpamOlt::orderBy('name')->get();

        return view('admin.ipam.routers.show', compact('router', 'olts'));
    }

    public function scanRouter(IpamRouter $router): RedirectResponse
    {
        if (!$this->scanner->canScan($router)) {
            $cooldown = (int) Setting::get('ipam.scan_cooldown_secs', 20);
            $elapsed = $router->last_scanned_at->diffInSeconds(now());
            $remaining = $cooldown - $elapsed;

            return back()->with('error', "Router {$router->device_name} baru di-scan {$elapsed} detik lalu, tunggu {$remaining}s.");
        }

        $result = $this->scanner->scanRouter($router);

        if ($result['success']) {
            return back()->with('success', "Scan berhasil untuk router {$router->device_name}.");
        }

        return back()->with('error', "Scan gagal untuk router {$router->device_name}: {$result['error']}");
    }

    public function scanAll(): RedirectResponse
    {
        $results = $this->scanner->scanAll();

        $successCount = $results->where('success', true)->count();
        $errorCount = $results->where('success', false)->count();

        return back()->with('success', "Bulk scan selesai: {$successCount} berhasil, {$errorCount} gagal.");
    }

    public function exportCsv(): StreamedResponse
    {
        $routers = IpamRouter::with(['mappedOlt', 'ipPools'])->get();

        return new StreamedResponse(function () use ($routers) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'device_name',
                'wireguard_ip',
                'connection_status',
                'mapped_olt_name',
                'ip_pools',
                'last_scanned_at',
            ]);

            // Data rows
            foreach ($routers as $router) {
                fputcsv($handle, [
                    $router->device_name,
                    $router->wireguard_ip,
                    $router->connection_status,
                    $router->mappedOlt?->name ?? '',
                    $router->ipPools->pluck('pool_name')->implode(', '),
                    $router->last_scanned_at?->toDateTimeString() ?? '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ipam_routers_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function mapOlt(Request $request, IpamRouter $router): RedirectResponse
    {
        $validated = $request->validate([
            'mapped_olt_id' => 'nullable|exists:ipam_olts,id',
        ]);

        $router->update(['mapped_olt_id' => $validated['mapped_olt_id']]);

        $oltName = $validated['mapped_olt_id']
            ? IpamOlt::find($validated['mapped_olt_id'])->name
            : 'none';

        IpamAuditService::log(
            'map',
            'router',
            $router->id,
            "Mapped router {$router->device_name} to OLT: {$oltName}"
        );

        return back()->with('success', "Router {$router->device_name} berhasil di-mapping ke OLT {$oltName}.");
    }

    public function autoMap(): RedirectResponse
    {
        $routers = IpamRouter::with('addresses')->whereNull('mapped_olt_id')->get();
        $olts = IpamOlt::all();
        $mappedCount = 0;

        foreach ($routers as $router) {
            if ($router->addresses->isEmpty()) {
                continue;
            }

            foreach ($olts as $olt) {
                $matched = false;

                foreach ($router->addresses as $address) {
                    // Extract network from address (e.g., "10.0.0.1/24" → check if OLT IP is in range)
                    $addrParts = explode('/', $address->address);
                    if (count($addrParts) !== 2) {
                        continue;
                    }

                    $ip = $addrParts[0];
                    $prefix = (int) $addrParts[1];

                    if ($this->ipInRange($olt->ip_address, $ip, $prefix)) {
                        $router->update(['mapped_olt_id' => $olt->id]);

                        IpamAuditService::log(
                            'auto_map',
                            'router',
                            $router->id,
                            "Auto-mapped router {$router->device_name} to OLT {$olt->name} (matched via {$address->address})"
                        );

                        $mappedCount++;
                        $matched = true;
                        break;
                    }
                }

                if ($matched) {
                    break;
                }
            }
        }

        return back()->with('success', "Auto-mapping selesai: {$mappedCount} router berhasil di-mapping.");
    }

    /**
     * Check if an IP address falls within a given network range.
     */
    private function ipInRange(string $ip, string $networkIp, int $prefix): bool
    {
        $ipLong = ip2long($ip);
        $networkLong = ip2long($networkIp);

        if ($ipLong === false || $networkLong === false) {
            return false;
        }

        $mask = -1 << (32 - $prefix);
        $networkStart = $networkLong & $mask;

        return ($ipLong & $mask) === $networkStart;
    }

    // ─── OLT Management (Task 5.3) ───────────────────────────────────────────

    public function olts(Request $request): View
    {
        $query = IpamOlt::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $olts = $query->get();

        return view('admin.ipam.olts.index', compact('olts'));
    }

    public function storeOlt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ipv4|unique:ipam_olts,ip_address',
        ]);

        $olt = IpamOlt::create($validated);

        IpamAuditService::log('create', 'olt', $olt->id, "Created OLT: {$olt->name} ({$olt->ip_address})");

        return back()->with('success', "OLT {$olt->name} berhasil ditambahkan.");
    }

    public function updateOlt(Request $request, IpamOlt $olt): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => "required|ipv4|unique:ipam_olts,ip_address,{$olt->id}",
        ]);

        $olt->update($validated);

        IpamAuditService::log('update', 'olt', $olt->id, "Updated OLT: {$olt->name} ({$olt->ip_address})");

        return back()->with('success', "OLT {$olt->name} berhasil diperbarui.");
    }

    public function destroyOlt(IpamOlt $olt): RedirectResponse
    {
        $name = $olt->name;
        $id = $olt->id;

        $olt->delete();

        IpamAuditService::log('delete', 'olt', $id, "Deleted OLT: {$name}");

        return back()->with('success', "OLT {$name} berhasil dihapus.");
    }

    public function bulkDestroyOlt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:ipam_olts,id'
        ]);
        
        $count = count($validated['ids']);
        IpamOlt::whereIn('id', $validated['ids'])->delete();
        
        IpamAuditService::log('delete_bulk', 'olt', null, "Deleted $count OLTs");
        
        return back()->with('success', "Berhasil menghapus $count OLT terpilih.");
    }

    public function importBookmarks(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:html,htm',
        ]);

        $htmlContent = file_get_contents($request->file('file')->getRealPath());
        $entries = $this->bookmarkParser->parse($htmlContent);

        if ($entries->isEmpty()) {
            return back()->with('error', 'Tidak ada OLT yang ditemukan dalam file bookmark.');
        }

        $actor = auth()->user()?->name ?? 'system';
        $result = $this->bookmarkParser->importToDatabase($entries, $actor);

        $message = "Import selesai: {$result['created']} OLT ditambahkan, {$result['skipped']} duplikat dilewati.";

        return back()->with('success', $message);
    }

    // ─── Subnet Management (Task 5.4) ────────────────────────────────────────

    public function subnets(Request $request): View
    {
        $subnets = $this->subnetService->calculateAll();

        return view('admin.ipam.subnets.index', compact('subnets'));
    }

    public function storeSubnet(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'network_address' => 'required|ipv4|unique:ipam_subnets,network_address',
            'prefix_length' => 'required|integer|min:1|max:32',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'vlan_id' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        $subnet = IpamSubnet::create($validated);

        IpamAuditService::log(
            'create',
            'subnet',
            $subnet->id,
            "Created subnet: {$subnet->network_address}/{$subnet->prefix_length}"
        );

        return back()->with('success', "Subnet {$subnet->network_address}/{$subnet->prefix_length} berhasil ditambahkan.");
    }

    public function updateSubnet(Request $request, IpamSubnet $subnet): RedirectResponse
    {
        $validated = $request->validate([
            'network_address' => "required|ipv4|unique:ipam_subnets,network_address,{$subnet->id}",
            'prefix_length' => 'required|integer|min:1|max:32',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'vlan_id' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        $subnet->update($validated);

        IpamAuditService::log(
            'update',
            'subnet',
            $subnet->id,
            "Updated subnet: {$subnet->network_address}/{$subnet->prefix_length}"
        );

        return back()->with('success', "Subnet {$subnet->network_address}/{$subnet->prefix_length} berhasil diperbarui.");
    }

    public function destroySubnet(IpamSubnet $subnet): RedirectResponse
    {
        $label = "{$subnet->network_address}/{$subnet->prefix_length}";
        $id = $subnet->id;

        $subnet->delete();

        IpamAuditService::log('delete', 'subnet', $id, "Deleted subnet: {$label}");

        return back()->with('success', "Subnet {$label} berhasil dihapus.");
    }

    public function subnetUtilization(): JsonResponse
    {
        $data = $this->subnetService->calculateAll();

        return response()->json($data);
    }

    public function subnetSuggestions(Request $request): JsonResponse
    {
        $subnetId = $request->input('subnet_id');

        if (!$subnetId) {
            return response()->json(['error' => 'Parameter subnet_id diperlukan.'], 422);
        }

        $subnet = IpamSubnet::find($subnetId);

        if (!$subnet) {
            return response()->json(['error' => 'Subnet tidak ditemukan.'], 404);
        }

        $availableSpace = $this->subnetService->findAvailableSpace($subnet);

        return response()->json($availableSpace);
    }

    // ─── Audit Log & Settings ────────────────────────────────────────────────

    public function auditLog(Request $request): View
    {
        $logs = IpamAuditLog::orderBy('created_at', 'desc')->paginate(50);

        return view('admin.ipam.audit-log', compact('logs'));
    }

    public function settings(): View
    {
        $settings = [
            'ipam.mikrotik_username' => Setting::get('ipam.mikrotik_username', ''),
            'ipam.mikrotik_password' => '', // Never expose password to view
            'ipam.use_https' => Setting::get('ipam.use_https', false),
            'ipam.allow_insecure_tls' => Setting::get('ipam.allow_insecure_tls', true),
            'ipam.request_timeout_secs' => Setting::get('ipam.request_timeout_secs', 20),
            'ipam.max_scan_concurrency' => Setting::get('ipam.max_scan_concurrency', 8),
            'ipam.scan_cooldown_secs' => Setting::get('ipam.scan_cooldown_secs', 20),
        ];

        return view('admin.ipam.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ipam_mikrotik_username' => 'nullable|string|max:255',
            'ipam_mikrotik_password' => 'nullable|string|max:255',
            'ipam_use_https' => 'nullable|boolean',
            'ipam_allow_insecure_tls' => 'nullable|boolean',
            'ipam_request_timeout_secs' => 'required|integer|min:5|max:120',
            'ipam_max_scan_concurrency' => 'required|integer|min:1|max:50',
            'ipam_scan_cooldown_secs' => 'required|integer|min:5|max:300',
        ]);

        Setting::set('ipam.mikrotik_username', $validated['ipam_mikrotik_username'] ?? '', 'ipam');

        if (!empty($validated['ipam_mikrotik_password'])) {
            Setting::set('ipam.mikrotik_password', encrypt($validated['ipam_mikrotik_password']), 'ipam');
        }

        Setting::set('ipam.use_https', $request->boolean('ipam_use_https') ? '1' : '0', 'ipam');
        Setting::set('ipam.allow_insecure_tls', $request->boolean('ipam_allow_insecure_tls') ? '1' : '0', 'ipam');
        Setting::set('ipam.request_timeout_secs', $validated['ipam_request_timeout_secs'], 'ipam');
        Setting::set('ipam.max_scan_concurrency', $validated['ipam_max_scan_concurrency'], 'ipam');
        Setting::set('ipam.scan_cooldown_secs', $validated['ipam_scan_cooldown_secs'], 'ipam');

        // Audit log without password value
        $changes = collect($validated)
            ->except('ipam_mikrotik_password')
            ->map(fn($v, $k) => str_replace('ipam_', 'ipam.', $k) . '=' . ($v ?? ''))
            ->implode(', ');

        $passwordChanged = !empty($validated['ipam_mikrotik_password']) ? ' (password updated)' : '';

        IpamAuditService::log(
            'update',
            'settings',
            null,
            "Updated IPAM settings: {$changes}{$passwordChanged}"
        );

        return back()->with('success', 'Pengaturan IPAM berhasil disimpan.');
    }
}
