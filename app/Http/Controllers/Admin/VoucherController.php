<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Voucher;
use App\Models\VoucherBatch;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function index()
    {
        $batches = VoucherBatch::withCount(['vouchers as used_count' => fn($q) => $q->where('status', 'used')])
            ->with('area')
            ->latest()
            ->paginate(20);

        return view('admin.vouchers.index', compact('batches'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('admin.vouchers.create', compact('areas'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:hotspot,pppoe',
            'quantity'      => 'required|integer|min:1|max:500',
            'duration_days' => 'required|integer|min:1',
            'price'         => 'nullable|numeric|min:0',
            'speed_limit'   => 'nullable|string|max:50',
            'profile'       => 'nullable|string|max:100',
            'prefix'        => 'nullable|string|max:6',
            'area_id'       => 'nullable|exists:areas,id',
        ]);

        $prefix   = strtoupper($validated['prefix'] ?? 'NK');
        $quantity = $validated['quantity'];
        $profile  = $validated['profile'] ?? 'default';

        $batch = VoucherBatch::create([
            'name'          => $validated['name'],
            'type'          => $validated['type'],
            'duration_days' => $validated['duration_days'],
            'price'         => $validated['price'] ?? 0,
            'speed_limit'   => $validated['speed_limit'],
            'profile'       => $profile,
            'prefix'        => $prefix,
            'total'         => $quantity,
            'used'          => 0,
            'area_id'       => $validated['area_id'] ?? null,
            'created_by'    => (int) auth()->id(),
        ]);

        // Generate unique voucher codes
        $codes = [];
        $attempts = 0;
        while (count($codes) < $quantity && $attempts < $quantity * 10) {
            $code = $prefix . '-' . strtoupper(Str::random(6));
            if (!in_array($code, $codes) && !Voucher::where('code', $code)->exists()) {
                $codes[] = $code;
            }
            $attempts++;
        }

        $now = now();
        $vouchers = array_map(fn($code) => [
            'batch_id'   => $batch->id,
            'code'       => $code,
            'status'     => 'unused',
            'created_at' => $now,
            'updated_at' => $now,
        ], $codes);

        Voucher::insert($vouchers);

        // Push to MikroTik for hotspot voucher mode
        $mikrotikPushed = 0;
        $mikrotikFailed = 0;

        if ($validated['type'] === 'hotspot') {
            try {
                $area = isset($validated['area_id']) ? Area::query()->find($validated['area_id']) : null;
                $mikrotik = $area ? MikroTikService::forArea($area) : app(MikroTikService::class);

                foreach ($codes as $code) {
                    $result = $mikrotik->pushVoucher($code, $profile);
                    if (($result['success'] ?? false) === true) {
                        $mikrotikPushed++;
                    } else {
                        $mikrotikFailed++;
                        Log::warning("Voucher {$code} gagal push ke MikroTik: " . (string) ($result['error'] ?? 'unknown'));
                    }
                }
            } catch (\Throwable $e) {
                Log::error('MikroTik koneksi gagal saat generate voucher', ['error' => $e->getMessage()]);
            }
        }

        $message = "Generated {$quantity} vouchers successfully.";
        if ($validated['type'] === 'hotspot') {
            $message .= $mikrotikFailed === 0
                ? " MikroTik: {$mikrotikPushed} berhasil."
                : " MikroTik: {$mikrotikPushed} berhasil, {$mikrotikFailed} gagal.";
        }

        return redirect()->route('admin.vouchers.show', $batch)
            ->with('success', $message);
    }

    public function show(VoucherBatch $voucher)
    {
        $batch    = $voucher->load('area');
        $vouchers = $batch->vouchers()->with('customer')->latest()->paginate(50);
        return view('admin.vouchers.show', compact('batch', 'vouchers'));
    }

    public function destroy(VoucherBatch $voucher)
    {
        $voucher->delete(); // cascade deletes individual vouchers
        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher batch deleted.');
    }
}
