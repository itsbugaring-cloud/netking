<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvLogTransaksi;
use App\Models\User;
use Illuminate\Http\Request;

class LogTransaksiController extends Controller
{
    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $query = InvLogTransaksi::with(['user', 'lokasiAsal', 'lokasiTujuan'])
            ->latest('created_at');

        if ($tipe = request('tipe')) {
            $query->where('tipe', $tipe);
        }

        if ($user_id = request('user_id')) {
            $query->where('user_id', $user_id);
        }

        if ($referensi_tabel = request('referensi_tabel')) {
            $query->where('referensi_tabel', $referensi_tabel);
        }

        if ($date_from = request('date_from')) {
            $query->whereDate('created_at', '>=', $date_from);
        }

        if ($date_to = request('date_to')) {
            $query->whereDate('created_at', '<=', $date_to);
        }

        $logs         = $query->paginate(30)->withQueryString();
        $tipe_options = InvLogTransaksi::TIPE_LABELS;
        $user_list    = User::orderBy('name')->select('id', 'name')->get();

        return view('admin.inventory.history.index', compact(
            'logs',
            'tipe_options',
            'user_list',
        ));
    }
}
