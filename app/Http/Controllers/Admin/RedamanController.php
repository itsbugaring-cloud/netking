<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Olt;
use App\Models\RedamanCalculation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedamanController extends Controller
{
    private function currentUser()
    {
        return Auth::user();
    }

    private function isPartner(): bool
    {
        return ($this->currentUser()->role ?? null) === 'partner';
    }

    private function accessibleAreaIds()
    {
        $user = $this->currentUser();
        if (! $user) {
            return collect();
        }

        if (($user->role ?? null) !== 'partner') {
            return Olt::query()->pluck('area_id')->filter()->unique()->values();
        }

        return Customer::where('partner_id', $user->id)
            ->whereNotNull('area_id')
            ->pluck('area_id')
            ->push($user->area_id)
            ->filter()
            ->unique()
            ->values();
    }

    private function ensureCustomerAccess(Customer $customer): void
    {
        if (! $this->isPartner()) {
            return;
        }

        abort_unless($customer->partner_id === $this->currentUser()->id, 403, 'Akses customer ditolak.');
    }

    /**
     * Kalkulator Redaman — main page
     */
    public function index()
    {
        $history = RedamanCalculation::where('user_id', Auth::id())
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.redaman.index', compact('history'));
    }

    /**
     * Save a calculation to history
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'   => 'required|in:fiber,wireless',
            'name'   => 'required|string|max:150',
            'inputs' => 'required|array',
            'results'=> 'required|array',
            'notes'  => 'nullable|string|max:500',
        ]);

        $calc = RedamanCalculation::create([
            'user_id' => Auth::id(),
            'type'    => $validated['type'],
            'name'    => $validated['name'],
            'inputs'  => $validated['inputs'],
            'results' => $validated['results'],
            'notes'   => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $calc->id,
            'message' => 'Kalkulasi disimpan.',
        ]);
    }

    /**
     * Get history list as JSON (for AJAX refresh)
     */
    public function historyData()
    {
        $history = RedamanCalculation::where('user_id', Auth::id())
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'type'       => $c->type,
                'name'       => $c->name,
                'notes'      => $c->notes,
                'results'    => $c->results,
                'inputs'     => $c->inputs,
                'created_at' => $c->created_at->format('d/m/Y H:i'),
                'status'     => $c->status_label,
            ]);

        return response()->json(['data' => $history]);
    }

    // ─── Cek Sinyal ONT ───────────────────────────────────────────────────────

    /**
     * Cek Sinyal ONT — page
     * Loads all OLTs with their ONTs (and customer/area) for stack-card display
     */
    public function signalChecker()
    {
        $query = Olt::with(['area', 'onts' => function ($q) {
            $q->with(['customer', 'area'])->orderBy('pon_port')->orderBy('olt_port_index');
        }])->orderBy('name');

        if ($this->isPartner()) {
            $query->whereIn('area_id', $this->accessibleAreaIds());
        }

        $olts = $query->get();

        // Summary stats
        $totalOnts   = 0;
        $goodCount   = 0;
        $weakCount   = 0;
        $critCount   = 0;
        $noDataCount = 0;

        foreach ($olts as $olt) {
            foreach ($olt->onts as $ont) {
                $totalOnts++;
                if ($ont->rx_power === null) { $noDataCount++; continue; }
                if ($ont->rx_power >= -25)   { $goodCount++; }
                elseif ($ont->rx_power >= -27){ $weakCount++; }
                elseif ($ont->rx_power >= -30){ $weakCount++; }
                else                          { $critCount++; }
            }
        }

        return view('admin.redaman.signal', compact('olts', 'totalOnts', 'goodCount', 'weakCount', 'critCount', 'noDataCount'));
    }

    /**
     * Cek Sinyal ONT — AJAX search customers
     * GET /admin/signal/customers?q=xxx
     */
    public function signalCustomers(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $customers = Customer::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('pppoe_user', 'like', "%{$q}%")
                      ->orWhere('ont_sn', 'like', "%{$q}%");
            })
            ->when($this->isPartner(), fn ($query) => $query->where('partner_id', $this->currentUser()->id))
            ->with(['area', 'package'])
            ->limit(15)
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'pppoe_user' => $c->pppoe_user,
                'ont_sn'     => $c->ont_sn,
                'area'       => $c->area?->name,
                'package'    => $c->package?->name,
                'status'     => $c->status,
            ]);

        return response()->json(['data' => $customers]);
    }

    /**
     * Cek Sinyal ONT — AJAX fetch live signal
     * GET /admin/signal/check/{customer}
     */
    public function signalCheck(Customer $customer)
    {
        $this->ensureCustomerAccess($customer);

        // [REMOVED] ACS/GenieACS feature removed — return database-only signal data
        $rxPower = $customer->ont?->rx_power;
        return response()->json([
            'success' => (bool) $rxPower,
            'source'  => 'database',
            'data'    => $rxPower ? [
                'rx_power'     => (float) $rxPower,
                'rx_power_str' => number_format((float) $rxPower, 2) . ' dBm',
                'quality'      => $this->signalQuality((float) $rxPower),
                'serial_number'=> $customer->ont_sn ?: $customer->ont?->serial_number,
                'model'        => $customer->ont?->model,
                'status'       => $customer->ont?->status,
                'area'         => $customer->area?->name,
                'customer_name' => $customer->name,
                'pppoe_user'   => $customer->pppoe_user,
            ] : null,
            'message' => $rxPower ? null : 'Data sinyal tidak tersedia (ACS tidak aktif).',
        ]);
    }

    private function signalQuality(float $rx): string
    {
        if ($rx > -10)  return 'too_strong';
        if ($rx >= -25) return 'good';
        if ($rx >= -27) return 'fair';
        if ($rx >= -30) return 'weak';
        return 'critical';
    }

    /**
     * Delete a saved calculation
     */
    public function destroy(int $id)
    {
        $calc = RedamanCalculation::where('user_id', Auth::id())->findOrFail($id);
        $calc->delete();

        return response()->json(['success' => true, 'message' => 'Dihapus.']);
    }
}
