<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncOltJob;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Olt;
use App\Models\Ont;
use App\Services\OltService;
use Illuminate\Http\Request;

class OltController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $query = Olt::with(['area', 'onts'])->orderBy('name');

        if ($user->role === 'partner') {
            $query->where('area_id', $user->area_id);
        }

        $olts = $query->get();
        return view('admin.olts.index', compact('olts'));
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();
        return view('admin.olts.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'brand'              => 'required|string|max:100',
            'model'              => 'required|string|max:100',
            'ip_address'         => 'required|ip',
            'area_id'            => 'nullable|exists:areas,id',
            'snmp_community'     => 'nullable|string|max:100',
            'snmp_version'       => 'nullable|in:1,2c,3',
            'ssh_user'           => 'nullable|string|max:100',
            'ssh_pass'           => 'nullable|string|max:100',
            'ssh_port'           => 'nullable|integer|min:1|max:65535',
            'telnet_user'        => 'nullable|string|max:100',
            'telnet_pass'        => 'nullable|string|max:100',
            'telnet_port'        => 'nullable|integer|min:1|max:65535',
            'api_url'            => 'nullable|url',
            'api_token'          => 'nullable|string',
            'preferred_protocol' => 'required|in:snmp,ssh,telnet,rest',
            'notes'              => 'nullable|string',
        ]);

        $olt = Olt::create($validated);

        return redirect()->route('admin.olts.show', $olt)
            ->with('success', "OLT {$olt->name} created. Click 'Sync ONTs' to fetch inventory.");
    }

    /**
     * Authorise partner access to a specific OLT (must be in partner's area).
     */
    private function authorizeOlt(Olt $olt): void
    {
        $user = auth()->user();
        if ($user->role === 'partner' && $olt->area_id !== $user->area_id) {
            abort(403, 'Anda tidak memiliki akses ke OLT ini.');
        }
    }

    /**
     * Authorise partner access to a specific ONT (via its OLT area).
     */
    private function authorizeOnt(Ont $ont): void
    {
        $user = auth()->user();
        if ($user->role === 'partner') {
            $oltAreaId = $ont->olt?->area_id;
            if ($oltAreaId !== $user->area_id) {
                abort(403, 'Anda tidak memiliki akses ke ONT ini.');
            }
        }
    }

    public function show(Olt $olt)
    {
        $this->authorizeOlt($olt);
        $olt->load('area');
        $onts = Ont::where('olt_id', $olt->id)
            ->with('customer')
            ->orderByRaw("FIELD(status, 'online', 'offline', 'unknown')")
            ->orderBy('pon_port')
            ->orderBy('olt_port_index')
            ->get();

        // Load customers filtered by OLT's area — only unlinked OR already linked to this OLT's ONTs
        $alreadyLinked = Ont::where('olt_id', $olt->id)->whereNotNull('customer_id')->pluck('customer_id');
        $customersQuery = Customer::select('id', 'name', 'pppoe_user', 'area_id')
            ->whereNotIn('id', $alreadyLinked)  // exclude those already linked in this OLT
            ->orderBy('name');

        // Strict area isolation for area-bound OLTs
        if ($olt->area_id) {
            $customersQuery->where('area_id', $olt->area_id);
        }
        $customers = $customersQuery->get();

        $stats = [
            'total'    => $onts->count(),
            'online'   => $onts->where('status', 'online')->count(),
            'offline'  => $onts->where('status', 'offline')->count(),
            'linked'   => $onts->whereNotNull('customer_id')->count(),
            'unlinked' => $onts->whereNull('customer_id')->count(),
        ];

        return view('admin.olts.show', compact('olt', 'onts', 'stats', 'customers'));
    }

    public function edit(Olt $olt)
    {
        $areas = Area::orderBy('name')->get();
        return view('admin.olts.edit', compact('olt', 'areas'));
    }

    public function update(Request $request, Olt $olt)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'brand'              => 'required|string|max:100',
            'model'              => 'required|string|max:100',
            'ip_address'         => 'required|ip',
            'area_id'            => 'nullable|exists:areas,id',
            'snmp_community'     => 'nullable|string|max:100',
            'snmp_version'       => 'nullable|in:1,2c,3',
            'ssh_user'           => 'nullable|string|max:100',
            'ssh_pass'           => 'nullable|string|max:100',
            'ssh_port'           => 'nullable|integer|min:1|max:65535',
            'telnet_user'        => 'nullable|string|max:100',
            'telnet_pass'        => 'nullable|string|max:100',
            'telnet_port'        => 'nullable|integer|min:1|max:65535',
            'api_url'            => 'nullable|url',
            'api_token'          => 'nullable|string',
            'preferred_protocol' => 'required|in:snmp,ssh,telnet,rest',
            'notes'              => 'nullable|string',
        ]);

        $olt->update($validated);

        return redirect()->route('admin.olts.show', $olt)
            ->with('success', "OLT {$olt->name} updated.");
    }

    public function destroy(Olt $olt)
    {
        $name = $olt->name;
        $olt->delete(); // cascades to onts

        return redirect()->route('admin.olts.index')
            ->with('success', "OLT {$name} and all its ONT records deleted.");
    }

    /**
     * Sync ONTs from OLT device — dispatches background job, returns immediately.
     */
    public function sync(Olt $olt)
    {
        $this->authorizeOlt($olt);
        // If already syncing, don't queue again
        if (in_array($olt->sync_status, ['queued', 'syncing'])) {
            return back()->with('info', "⏳ Sync OLT {$olt->name} sudah berjalan, harap tunggu...");
        }

        $olt->update([
            'sync_status'  => 'queued',
            'sync_message' => 'Sync dijadwalkan, menunggu worker...',
        ]);

        SyncOltJob::dispatch($olt);

        return back()->with(
            'success',
            "✅ Sync OLT {$olt->name} dijadwalkan. Data akan diperbarui dalam beberapa detik."
        );
    }

    /**
     * Sync ONTs LANGSUNG (synchronous) — tanpa queue, tanpa worker.
     * Gunakan ini jika tombol sync biasa tidak bekerja.
     * Akan memerlukan waktu 10–120 detik tergantung jumlah ONT.
     */
    public function syncNow(Olt $olt)
    {
        $this->authorizeOlt($olt);
        // Cegah double-run
        if ($olt->sync_status === 'syncing') {
            return back()->with('info', "⏳ Sync OLT {$olt->name} sedang berjalan, tunggu selesai.");
        }

        $olt->update([
            'sync_status'  => 'syncing',
            'sync_message' => 'Sync langsung sedang berjalan...',
        ]);

        try {
            set_time_limit(300);
            $result = OltService::for($olt)->syncAll();

            if (isset($result['error'])) {
                $olt->update([
                    'sync_status'  => 'failed',
                    'sync_message' => "Sync gagal: {$result['error']}",
                ]);
                return back()->with('error', "❌ Sync gagal: {$result['error']}");
            }

            $olt->update([
                'sync_status'  => 'done',
                'sync_message' => "Berhasil sync {$result['total']} ONTs — {$result['created']} baru, {$result['updated']} diperbarui.",
                'synced_at'    => now(),
            ]);

            return back()->with(
                'success',
                "✅ Sync selesai! {$result['total']} ONTs — {$result['created']} baru, {$result['updated']} diperbarui."
            );

        } catch (\Throwable $e) {
            $olt->update([
                'sync_status'  => 'failed',
                'sync_message' => "Exception: {$e->getMessage()}",
            ]);
            return back()->with('error', "❌ Sync error: {$e->getMessage()}");
        }
    }

    /**
     * AJAX: return current sync_status for a given OLT.
     */
    public function syncStatus(Olt $olt)
    {
        $this->authorizeOlt($olt);
        return response()->json([
            'sync_status'  => $olt->sync_status,
            'sync_message' => $olt->sync_message,
            'synced_at'    => $olt->synced_at?->diffForHumans(),
        ]);
    }

    /**
     * Link an ONT to a customer
     */
    public function linkCustomer(Request $request, Ont $ont)
    {
        $this->authorizeOnt($ont);
        $request->validate(['customer_id' => 'nullable|exists:customers,id']);
        $customerId = $request->filled('customer_id') ? (int) $request->customer_id : null;

        if ($customerId) {
            // Clear any previous ONT link for this customer
            Ont::where('customer_id', $customerId)
                ->where('id', '!=', $ont->id)
                ->update(['customer_id' => null]);

            $customer = Customer::find($customerId);
            if ($customer) {
                $customer->update(['ont_sn' => $ont->serial_number]);
            }
        } else {
            // Unlinking: clear ont_sn on the previously linked customer
            if ($ont->customer_id) {
                $prevCustomer = Customer::find($ont->customer_id);
                if ($prevCustomer && $prevCustomer->ont_sn === $ont->serial_number) {
                    $prevCustomer->update(['ont_sn' => null]);
                }
            }
        }

        $ont->update(['customer_id' => $customerId]);

        return back()->with('success', $customerId ? 'ONT linked to customer.' : 'ONT unlinked from customer.');
    }

    /**
     * Bulk unlink multiple ONTs from their customers.
     */
    public function bulkUnlinkOnts(Request $request, Olt $olt)
    {
        $this->authorizeOlt($olt);
        $request->validate(['ont_ids' => 'required|array', 'ont_ids.*' => 'integer|exists:onts,id']);

        $onts = \App\Models\Ont::whereIn('id', $request->ont_ids)
            ->where('olt_id', $olt->id)
            ->whereNotNull('customer_id')
            ->get();

        $count = 0;
        foreach ($onts as $ont) {
            $prevCustomer = Customer::find($ont->customer_id);
            if ($prevCustomer && $prevCustomer->ont_sn === $ont->serial_number) {
                $prevCustomer->update(['ont_sn' => null]);
            }
            $ont->update(['customer_id' => null]);
            $count++;
        }

        return back()->with('success', "{$count} ONT berhasil di-unlink dari customer.");
    }

    /**
     * Auto-link ONTs to customers using strong evidence only:
     * 1. Exact serial number → customer.ont_sn
     *
     * Do NOT guess by area/name order because that can corrupt production mappings.
     */
    public function autoLinkCustomers(Olt $olt)
    {
        $this->authorizeOlt($olt);
        return back()->with('info', 'Auto-Link dinonaktifkan. Gunakan PPPoE per area -> customer, lalu assign ONT manual per customer.');
    }

    /**
     * Reboot a specific ONT via OLT Telnet.
     */
    public function rebootOnt(\Illuminate\Http\Request $request, \App\Models\Ont $ont)
    {
        $this->authorizeOnt($ont);
        $olt    = $ont->olt;
        $result = OltService::for($olt)->rebootOnt($ont->pon_port, $ont->olt_port_index);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? "✅ Reboot command sent to ONT {$ont->serial_number}"
                : "❌ Reboot failed: " . ($result['error'] ?? 'Unknown error')
        );
    }

    /**
     * Push WAN config (VLAN + mode + DBA profile) to a specific ONT.
     */
    public function setWanConfig(\Illuminate\Http\Request $request, \App\Models\Ont $ont)
    {
        $this->authorizeOnt($ont);
        $request->validate([
            'vlan_id'        => 'nullable|integer|min:1|max:4094',
            'vlan_mode'      => 'nullable|in:tagged,untagged',
            'mode'           => 'required|in:pppoe,bridge,static',
            'vlan_priority'  => 'nullable|integer|min:0|max:7',
            'tcont_slot'     => 'nullable|integer|min:1|max:8',
            'gem_port'       => 'nullable|integer|min:1|max:8',
            'profile'        => 'nullable|string|max:100',
            'wan_index'      => 'nullable|integer|min:0|max:63',
            'pppoe_username' => 'nullable|string|max:64',
            'pppoe_password' => 'nullable|string|max:64',
            'mtu'            => 'nullable|integer|min:1492|max:9216',
            'service_port'   => 'nullable|integer|min:1|max:4094',
        ]);

        $olt    = $ont->olt;
        // Untagged mode → force vlan_id null regardless of input
        $vlanId = ($request->vlan_mode === 'untagged') ? null
                : ($request->filled('vlan_id') ? (int) $request->vlan_id : null);

        $result = OltService::for($olt)->setOntWanConfig(
            $ont->pon_port,
            $ont->olt_port_index,
            $vlanId,
            $request->mode,
            (int) ($request->tcont_slot ?? 1),
            (int) ($request->gem_port ?? 1),
            (string) ($request->profile ?? ''),
            (int) ($request->wan_index ?? 0),
            (int) ($request->vlan_priority ?? 0),
            (string) ($request->pppoe_username ?? ''),
            (string) ($request->pppoe_password ?? ''),
            (int) ($request->mtu ?? 0),
            (int) ($request->service_port ?? 0)
        );

        $modeLabel  = $request->mode === 'bridge' ? 'IPoE/Bridge' : 'PPPoE';
        $vlanLabel  = $vlanId ? "VLAN {$vlanId}" : 'Untagged';
        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? "✅ WAN config applied: {$vlanLabel} / {$modeLabel} on ONT {$ont->serial_number}"
                : "❌ WAN config failed: " . ($result['error'] ?? 'Unknown error')
        );
    }

    /**
     * Rebind DBA / bandwidth profile to a specific ONT (change speed plan).
     * DBA = Dynamic Bandwidth Allocation — controls upstream bandwidth.
     */
    public function setProfile(\Illuminate\Http\Request $request, \App\Models\Ont $ont)
    {
        $this->authorizeOnt($ont);
        $request->validate([
            'profile_name' => 'required|string|max:100',
            'tcont_slot'   => 'nullable|integer|min:1|max:8',
        ]);

        $olt    = $ont->olt;
        $result = OltService::for($olt)->setOntServiceProfile(
            $ont->pon_port,
            $ont->olt_port_index,
            $request->profile_name,
            (int) ($request->tcont_slot ?? 1)
        );

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? "✅ DBA Profile '{$request->profile_name}' applied to ONT {$ont->serial_number}"
                : "❌ Set profile failed: " . ($result['error'] ?? 'Unknown error')
        );
    }
    /**
     * Global OLT monitoring dashboard — shows all OLTs with real-time online/offline stats.
     */
    public function monitor()
    {
        $user       = auth()->user();
        $oltQuery   = Olt::with('area')->orderBy('name');
        $ontQuery   = Ont::where('status', 'offline')->with(['olt', 'customer'])
                         ->orderBy('olt_id')->orderBy('pon_port');

        if ($user->role === 'partner') {
            $oltQuery->where('area_id', $user->area_id);
            $ontQuery->whereHas('olt', fn ($q) => $q->where('area_id', $user->area_id));
        }

        $olts = $oltQuery->get();

        // Per-OLT stats (from DB — lightweight, no Telnet)
        $oltStats = [];
        foreach ($olts as $olt) {
            $onts = Ont::where('olt_id', $olt->id)->get();
            $oltStats[$olt->id] = [
                'total'   => $onts->count(),
                'online'  => $onts->where('status', 'online')->count(),
                'offline' => $onts->where('status', 'offline')->count(),
            ];
        }

        // Global totals
        $globalStats = [
            'total'   => array_sum(array_column($oltStats, 'total')),
            'online'  => array_sum(array_column($oltStats, 'online')),
            'offline' => array_sum(array_column($oltStats, 'offline')),
        ];

        $offlineOnts = $ontQuery->get();

        return view('admin.olts.monitor', compact('olts', 'oltStats', 'globalStats', 'offlineOnts'));
    }
}
