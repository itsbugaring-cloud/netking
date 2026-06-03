<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvKabel;
use App\Models\InvLogTransaksi;
use App\Models\InvLokasi;
use App\Models\InvQty;
use App\Models\InvUnit;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Unit stats ────────────────────────────────────────────────
        $total_unit      = InvUnit::count();
        $unit_gudang     = InvUnit::where('status', 'gudang')->count();
        $unit_terpasang  = InvUnit::where('status', 'terpasang')->count();
        $unit_rusak      = InvUnit::where('status', 'rusak')->count();

        // ── Kabel stats ───────────────────────────────────────────────
        $total_kabel_haspel = InvKabel::count();
        $total_sisa_meter   = InvKabel::sum('sisa_panjang');

        // ── Qty stats ─────────────────────────────────────────────────
        $total_qty_jenis = InvQty::count();

        // ── Nilai aset ────────────────────────────────────────────────
        $total_nilai_unit  = InvUnit::sum('nilai_aset');
        $total_nilai_kabel = InvKabel::selectRaw('SUM(sisa_panjang * nilai_per_meter) as total')
            ->value('total') ?? 0;
        $total_nilai_qty   = InvQty::selectRaw('SUM(jumlah * harga_satuan) as total')
            ->value('total') ?? 0;
        $total_nilai_aset  = (float) $total_nilai_unit
            + (float) $total_nilai_kabel
            + (float) $total_nilai_qty;

        // ── Recent activity ───────────────────────────────────────────
        $recent_log = InvLogTransaksi::with(['user', 'lokasiAsal', 'lokasiTujuan'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        // ── Per-lokasi breakdown ──────────────────────────────────────
        $per_lokasi = InvLokasi::withCount(['units', 'kabels', 'qtys'])->get();

        return view('admin.inventory.dashboard', compact(
            'total_unit',
            'unit_gudang',
            'unit_terpasang',
            'unit_rusak',
            'total_kabel_haspel',
            'total_sisa_meter',
            'total_qty_jenis',
            'total_nilai_unit',
            'total_nilai_kabel',
            'total_nilai_qty',
            'total_nilai_aset',
            'recent_log',
            'per_lokasi',
        ));
    }
}
