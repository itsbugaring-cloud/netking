<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvLogTransaksi;
use App\Models\InvLokasi;
use App\Models\InvMasterBarang;
use App\Models\InvQty;
use Illuminate\Http\Request;

class QtyController extends Controller
{
    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $query = InvQty::with(['masterBarang.kategori', 'lokasi'])->latest();

        if ($lokasi_id = request('lokasi_id')) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($search = request('search')) {
            $query->whereHas('masterBarang', function ($q) use ($search) {
                $q->where('merek', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        $stocks      = $query->paginate(25)->withQueryString();
        $master_list = InvMasterBarang::where('jenis_penghitungan', 'qty')
            ->with('kategori')
            ->orderBy('merek')
            ->get();
        $lokasi_list    = InvLokasi::orderBy('nama_lokasi')->get();
        $total_nilai_qty = InvQty::selectRaw('SUM(jumlah * harga_satuan) as total')->value('total') ?? 0;

        return view('admin.inventory.qty.index', compact(
            'stocks',
            'master_list',
            'lokasi_list',
            'total_nilai_qty',
        ));
    }

    // ── store (new stock entry or increment if record exists) ─────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'master_barang_id' => 'required|exists:inv_master_barang,id',
            'lokasi_id'        => 'required|exists:inv_lokasi,id',
            'jumlah'           => 'required|integer|min:1',
            'harga_satuan'     => 'nullable|numeric|min:0',
            'catatan'          => 'nullable|string',
        ]);

        $existing = InvQty::where('master_barang_id', $validated['master_barang_id'])
            ->where('lokasi_id', $validated['lokasi_id'])
            ->first();

        if ($existing) {
            $existing->increment('jumlah', $validated['jumlah']);
            $invQty = $existing->fresh();
        } else {
            $invQty = InvQty::create($validated);
        }

        InvLogTransaksi::create([
            'user_id'          => auth()->id(),
            'tipe'             => 'masuk_baru',
            'referensi_tabel'  => 'inv_qty',
            'referensi_id'     => $invQty->id,
            'lokasi_tujuan_id' => $invQty->lokasi_id,
            'kuantitas'        => $validated['jumlah'],
            'keterangan'       => $validated['catatan'] ?? null,
            'created_at'       => now(),
        ]);

        session()->flash('success', 'Stok qty berhasil ditambahkan.');

        return redirect()->route('admin.inventory.qty.index');
    }

    // ── tambah ────────────────────────────────────────────────────────

    public function tambah(Request $request, InvQty $invQty)
    {
        $validated = $request->validate([
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $invQty->increment('jumlah', $validated['jumlah']);

        InvLogTransaksi::create([
            'user_id'          => auth()->id(),
            'tipe'             => 'masuk_baru',
            'referensi_tabel'  => 'inv_qty',
            'referensi_id'     => $invQty->id,
            'lokasi_tujuan_id' => $invQty->lokasi_id,
            'kuantitas'        => $validated['jumlah'],
            'keterangan'       => $validated['keterangan'] ?? null,
            'created_at'       => now(),
        ]);

        session()->flash('success', 'Stok berhasil ditambah sebanyak ' . $validated['jumlah'] . ' pcs.');

        return redirect()->route('admin.inventory.qty.index');
    }

    // ── kurangi ───────────────────────────────────────────────────────

    public function kurangi(Request $request, InvQty $invQty)
    {
        $validated = $request->validate([
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        if ($validated['jumlah'] > $invQty->jumlah) {
            session()->flash('error', 'Jumlah pengurangan melebihi stok yang tersedia (' . $invQty->jumlah . ').');

            return redirect()->back();
        }

        $invQty->decrement('jumlah', $validated['jumlah']);

        InvLogTransaksi::create([
            'user_id'         => auth()->id(),
            'tipe'            => 'barang_keluar',
            'referensi_tabel' => 'inv_qty',
            'referensi_id'    => $invQty->id,
            'lokasi_asal_id'  => $invQty->lokasi_id,
            'kuantitas'       => $validated['jumlah'],
            'keterangan'      => $validated['keterangan'] ?? null,
            'created_at'      => now(),
        ]);

        session()->flash('success', 'Stok berhasil dikurangi sebanyak ' . $validated['jumlah'] . ' pcs.');

        return redirect()->route('admin.inventory.qty.index');
    }

    // ── adjust (±1 quick button) ──────────────────────────────────────

    public function adjust(Request $request, InvQty $invQty)
    {
        $action = $request->input('action'); // 'plus' | 'minus'

        if ($action === 'plus') {
            $invQty->increment('jumlah', 1);
            InvLogTransaksi::create([
                'user_id'          => auth()->id(),
                'tipe'             => 'masuk_baru',
                'referensi_tabel'  => 'inv_qty',
                'referensi_id'     => $invQty->id,
                'lokasi_tujuan_id' => $invQty->lokasi_id,
                'kuantitas'        => 1,
                'keterangan'       => 'Tambah cepat +1',
                'created_at'       => now(),
            ]);
        } elseif ($action === 'minus') {
            if ($invQty->jumlah < 1) {
                session()->flash('error', 'Stok sudah 0, tidak bisa dikurangi.');
                return redirect()->back();
            }
            $invQty->decrement('jumlah', 1);
            InvLogTransaksi::create([
                'user_id'         => auth()->id(),
                'tipe'            => 'barang_keluar',
                'referensi_tabel' => 'inv_qty',
                'referensi_id'    => $invQty->id,
                'lokasi_asal_id'  => $invQty->lokasi_id,
                'kuantitas'       => 1,
                'keterangan'      => 'Kurang cepat -1',
                'created_at'      => now(),
            ]);
        }

        return redirect()->route('admin.inventory.qty.index');
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function destroy(InvQty $invQty)
    {
        if ($invQty->jumlah > 0) {
            session()->flash('error', 'Stok masih ada (' . $invQty->jumlah . ' pcs). Kosongkan stok terlebih dahulu sebelum menghapus.');

            return redirect()->back();
        }

        $invQty->delete();

        session()->flash('success', 'Record stok qty berhasil dihapus.');

        return redirect()->route('admin.inventory.qty.index');
    }
}
