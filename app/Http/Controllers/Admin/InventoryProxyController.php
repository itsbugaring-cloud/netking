<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryProxyController extends Controller
{
    private string $baseUrl;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.inventory.url', 'http://127.0.0.1:3000'), '/');
        $this->username = config('services.inventory.username', '');
        $this->password = config('services.inventory.password', '');
    }

    /**
     * GET /admin/inventory/summary
     * Proxy ke inventory API — dipakai dashboard widget.
     * Token di-cache 55 menit, di-refresh otomatis jika expired.
     */
    public function summary()
    {
        if (empty($this->username) || empty($this->password)) {
            return response()->json(['error' => 'Inventory not configured'], 503);
        }

        try {
            $token = $this->getToken();
            $res   = Http::timeout(5)
                ->withToken($token)
                ->get("{$this->baseUrl}/api/asset/dashboard");

            if ($res->status() === 401) {
                // Token expired — hapus cache dan retry sekali
                Cache::forget('inventory_service_token');
                $token = $this->getToken();
                $res   = Http::timeout(5)->withToken($token)->get("{$this->baseUrl}/api/asset/dashboard");
            }

            if (! $res->ok()) {
                return response()->json(['error' => 'Inventory API error'], 502);
            }

            // API wraps response in { success, data: { grand_total, per_lokasi, ... } }
            $payload    = $res->json('data') ?? $res->json();
            $grandTotal = $payload['grand_total'] ?? [];
            $perLokasi  = $payload['per_lokasi']  ?? [];

            // Kabel: jumlahkan sisa meter dari semua lokasi
            $totalKabelMeter = collect($perLokasi)->sum(fn ($l) => (float) ($l['total_sisa_meter'] ?? 0));

            // Terpasang: unit di POP distribusi (bukan gudang)
            $unitTerpasang = collect($perLokasi)
                ->where('jenis_lokasi', 'pop_distribusi')
                ->sum(fn ($l) => (int) ($l['jumlah_unit'] ?? 0));

            return response()->json([
                'jumlah_unit'       => $grandTotal['jumlah_unit']    ?? null,
                'total_kabel_meter' => $totalKabelMeter ?: null,
                'unit_terpasang'    => $unitTerpasang   ?: null,
                'total_nilai_aset'  => $grandTotal['total_nilai_aset'] ?? null,
            ])->header('Cache-Control', 'no-store');

        } catch (\Throwable $e) {
            Log::warning('InventoryProxy: ' . $e->getMessage());
            return response()->json(['error' => 'Inventory unavailable'], 503);
        }
    }

    /** Ambil JWT token — cache 55 menit */
    private function getToken(): string
    {
        return Cache::remember('inventory_service_token', now()->addMinutes(55), function () {
            $res = Http::timeout(5)->post("{$this->baseUrl}/api/login", [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if (! $res->ok() || empty($res->json('data.token'))) {
                throw new \RuntimeException('Inventory login failed: ' . $res->body());
            }

            return $res->json('data.token');
        });
    }

}
