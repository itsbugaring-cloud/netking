<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CustomersExport;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\InvUnit;
use App\Models\Odp;
use App\Models\User;
use App\Jobs\CreateMikrotikSecretJob;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /** Scope query to partner's area if logged in as partner */
    private function scopedQuery()
    {
        $user  = auth()->user();
        $query = Customer::with(['partner', 'area', 'package']);

        if ($user->role === 'partner') {
            $query->where('partner_id', $user->id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = $this->scopedQuery();
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 100, 200], true)) {
            $perPage = 25;
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('pppoe_user', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Admin area filter
        if ($request->filled('area_id') && $user->role === 'admin') {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('partner_id') && $user->role === 'admin') {
            $query->where('partner_id', $request->partner_id);
        }

        // CSV Export
        if ($request->get('export') === 'csv') {
            // Ensure relationships are eager-loaded to prevent N+1 and null crashes
            $rows = $query->with(['partner:id,name', 'area:id,name', 'package:id,name'])->latest()->get();
            $callback = function () use ($rows) {
                $f = fopen('php://output', 'w');
                fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
                fputcsv($f, ['Nama', 'PPPoE User', 'Mitra', 'Area', 'Paket', 'Status', 'No HP', 'Alamat', 'Mulai Tagihan', 'Dibuat']);
                foreach ($rows as $c) {
                    fputcsv($f, [
                        $c->name,
                        $c->pppoe_user,
                        $c->partner?->name ?? '-',   // nullsafe: partner_id can be null
                        $c->area?->name ?? '-',
                        $c->package?->name ?? '-',
                        $c->status,
                        $c->phone,
                        $c->address,
                        optional($c->billing_start_date)->format('Y-m-d') ?? '',
                        $c->created_at->format('Y-m-d'),
                    ]);
                }
                fclose($f);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=customers_' . date('Ymd') . '.csv',
            ]);
        }

        $customers = $query->select([
            'id', 'name', 'pppoe_user', 'phone', 'address', 'status',
            'area_id', 'partner_id', 'package_id', 'package_price',
            'billing_start_date', 'billing_due_day', 'is_free', 'customer_code', 'created_at', 'remote_ip',
        ])->latest()->paginate($perPage)->withQueryString();

        // Pass areas for admin filter dropdown (not needed for partner)
        $areas = ($user->role === 'admin')
            ? Area::orderBy('name')->get()
            : collect();

        return view('admin.customers.index', compact('customers', 'areas', 'perPage'));
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->role === 'partner') {
            $areas    = Area::where('id', $user->area_id)->get();
            // Partner hanya lihat ODP area-nya sendiri
            $odps     = Odp::where('area_id', $user->area_id)->orderBy('name')->get();
            $packages = \App\Models\Package::where('is_active', true)
                ->where('area_id', $user->area_id)
                ->orderBy('name')
                ->get();
        } else {
            $areas    = Area::orderBy('name')->get();
            // Admin: ODP dimuat semua di awal, akan di-filter AJAX saat area dipilih
            $odps     = collect();
            $packages = collect();
        }

        return view('admin.customers.create', compact('areas', 'odps', 'packages'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Force area & partner for partners
        if ($user->role === 'partner') {
            $request->merge([
                'area_id'    => $user->area_id,
                'partner_id' => $user->id,
            ]);
        }

        $areaId = (int) $request->input('area_id');

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'pppoe_user'      => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'pppoe_user')->where(fn($q) => $q->where('area_id', $areaId)),
            ],
            'pppoe_pass'      => 'required|string|max:255',
            'area_id'         => 'required|exists:areas,id',
            'partner_id'      => 'nullable|exists:users,id',
            'package_id'      => [
                'nullable',
                Rule::exists('packages', 'id')->where(fn($q) => $q->where('area_id', $areaId)),
            ],
            'local_address'   => 'nullable|string|max:45',
            'ont_sn'          => 'nullable|string|max:255',
            'package_price'   => 'required|numeric|min:0',
            'billing_start_date' => 'required|date',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:500',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'odp_id'          => 'nullable|exists:odps,id',
            'odp_port'        => 'nullable|integer|min:1|max:128',
        ]);

        $customer = Customer::create([
            ...$validated,
            'is_free'         => $request->boolean('is_free'),
            'portal_password' => Hash::make(Str::random(12)),
            'status'          => 'provisioning',
        ]);

        CreateMikrotikSecretJob::dispatch($customer);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', "Customer {$customer->name} created and queued for provisioning.");
    }

    public function show(Customer $customer)
    {
        $this->authorizeArea($customer);
        $customer->load(['partner', 'area', 'package', 'odp', 'ont', 'devices']);
        $ontAssignmentHistories = $customer->ontAssignmentHistories()
            ->with(['ont', 'previousCustomer', 'invUnit', 'creator'])
            ->latest()
            ->limit(8)
            ->get();

        $invUnitMatch = null;
        if (!empty($customer->ont_sn)) {
            $normalizedSn = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $customer->ont_sn));
            $invUnitMatch = InvUnit::query()
                ->with('lokasi')
                ->whereRaw('REPLACE(UPPER(serial_number), "-", "") = ?', [$normalizedSn])
                ->first();
        }

        return view('admin.customers.show', compact('customer', 'ontAssignmentHistories', 'invUnitMatch'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeArea($customer);
        $user = auth()->user();

        if ($user->role === 'partner') {
            $areas    = Area::where('id', $user->area_id)->get();
            $odps     = Odp::where('area_id', $customer->area_id)->orderBy('name')->get();
            $packages = \App\Models\Package::where('is_active', true)
                ->where('area_id', $customer->area_id)
                ->orderBy('name')
                ->get();
        } else {
            $areas    = Area::orderBy('name')->get();
            // Admin: ODP di-filter per area customer, AJAX bisa reload jika ganti area
            $odps     = Odp::where('area_id', $customer->area_id)->orderBy('name')->get();
            $packages = \App\Models\Package::where('is_active', true)
                ->where('area_id', $customer->area_id)
                ->orderBy('name')
                ->get();
        }
        return view('admin.customers.edit', compact('customer', 'areas', 'packages', 'odps'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeArea($customer);

        $user = auth()->user();
        if ($user->role === 'partner') {
            $request->merge([
                'area_id'    => $user->area_id,
                'partner_id' => $user->id,
            ]);
        }

        $areaId = (int) $request->input('area_id');

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'pppoe_user'      => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'pppoe_user')->ignore($customer->id)->where(fn($q) => $q->where('area_id', $areaId)),
            ],
            'username'        => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('customers', 'username')->ignore($customer->id),
            ],
            'area_id'         => 'required|exists:areas,id',
            'package_id'      => [
                'nullable',
                Rule::exists('packages', 'id')->where(fn($q) => $q->where('area_id', $areaId)),
            ],
            'package_price'   => 'required|numeric|min:0',
            'billing_start_date' => 'required|date',
            'billing_due_day'    => 'nullable|integer|min:1|max:31',
            'pppoe_pass'      => 'nullable|string|max:255',
            'ont_sn'          => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:500',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'odp_id'          => 'nullable|exists:odps,id',
            'odp_port'        => 'nullable|integer|min:1|max:128',
            'portal_password' => 'nullable|string|min:6',
        ]);

        if (!$request->filled('pppoe_pass')) {
            unset($validated['pppoe_pass']);
        }

        if ($request->filled('portal_password')) {
            $validated['portal_password'] = Hash::make($validated['portal_password']);
        } else {
            unset($validated['portal_password']);
        }

        $validated['is_free'] = $request->boolean('is_free');

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeArea($customer);

        // [SECURITY PATCH] MikroTik cleanup runs BEFORE DB delete.
        // If MikroTik cleanup fails, the customer record is preserved and
        // an error is returned — preventing orphaned PPPoE secrets on the router.
        $customer->loadMissing('area');
        try {
            $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
            if ($mikrotik->isConnected()) {
                $mikrotik->disconnectSession($customer->pppoe_user);
                $mikrotik->deleteSecret($customer->pppoe_user);
            }
        } catch (\Exception $e) {
            Log::warning("MikroTik cleanup failed during delete, aborting DB delete to prevent orphan secret", [
                'customer_id' => $customer->id,
                'pppoe_user'  => $customer->pppoe_user,
                'error'       => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus PPPoE di MikroTik (' . $e->getMessage() . '). Customer tidak dihapus. Coba lagi atau periksa koneksi MikroTik.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    public function toggleStatus(Customer $customer)
    {
        $this->authorizeArea($customer);

        $newStatus = $customer->status === 'active' ? 'suspended' : 'active';
        $customer->update(['status' => $newStatus]);

        try {
            $customer->loadMissing('area');
            $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
            if ($mikrotik->isConnected()) {
                if ($newStatus === 'suspended') {
                    $mikrotik->toggleSecret($customer->pppoe_user, false);
                    $mikrotik->disconnectSession($customer->pppoe_user);
                } else {
                    $mikrotik->toggleSecret($customer->pppoe_user, true);
                }
            }
        } catch (\Exception $e) {
            Log::warning("MikroTik toggle failed: " . $e->getMessage());
        }

        return back()->with('success', 'Customer status updated to ' . $newStatus);
    }

    public function isolir(Customer $customer)
    {
        $this->authorizeArea($customer);
        $customer->loadMissing('area');

        try {
            $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
            if (!$mikrotik->isConnected()) {
                return back()->with('error', 'MikroTik tidak bisa dihubungi.');
            }

            // Ambil IP dari sesi aktif PPPoE
            $ip = null;
            $sessions = $mikrotik->getActiveSessions($customer->pppoe_user);
            if (($sessions['success'] ?? false) && !empty($sessions['data'])) {
                foreach ($sessions['data'] as $s) {
                    if (strtolower($s['name'] ?? '') === strtolower($customer->pppoe_user)) {
                        $ip = $s['address'] ?? null;
                        break;
                    }
                }
            }
            if (!$ip) $ip = $customer->remote_ip ?? null;

            if (!$ip) {
                return back()->with('error', "Pelanggan {$customer->name} tidak ada sesi aktif / IP tidak ditemukan. Pastikan sedang online.");
            }

            $check = $mikrotik->checkAddressList($ip, 'isolir');
            if (($check['found'] ?? false) === true) {
                return back()->with('warning', "IP {$ip} sudah ada di list isolir.");
            }

            $result = $mikrotik->addToAddressList($ip, 'isolir', null, "isolir:{$customer->pppoe_user}");
            if (!($result['success'] ?? false)) {
                return back()->with('error', 'Gagal isolir: ' . ($result['error'] ?? 'unknown'));
            }

            $customer->update(['status' => 'suspended']);
            Log::info("Isolir customer {$customer->pppoe_user} IP {$ip}");

            return back()->with('success', "✅ {$customer->name} berhasil diisolir. IP {$ip} masuk list isolir.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function lepasIsolir(Customer $customer)
    {
        $this->authorizeArea($customer);
        $customer->loadMissing('area');

        try {
            $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
            if (!$mikrotik->isConnected()) {
                return back()->with('error', 'MikroTik tidak bisa dihubungi.');
            }

            $listResult = $mikrotik->getAddressList('isolir');
            if (!($listResult['success'] ?? false)) {
                return back()->with('error', 'Gagal ambil address list: ' . ($listResult['error'] ?? ''));
            }

            $removed = 0;
            foreach (($listResult['data'] ?? []) as $entry) {
                $entryComment = $entry['comment'] ?? '';
                $entryId = $entry['.id'] ?? null;
                if (!$entryId) continue;

                if (
                    str_contains($entryComment, $customer->pppoe_user) ||
                    ($customer->remote_ip && ($entry['address'] ?? '') === $customer->remote_ip)
                ) {
                    $mikrotik->removeFromAddressList($entryId);
                    $removed++;
                }
            }

            $customer->update(['status' => 'active']);
            Log::info("Lepas isolir customer {$customer->pppoe_user}, removed {$removed} entries");

            return back()->with('success', "✅ {$customer->name} berhasil dilepas dari isolir. ({$removed} entry dihapus)");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function enablePppoe(Customer $customer)
    {

        $this->authorizeArea($customer);

        try {
            $customer->loadMissing('area');
            $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
            if (!$mikrotik->isConnected()) {
                return back()->with('error', 'MikroTik area ' . $customer->area->name . ' tidak dapat dihubungi. Coba lagi nanti.');
            }
            $mikrotik->toggleSecret($customer->pppoe_user, true);
            $customer->update([
                'pppoe_pending_enable' => false,
                'error_message'        => null,
            ]);
            \App\Models\ActivityLog::log('activate',
                "PPPoE diaktifkan manual untuk {$customer->name} ({$customer->pppoe_user})",
                $customer
            );
            return back()->with('success', "PPPoE {$customer->pppoe_user} berhasil diaktifkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal aktifkan PPPoE: ' . $e->getMessage());
        }
    }

    public function retryProvision(Customer $customer)
    {
        $this->authorizeArea($customer);

        if ($customer->status !== 'failed') {
            return back()->with('error', 'Only failed customers can be retried');
        }

        $customer->update(['status' => 'provisioning', 'error_message' => null]);
        CreateMikrotikSecretJob::dispatch($customer);

        return back()->with('success', 'Provisioning retry queued');
    }

    public function resetPortalPassword(Request $request, Customer $customer)
    {
        $this->authorizeArea($customer);

        $validated = $request->validate([
            'portal_password' => 'nullable|string|min:8|confirmed',
        ]);

        $plainPassword = trim((string) ($validated['portal_password'] ?? ''));
        if ($plainPassword === '') {
            $plainPassword = Str::random(10);
        }

        $customer->update([
            'portal_password' => Hash::make($plainPassword),
        ]);

        $customer->tokens()->delete();

        \App\Models\ActivityLog::log(
            'portal-password-reset',
            "Password portal direset untuk {$customer->name} ({$customer->pppoe_user})",
            $customer,
            ['reset_by' => auth()->id()]
        );

        return back()->with('success', "Password portal berhasil direset. Password baru: {$plainPassword}");
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No customers selected']);
        }

        // Partners can only delete within their own area
        $query = Customer::whereIn('id', $ids);
        if ($user->role === 'partner') {
            $query->where('partner_id', $user->id);
        }

        $count = $query->delete();

        return response()->json([
            'success' => true,
            'message' => $count . ' customer(s) deleted successfully',
        ]);
    }

    // ─── Live Connection Topology (AJAX) ─────────────────────────────────

    public function topology(Customer $customer)
    {
        $this->authorizeArea($customer);
        $customer->load(['odp', 'ont.olt']);

        $result = [
            'customer' => $customer->name,
            'odp'      => $customer->odp ? [
                'name'  => $customer->odp->name,
                'ports' => ($customer->odp->capacity_used ?? 0) . '/' . ($customer->odp->capacity ?? 0),
            ] : null,
            'ont'      => null,
            'olt'      => null,
            'acs'      => null,
        ];

        // Try to get live data from GenieACS
        if ($customer->ont_sn) {
            try {
                // [REMOVED] ACS/GenieACS feature removed — skip live ACS data
            } catch (\Exception $e) {
                Log::warning('Topology ACS fetch failed: ' . $e->getMessage());
            }
        }

        // ONT from database
        if ($customer->ont) {
            $result['ont'] = [
                'serial'    => $customer->ont->serial_number,
                'rx_power'  => $customer->ont->rx_power,
                'tx_power'  => $customer->ont->tx_power,
                'status'    => $customer->ont->status,
                'quality'   => $customer->ont->signal_quality,
            ];

            // OLT from ONT relationship
            if ($customer->ont->olt) {
                $olt = $customer->ont->olt;
                $result['olt'] = [
                    'name'   => $olt->name,
                    'brand'  => $olt->brand,
                    'model'  => $olt->model,
                    'ip'     => $olt->ip_address,
                    'status' => $olt->status,
                    'uptime' => $olt->online_count . '/' . ($olt->online_count + $olt->offline_count) . ' ONT online',
                ];
            }
        }

        return response()->json($result);
    }

    // ─── Equipment Inventory CRUD ────────────────────────────────────────

    public function storeDevice(Request $request, Customer $customer)
    {
        $this->authorizeArea($customer);

        $validated = $request->validate([
            'type'          => 'required|in:ont,router,cable,adapter,splitter,other',
            'brand'         => 'nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'status'        => 'required|in:active,returned,damaged,lost',
            'assigned_at'   => 'nullable|date',
            'returned_at'   => 'nullable|date',
            'notes'         => 'nullable|string|max:500',
        ]);

        $customer->devices()->create($validated);

        return back()->with('success', 'Device added successfully');
    }

    public function updateDevice(Request $request, Customer $customer, CustomerDevice $device)
    {
        $this->authorizeArea($customer);

        $validated = $request->validate([
            'type'          => 'required|in:ont,router,cable,adapter,splitter,other',
            'brand'         => 'nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'status'        => 'required|in:active,returned,damaged,lost',
            'assigned_at'   => 'nullable|date',
            'returned_at'   => 'nullable|date',
            'notes'         => 'nullable|string|max:500',
        ]);

        $device->update($validated);

        return back()->with('success', 'Device updated successfully');
    }

    public function destroyDevice(Customer $customer, CustomerDevice $device)
    {
        $this->authorizeArea($customer);
        $device->delete();
        return back()->with('success', 'Device removed successfully');
    }

    /** Ensure partner can only access customers that belong to them */
    private function authorizeArea(Customer $customer): void
    {
        $user = auth()->user();
        if ($user->role === 'partner' && $customer->partner_id !== $user->id) {
            abort(403, 'Access denied: customer is not assigned to your account.');
        }
    }

    // ─── Import Customers ────────────────────────────────────────────────────

    /**
     * Show the customer import form (admin only)
     */
    public function import()
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $areas = Area::orderBy('name')->get();

        return view('admin.customers.import', compact('areas'));
    }

    /**
     * Process the uploaded CSV file and bulk-create customers (admin only)
     * Supports CSV only (.csv). Excel files require phpspreadsheet package.
     */
    public function importProcess(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $request->validate([
            'file'       => 'required|file|mimes:csv,txt|max:5120',
            'area_id'    => 'required|exists:areas,id',
            'partner_id' => 'nullable|exists:users,id',
        ]);

        $file     = $request->file('file');
        $areaId   = $request->area_id;
        $partnerId = $request->partner_id ?: null;

        $handle  = fopen($file->getRealPath(), 'r');
        $header  = null;
        $created = 0;
        $skipped = 0;
        $errors  = [];

        while (($row = fgetcsv($handle)) !== false) {
            // First row = header
            if ($header === null) {
                $header = array_map('trim', $row);
                continue;
            }

            if (count($row) < 2) continue;

            $data = array_combine($header, array_pad($row, count($header), null));

            $name      = trim($data['name']      ?? '');
            $pppoeUser = trim($data['pppoe_user'] ?? '');
            $pppoePass = trim($data['pppoe_pass'] ?? $data['password'] ?? '');

            if (!$name || !$pppoeUser) {
                $skipped++;
                continue;
            }

            if (Customer::where('pppoe_user', $pppoeUser)->exists()) {
                $errors[] = "PPPoE '{$pppoeUser}' sudah ada — dilewati.";
                $skipped++;
                continue;
            }

            try {
                $billingStartRaw = trim((string) ($data['billing_start_date'] ?? $data['tgl_aktif'] ?? ''));
                $billingStart = now()->toDateString();
                if ($billingStartRaw !== '') {
                    try {
                        $billingStart = Carbon::parse($billingStartRaw)->toDateString();
                    } catch (\Throwable $ignored) {
                        $billingStart = now()->toDateString();
                    }
                }

                Customer::create([
                    'name'       => $name,
                    'pppoe_user' => $pppoeUser,
                    'pppoe_pass' => $pppoePass ?: 'changeme',
                    'phone'      => trim($data['phone']   ?? ''),
                    'address'    => trim($data['address'] ?? ''),
                    'area_id'    => $areaId,
                    'partner_id' => $partnerId,
                    'package_id' => isset($data['package_id']) && is_numeric($data['package_id']) ? (int) $data['package_id'] : null,
                    'billing_start_date' => $billingStart,
                    'status'     => 'active',
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Baris '{$name}': " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        $msg = "Impor selesai: {$created} pelanggan berhasil dibuat";
        if ($skipped) $msg .= ", {$skipped} dilewati";

        if (!empty($errors)) {
            session()->flash('import_errors', array_slice($errors, 0, 20));
        }

        return redirect()->route('admin.customers.index')
            ->with('success', $msg . '.');
    }

    /**
     * Download Excel template for bulk billing_start_date update.
     */
    public function downloadBillingStartTemplate()
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }

        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . 'billing_start_template_' . uniqid('', true) . '.xlsx';
        $this->buildBillingStartTemplateXlsx($tmpPath);

        return response()->download(
            $tmpPath,
            'template_update_tanggal_tagihan.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /**
     * Bulk update billing_start_date for existing customers (admin only).
     * Headers supported:
     * - pppoe_user (required)
     * - billing_start_date OR tgl_aktif OR tanggal_pasang (required per-row)
     * Supported file types: CSV/TXT/XLSX.
     */
    public function importBillingStartDates(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'dry_run' => 'nullable|boolean',
        ]);

        $isDryRun = $request->boolean('dry_run');
        $file = $request->file('file');
        $rows = $this->readTabularRows($file);

        if (empty($rows)) {
            return back()->with('error', 'File kosong atau tidak memiliki baris data.');
        }

        $header = null;
        $updated = 0;
        $unchanged = 0;
        $missing = 0;
        $invalid = 0;
        $ambiguous = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            if ($header === null) {
                $header = array_map(fn($v) => trim((string) $v), $row);
                if (!empty($header[0])) {
                    $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
                }
                continue;
            }

            if (!$header || count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                $skipped++;
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));
            if (!is_array($data)) {
                $invalid++;
                $errors[] = 'Format baris CSV tidak valid.';
                continue;
            }

            $pppoeRaw = trim((string) ($data['pppoe_user'] ?? ''));
            $dateRaw = trim((string) (
                $data['billing_start_date']
                ?? $data['tgl_aktif']
                ?? $data['tanggal_pasang']
                ?? ''
            ));

            if ($pppoeRaw === '') {
                $invalid++;
                $errors[] = 'pppoe_user kosong, baris dilewati.';
                continue;
            }

            if ($dateRaw === '') {
                $invalid++;
                $errors[] = "PPPoE {$pppoeRaw}: tanggal mulai tagihan kosong.";
                continue;
            }

            try {
                $resolvedDate = Carbon::parse($dateRaw)->toDateString();
            } catch (\Throwable $e) {
                $invalid++;
                $errors[] = "PPPoE {$pppoeRaw}: format tanggal '{$dateRaw}' tidak valid.";
                continue;
            }

            $matches = Customer::whereRaw('LOWER(TRIM(pppoe_user)) = ?', [mb_strtolower($pppoeRaw)])
                ->get(['id', 'name', 'pppoe_user', 'billing_start_date']);

            if ($matches->isEmpty()) {
                $missing++;
                $errors[] = "PPPoE {$pppoeRaw}: pelanggan tidak ditemukan.";
                continue;
            }

            if ($matches->count() > 1) {
                $ambiguous++;
                $errors[] = "PPPoE {$pppoeRaw}: ditemukan lebih dari satu pelanggan, perlu update manual.";
                continue;
            }

            /** @var Customer $customer */
            $customer = $matches->first();
            $currentDate = optional($customer->billing_start_date)->toDateString();

            if ($currentDate === $resolvedDate) {
                $unchanged++;
                continue;
            }

            if (!$isDryRun) {
                $customer->update(['billing_start_date' => $resolvedDate]);
            }

            $updated++;
        }

        $mode = $isDryRun ? 'DRY RUN' : 'EKSEKUSI';
        $summary = "{$mode} update tanggal tagihan selesai — "
            . "updated: {$updated}, unchanged: {$unchanged}, missing: {$missing}, invalid: {$invalid}, ambiguous: {$ambiguous}, skipped: {$skipped}.";

        if (!empty($errors)) {
            session()->flash('import_billing_errors', array_slice($errors, 0, 30));
        }

        return back()->with($isDryRun ? 'success' : 'success', $summary);
    }

    /**
     * Read CSV/TXT/XLSX into 2D array rows.
     */
    private function readTabularRows(UploadedFile $file): array
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $path = $file->getRealPath();
        if (!$path) {
            return [];
        }

        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->readCsvRows($path);
        }

        if ($ext === 'xlsx') {
            return $this->readXlsxRows($path);
        }

        return [];
    }

    private function readCsvRows(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (!$handle) {
            return $rows;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File XLSX tidak bisa dibuka.');
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new \RuntimeException('Worksheet pertama pada file XLSX tidak ditemukan.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $shared = simplexml_load_string($sharedXml);
            if ($shared !== false) {
                foreach ($shared->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                        continue;
                    }

                    $text = '';
                    foreach ($si->r as $run) {
                        $text .= (string) ($run->t ?? '');
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
        $zip->close();

        $sheet = simplexml_load_string($sheetXml);
        if ($sheet === false) {
            return [];
        }

        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $sheet->xpath('//x:sheetData/x:row') ?: [];

        $rows = [];
        foreach ($rowNodes as $rowNode) {
            $rowNode->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $cellNodes = $rowNode->xpath('./x:c') ?: [];

            $mapped = [];
            $maxCol = 0;

            foreach ($cellNodes as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $col = 1;
                if ($ref !== '' && preg_match('/^([A-Z]+)\d+$/', $ref, $m)) {
                    $col = $this->xlsxColumnToIndex($m[1]);
                } else {
                    $col = $maxCol + 1;
                }

                $value = '';
                $type = (string) ($cell['t'] ?? '');
                if ($type === 's') {
                    $idx = (int) ($cell->v ?? 0);
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                } else {
                    $value = (string) ($cell->v ?? '');
                }

                $mapped[$col] = trim($value);
                $maxCol = max($maxCol, $col);
            }

            $dense = [];
            for ($i = 1; $i <= $maxCol; $i++) {
                $dense[] = $mapped[$i] ?? '';
            }
            $rows[] = $dense;
        }

        return $rows;
    }

    private function xlsxColumnToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }
        return max(1, $index);
    }

    /**
     * Build minimal XLSX template without external package.
     */
    /**
     * Export all customers to Excel (.xlsx) with styling
     */
    public function exportExcel(Request $request)
    {
        $parts = ['data_pelanggan'];

        if ($request->filled('area_id')) {
            $areaName = Area::whereKey($request->area_id)->value('name');
            if ($areaName) {
                $parts[] = Str::slug($areaName, '-');
            }
        }

        if ($request->filled('status')) {
            $parts[] = Str::slug((string) $request->status, '-');
        }

        if ($request->filled('payment_status')) {
            $paymentLabels = [
                'approved' => 'sudah-bayar',
                'pending' => 'pending',
            ];
            $parts[] = $paymentLabels[$request->payment_status] ?? Str::slug((string) $request->payment_status, '-');
        }

        if (count($parts) === 1) {
            $parts[] = 'semua';
        }

        $filename = implode('_', $parts) . '_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new CustomersExport($request), $filename);
    }

    private function buildBillingStartTemplateXlsx(string $targetPath): void
    {
        $rows = [
            ['pppoe_user', 'billing_start_date', 'catatan'],
            ['NGS-001', '2025-08-05', 'Format YYYY-MM-DD'],
            ['NPL-002', '2025-09-12', 'Tanggal real pemasangan'],
        ];

        $allStrings = [];
        foreach ($rows as $row) {
            foreach ($row as $value) {
                $allStrings[] = (string) $value;
            }
        }
        $uniqueStrings = array_values(array_unique($allStrings));
        $stringIndex = array_flip($uniqueStrings);

        $escape = static fn(string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $sharedStringsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($allStrings) . '" uniqueCount="' . count($uniqueStrings) . '">';
        foreach ($uniqueStrings as $s) {
            $sharedStringsXml .= '<si><t>' . $escape($s) . '</t></si>';
        }
        $sharedStringsXml .= '</sst>';

        $sheetData = '';
        foreach ($rows as $r => $row) {
            $rowNum = $r + 1;
            $sheetData .= '<row r="' . $rowNum . '">';
            foreach ($row as $c => $value) {
                $colLetter = chr(65 + $c); // A, B, C
                $cellRef = $colLetter . $rowNum;
                $idx = $stringIndex[(string) $value];
                $sheetData .= '<c r="' . $cellRef . '" t="s"><v>' . $idx . '</v></c>';
            }
            $sheetData .= '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:C' . count($rows) . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<sheetData>' . $sheetData . '</sheetData>'
            . '</worksheet>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Template" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border/></borders>'
            . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            . '<cellXfs count="1"><xf xfId="0"/></cellXfs>'
            . '</styleSheet>';

        $nowIso = now()->toIso8601String();
        $coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
            . 'xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>NETKING</dc:creator>'
            . '<cp:lastModifiedBy>NETKING</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $escape($nowIso) . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $escape($nowIso) . '</dcterms:modified>'
            . '</cp:coreProperties>';

        $appXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
            . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>NETKING</Application>'
            . '</Properties>';

        $zip = new \ZipArchive();
        if ($zip->open($targetPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file template XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('docProps/core.xml', $coreXml);
        $zip->addFromString('docProps/app.xml', $appXml);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
    }
}
