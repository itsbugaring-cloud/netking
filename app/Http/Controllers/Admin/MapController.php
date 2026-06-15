<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Area;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with('area')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $odps = \App\Models\Odp::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $areas = Area::all();

        return view('admin.maps.index', compact('customers', 'odps', 'areas'));
    }

    public function status(Request $request)
    {
        // Simple polling endpoint to return updated customer statuses
        $customers = Customer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'status', 'is_free', 'is_isolated')
            ->get();

        return response()->json($customers);
    }

    public function traffic(Request $request, Customer $customer)
    {
        if (!$customer->area || !$customer->pppoe_user) {
            return response()->json(['success' => false, 'error' => 'Invalid customer configuration']);
        }

        $mikrotik = \App\Services\MikroTikService::forArea($customer->area);
        if (!$mikrotik->isConnected()) {
            return response()->json(['success' => false, 'error' => 'Cannot connect to router']);
        }

        try {
            $query = new \RouterOS\Query('/interface/monitor-traffic');
            $query->equal('interface', '<pppoe-' . $customer->pppoe_user . '>');
            $query->equal('once', '');
            
            $client = $mikrotik->getClient(); // We need a getter for client or we can use another method
            $response = $client->query($query)->read();

            if (empty($response)) {
                return response()->json(['success' => false, 'error' => 'Interface not active']);
            }

            return response()->json([
                'success' => true,
                'rx' => (int) ($response[0]['rx-bits-per-second'] ?? 0),
                'tx' => (int) ($response[0]['tx-bits-per-second'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("MRTG Traffic Error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
