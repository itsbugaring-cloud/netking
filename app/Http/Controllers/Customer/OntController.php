<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OntController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();
        return view('customer.ont.index', compact('customer'));
    }

    public function reboot(Request $request)
    {
        return back()->with('info', 'Reboot request sent.');
    }

    public function updateWifi(Request $request)
    {
        return back()->with('info', 'WiFi settings update requested.');
    }
}
