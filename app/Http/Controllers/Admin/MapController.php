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
        try {
            // Get all customers with valid coordinates
            $customers = Customer::with('area')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            // Get all areas for filtering if needed
            $areas = Area::all();

            $html = view('admin.maps.index', compact('customers', 'areas'))->render();
            return response($html);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
