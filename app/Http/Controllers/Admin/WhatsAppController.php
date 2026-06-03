<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppService $wa;

    public function __construct(WhatsAppService $wa)
    {
        $this->wa = $wa;
    }

    public function index()
    {
        $configured  = !empty(config('services.fonnte.api_key'));
        $connected   = $configured && $this->wa->checkConnection();
        $customers   = Customer::whereNotNull('phone')->orderBy('name')->get();

        return view('admin.whatsapp.index', compact('configured', 'connected', 'customers'));
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $result = $this->wa->sendMessage($request->phone, $request->message);

        if ($result['success']) {
            return back()->with('success', "Message sent to {$request->phone} ✓");
        }

        return back()->with('error', 'Send failed: ' . ($result['error'] ?? 'Unknown'));
    }

    public function sendToCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'message'     => 'required|string|max:1000',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        if (empty($customer->phone)) {
            return back()->with('error', 'Customer has no phone number.');
        }

        $result = $this->wa->sendMessage($customer->phone, $request->message);

        if ($result['success']) {
            return back()->with('success', "Message sent to {$customer->name} ✓");
        }

        return back()->with('error', 'Send failed: ' . ($result['error'] ?? 'Unknown'));
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'fonnte_api_key' => 'required|string|max:512|regex:/^\S+$/',
        ]);

        // Write FONNTE_API_KEY to .env file
        $envPath = base_path('.env');
        $env     = file_get_contents($envPath);

        $key = 'FONNTE_API_KEY';
        $val = $request->fonnte_api_key;

        if (str_contains($env, $key . '=')) {
            // Replace existing value - use str_replace on the full line
            $env = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $val, $env);
        } else {
            $env .= "\n{$key}={$val}\n";
        }

        file_put_contents($envPath, $env);

        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return back()->with('success', 'WhatsApp API key saved. Test connection to verify.');
    }
}
