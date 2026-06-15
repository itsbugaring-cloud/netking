<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Services\MikroTikService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->withCount('customers')->orderBy('name')->get();
        $selectedArea = null;
        $queues = [];
        $error = null;

        if ($request->filled('area_id')) {
            $selectedArea = Area::findOrFail($request->area_id);
            $mikrotik = MikroTikService::forArea($selectedArea);
            $result = $mikrotik->getSimpleQueues();

            if ($result['success']) {
                $queues = $result['data'];
                // Match queues to customers
                $customerIds = [];
                foreach ($queues as &$q) {
                    if (preg_match('/^nk-(\d+)$/', $q['name'] ?? '', $m)) {
                        $customerIds[] = (int)$m[1];
                    }
                    $q['_customer'] = null;
                }
                unset($q);

                if ($customerIds) {
                    $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');
                    foreach ($queues as &$q) {
                        if (preg_match('/^nk-(\d+)$/', $q['name'] ?? '', $m)) {
                            $q['_customer'] = $customers->get((int)$m[1]);
                        }
                    }
                    unset($q);
                }
            } else {
                $error = $result['error'];
            }
        }

        return view('admin.queues.index', compact('areas', 'selectedArea', 'queues', 'error'));
    }

    public function create(Request $request)
    {
        $area = Area::findOrFail($request->area_id);
        $customers = Customer::where('area_id', $area->id)->whereNotNull('remote_ip')->orderBy('name')->get();
        return view('admin.queues.create', compact('area', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'customer_id' => 'required|exists:customers,id',
            'upload_speed' => 'required|integer|min:1',
            'download_speed' => 'required|integer|min:1',
            'burst_upload' => 'nullable|integer|min:0',
            'burst_download' => 'nullable|integer|min:0',
            'burst_threshold_up' => 'nullable|integer|min:0',
            'burst_threshold_down' => 'nullable|integer|min:0',
            'burst_time' => 'nullable|integer|min:0',
        ]);

        $area = Area::findOrFail($request->area_id);
        $customer = Customer::findOrFail($request->customer_id);
        $mikrotik = MikroTikService::forArea($area);

        $name = "nk-{$customer->id}";
        $target = "{$customer->remote_ip}/32";
        $maxLimit = "{$request->upload_speed}M/{$request->download_speed}M";

        $burstLimit = null;
        $burstThreshold = null;
        $burstTime = null;

        if ($request->filled('burst_upload') && $request->filled('burst_download')) {
            $burstLimit = "{$request->burst_upload}M/{$request->burst_download}M";
        }
        if ($request->filled('burst_threshold_up') && $request->filled('burst_threshold_down')) {
            $burstThreshold = "{$request->burst_threshold_up}M/{$request->burst_threshold_down}M";
        }
        if ($request->filled('burst_time')) {
            $burstTime = "{$request->burst_time}/{$request->burst_time}";
        }

        $comment = "{$customer->name} ({$customer->pppoe_user})";

        $result = $mikrotik->createSimpleQueue($name, $target, $maxLimit, $burstLimit, $burstThreshold, $burstTime, $comment);

        if ($result['success']) {
            return redirect()->route('admin.queues.index', ['area_id' => $area->id])
                ->with('success', "Queue '{$name}' created: {$maxLimit}");
        }

        return back()->withInput()->with('error', "Failed: " . ($result['error'] ?? 'Unknown'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'queue_id' => 'required|string',
            'upload_speed' => 'required|integer|min:1',
            'download_speed' => 'required|integer|min:1',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        $params = ['max-limit' => "{$request->upload_speed}M/{$request->download_speed}M"];

        if ($request->filled('burst_upload') && $request->filled('burst_download')) {
            $params['burst-limit'] = "{$request->burst_upload}M/{$request->burst_download}M";
        }
        if ($request->filled('burst_threshold_up') && $request->filled('burst_threshold_down')) {
            $params['burst-threshold'] = "{$request->burst_threshold_up}M/{$request->burst_threshold_down}M";
        }

        $result = $mikrotik->updateSimpleQueue($request->queue_id, $params);

        if ($result['success']) {
            return redirect()->route('admin.queues.index', ['area_id' => $area->id])
                ->with('success', "Queue updated: {$params['max-limit']}");
        }

        return back()->with('error', "Failed: " . ($result['error'] ?? 'Unknown'));
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'queue_id' => 'required|string',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $result = $mikrotik->deleteSimpleQueue($request->queue_id);

        if ($result['success']) {
            return redirect()->route('admin.queues.index', ['area_id' => $area->id])
                ->with('success', 'Queue deleted.');
        }

        return back()->with('error', "Failed: " . ($result['error'] ?? 'Unknown'));
    }

    public function sync(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id']);
        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        $result = $mikrotik->getSimpleQueues();
        if (!$result['success']) {
            return back()->with('error', 'Cannot connect: ' . $result['error']);
        }

        $routerQueues = collect($result['data']);
        $customers = Customer::where('area_id', $area->id)->whereNotNull('remote_ip')->whereNotNull('package_id')->with('package')->get();

        // Find orphaned (on router, no customer)
        $orphaned = $routerQueues->filter(function ($q) {
            return preg_match('/^nk-(\d+)$/', $q['name'] ?? '') && !Customer::find((int)str_replace('nk-', '', $q['name']));
        });

        // Find missing (customer exists, no queue)
        $queueNames = $routerQueues->pluck('name')->toArray();
        $missing = $customers->filter(fn($c) => !in_array("nk-{$c->id}", $queueNames));

        return view('admin.queues.sync', compact('area', 'orphaned', 'missing', 'routerQueues', 'customers'));
    }
}
