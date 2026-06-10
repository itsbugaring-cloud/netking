<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Services\MikroTikService;
use Illuminate\Http\Request;

class AddressListController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->orderBy('name')->get();
        $selectedArea = null;
        $entries = [];
        $error = null;
        $listName = config('netking.isolir_list', 'isolir');

        if ($request->filled('area_id')) {
            $selectedArea = Area::findOrFail($request->area_id);
            $mikrotik = MikroTikService::forArea($selectedArea);
            $result = $mikrotik->getAddressList($listName);

            if ($result['success']) {
                $entries = $result['data'];
                // Match IPs to customers
                $ips = collect($entries)->pluck('address')->toArray();
                $customers = Customer::where('area_id', $selectedArea->id)
                    ->whereIn('remote_ip', $ips)
                    ->get()
                    ->keyBy('remote_ip');
                foreach ($entries as &$e) {
                    $e['_customer'] = $customers->get($e['address'] ?? '') ?? null;
                }
                unset($e);
            } else {
                $error = $result['error'];
            }
        }

        return view('admin.address-list.index', compact('areas', 'selectedArea', 'entries', 'error', 'listName'));
    }

    public function isolate(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'timeout' => 'nullable|string|max:10',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        if (!$customer->remote_ip) {
            return back()->with('error', 'Customer tidak memiliki IP address.');
        }

        $area = Area::findOrFail($customer->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $listName = config('netking.isolir_list', 'isolir');

        // Check if already isolated
        $check = $mikrotik->findInAddressList($customer->remote_ip, $listName);
        if (($check['found'] ?? false)) {
            return back()->with('info', "Customer {$customer->name} sudah di-isolir.");
        }

        $comment = "nk-{$customer->id} {$customer->name}";
        $result = $mikrotik->addToAddressList($customer->remote_ip, $listName, $request->timeout ?: null, $comment);

        if ($result['success']) {
            $customer->update(['is_isolated' => true, 'isolated_at' => now()]);
            return back()->with('success', "Customer {$customer->name} berhasil di-isolir.");
        }

        return back()->with('error', 'Gagal isolir: ' . ($result['error'] ?? 'Unknown'));
    }

    public function deisolate(Request $request)
    {
        $request->validate(['customer_id' => 'required|exists:customers,id']);

        $customer = Customer::findOrFail($request->customer_id);
        $area = Area::findOrFail($customer->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $listName = config('netking.isolir_list', 'isolir');

        $check = $mikrotik->findInAddressList($customer->remote_ip, $listName);
        if (!($check['found'] ?? false)) {
            $customer->update(['is_isolated' => false, 'isolated_at' => null]);
            return back()->with('info', "Customer {$customer->name} tidak ditemukan di address-list (sudah de-isolir).");
        }

        // Remove the entry
        $entryId = $check['data'][0]['.id'] ?? null;
        if ($entryId) {
            $result = $mikrotik->removeFromAddressList($entryId);
            if ($result['success']) {
                $customer->update(['is_isolated' => false, 'isolated_at' => null]);
                return back()->with('success', "Customer {$customer->name} berhasil de-isolir.");
            }
            return back()->with('error', 'Gagal de-isolir: ' . ($result['error'] ?? 'Unknown'));
        }

        return back()->with('error', 'Entry ID tidak ditemukan.');
    }

    public function bulkIsolate(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'timeout' => 'nullable|string|max:10',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $listName = config('netking.isolir_list', 'isolir');

        // Find overdue customers (no approved payment for current period past grace period)
        $graceDays = (int) config('netking.isolir_grace_days', 7);
        $overdueCustomers = Customer::where('area_id', $area->id)
            ->where('status', 'active')
            ->where('is_isolated', false)
            ->whereNotNull('remote_ip')
            ->whereDoesntHave('payments', function ($q) {
                $q->where('status', 'approved')
                  ->where('periode_bulan', now()->month)
                  ->where('periode_tahun', now()->year);
            })
            ->where(function ($q) use ($graceDays) {
                // Only isolate if billing_start_date is past grace period into the month
                $q->whereNotNull('billing_start_date')
                  ->where('billing_start_date', '<', now()->subDays($graceDays));
            })
            ->get();

        $results = ['success' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []];

        foreach ($overdueCustomers as $customer) {
            $check = $mikrotik->findInAddressList($customer->remote_ip, $listName);
            if ($check['found'] ?? false) {
                $results['skipped']++;
                continue;
            }

            $comment = "nk-{$customer->id} {$customer->name}";
            $result = $mikrotik->addToAddressList($customer->remote_ip, $listName, $request->timeout ?: null, $comment);

            if ($result['success']) {
                $customer->update(['is_isolated' => true, 'isolated_at' => now()]);
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "{$customer->name}: " . ($result['error'] ?? 'Unknown');
            }

            usleep(500000); // 500ms delay
        }

        $msg = "Bulk isolir selesai: {$results['success']} berhasil, {$results['skipped']} sudah terisolir, {$results['failed']} gagal.";
        return back()->with($results['failed'] > 0 ? 'warning' : 'success', $msg);
    }

    public function sync(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id']);
        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $listName = config('netking.isolir_list', 'isolir');

        $result = $mikrotik->getAddressList($listName);
        if (!$result['success']) {
            return back()->with('error', 'Cannot connect: ' . $result['error']);
        }

        $routerIps = collect($result['data'])->pluck('address')->toArray();
        $customers = Customer::where('area_id', $area->id)->get();

        $corrected = 0;
        foreach ($customers as $customer) {
            $onRouter = in_array($customer->remote_ip, $routerIps);
            if ($onRouter && !$customer->is_isolated) {
                $customer->update(['is_isolated' => true, 'isolated_at' => now()]);
                $corrected++;
            } elseif (!$onRouter && $customer->is_isolated) {
                $customer->update(['is_isolated' => false, 'isolated_at' => null]);
                $corrected++;
            }
        }

        return back()->with('success', "Sync selesai. {$corrected} customer status dikoreksi.");
    }
}
