<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotspotController extends Controller
{
    public function login(Request $request)
    {
        return view('hotspot.login', [
            'linkLogin' => (string) $request->query('link-login', ''),
            'linkOrig' => (string) $request->query('link-orig', 'https://google.com'),
            'mac' => (string) $request->query('mac', ''),
            'ip' => (string) $request->query('ip', ''),
            'error' => (string) $request->query('error', ''),
        ]);
    }

    public function status(Request $request)
    {
        return view('hotspot.status', [
            'linkLogout' => (string) $request->query('link-logout', ''),
            'ip' => (string) $request->query('ip', ''),
            'mac' => (string) $request->query('mac', ''),
            'username' => (string) $request->query('username', ''),
            'uptime' => (string) $request->query('uptime', ''),
            'bytesIn' => (string) $request->query('bytes-in-nice', ''),
            'bytesOut' => (string) $request->query('bytes-out-nice', ''),
            'sessionTimeLeft' => (string) $request->query('session-time-left', ''),
        ]);
    }
}

