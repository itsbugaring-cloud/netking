<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvKabel;
use App\Models\InvLogTransaksi;
use App\Models\InvLokasi;
use App\Models\InvMasterBarang;
use App\Models\User;
use Illuminate\Http\Request;

class KabelController extends Controller
{
    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $query = InvKabel::with(['masterBarang.kategori', 'lokasi'])->latest();

        if ($lokasi_id = request('lokasi_id')) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($search = request('search')) {
            $query->where('id_haspel', 'like', "%{$search}%");
        }

        $kabels       = $query->paginate(25)->withQueryString();
        $lokasi_list  = InvLokasi::orderBy('nama_lokasi')->get();
        $total_haspel = InvKabel::count();
        $total_sisa   = InvKabel::sum('sisa_panjang') ?? 0;
        $total_nilai  = InvKabel::selectRaw('SUM(sisa_panjang * nilai_per_meter) as total')->value('total') ?? 0;

        return view('admin.inventory.kabel.index', compact(
            'kabels',
            'lokasi_list',
            'total_haspel',
            'total_sisa',
            'total_nilai',
        ));
    }

    // ── create ────────────────────────────────────────────────────────

    public function create()
    {
        $master_list = InvMasterBarang::where('jenis_penghitungan', 'meteran')
            ->with('kategori')
            ->orderBy('merek')
            ->get();
        $lokasi_list = InvLokasi::orderBy('nama_lokasi')->get();

        // Auto-generate next haspel ID
        $lastHaspel  = InvKabel::orderBy('id', 'desc')->value('id_haspel');
        $nextNumber  = InvKabel::count() + 1;
        $next_haspel = 'HSP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('admin.inventory.kabel.form', [
            'kabel'        => null,
            'invKabel'     => null,
            'master_list'  => $master_list,
            'lokasi_list'  => $lokasi_list,
            'next_haspel'  => $next_haspel,
            'partner_list' => User::where('role', 'partner')->orderBy('name')->get(),
        ]);
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'master_barang_id' => 'required|exists:inv_master_barang,id',
            'id_haspel'        => 'required|string|unique:inv_kabel,id_haspel',
            'panjang_awal'     => 'required|numeric|min:0.01',
            'nilai_per_meter'  => 'nullable|numeric|min:0',
            'lokasi_id'        => 'required|exists:inv_lokasi,id',
            'penanggung_jawab'         => 'nullable|string|max:150',
            'penanggung_jawab_user_id' => 'nullable|exists:users,id',
            'catatan'          => 'nullable|string',
        ]);

        // sisa_panjang starts at panjang_awal on creation
        $validated['sisa_panjang'] = $validated['panjang_awal'];

        $kabel = InvKabel::create($validated);

        InvLogTransaksi::create([
            'user_id'          => auth()->id(),
            'tipe'             => 'masuk_baru',
            'referensi_tabel'  => 'inv_kabel',
            'referensi_id'     => $kabel->id,
            'lokasi_tujuan_id' => $kabel->lokasi_id,
            'kuantitas'        => $kabel->panjang_awal,
            'detail_baru'      => $kabel->toArray(),
            'created_at'       => now(),
        ]);

        session()->flash('success', 'Haspel kabel berhasil ditambahkan.');

        return redirect()->route('admin.inventory.kabel.show', $kabel);
    }

    // ── show ──────────────────────────────────────────────────────────

    public function show(InvKabel $invKabel)
    {
        $invKabel->load(['masterBarang.kategori', 'lokasi']);

        $logs = InvLogTransaksi::with(['user', 'lokasiAsal', 'lokasiTujuan'])
            ->where('referensi_tabel', 'inv_kabel')
            ->where('referensi_id', $invKabel->id)
            ->latest('created_at')
            ->get();

        return view('admin.inventory.kabel.show', compact('invKabel', 'logs'));
    }

    // ── potong ────────────────────────────────────────────────────────

    public function potong(Request $request, InvKabel $invKabel)
    {
        $validated = $request->validate([
            'panjang_potong' => 'required|numeric|min:0.01',
            'keterangan'     => 'nullable|string',
        ]);

        if ($validated['panjang_potong'] > $invKabel->sisa_panjang) {
            session()->flash('error', 'Panjang potong melebihi sisa panjang kabel yang tersedia (' . $invKabel->sisa_panjang . ' m).');

            return redirect()->back();
        }

        $invKabel->decrement('sisa_panjang', $validated['panjang_potong']);

        InvLogTransaksi::create([
            'user_id'         => auth()->id(),
            'tipe'            => 'potong_kabel',
            'referensi_tabel' => 'inv_kabel',
            'referensi_id'    => $invKabel->id,
            'lokasi_asal_id'  => $invKabel->lokasi_id,
            'kuantitas'       => $validated['panjang_potong'],
            'keterangan'      => $validated['keterangan'] ?? null,
            'created_at'      => now(),
        ]);

        session()->flash('success', 'Potong kabel berhasil dicatat. Sisa: ' . ($invKabel->sisa_panjang - $validated['panjang_potong']) . ' m.');

        return redirect()->route('admin.inventory.kabel.show', $invKabel);
    }

    // ── edit ──────────────────────────────────────────────────────────

    public function edit(InvKabel $invKabel)
    {
        $master_list = InvMasterBarang::where('jenis_penghitungan', 'meteran')
            ->with('kategori')
            ->orderBy('merek')
            ->get();
        $lokasi_list = InvLokasi::orderBy('nama_lokasi')->get();

        return view('admin.inventory.kabel.form', [
            'kabel'        => $invKabel,
            'invKabel'     => $invKabel,
            'master_list'  => $master_list,
            'lokasi_list'  => $lokasi_list,
            'next_haspel'  => null,
            'partner_list' => User::where('role', 'partner')->orderBy('name')->get(),
        ]);
    }

    // ── update ────────────────────────────────────────────────────────

    public function update(Request $request, InvKabel $invKabel)
    {
        $validated = $request->validate([
            'master_barang_id' => 'required|exists:inv_master_barang,id',
            'id_haspel'        => 'required|string|unique:inv_kabel,id_haspel,' . $invKabel->id,
            'panjang_awal'     => 'required|numeric|min:0.01',
            'nilai_per_meter'  => 'nullable|numeric|min:0',
            'lokasi_id'        => 'required|exists:inv_lokasi,id',
            'penanggung_jawab'         => 'nullable|string|max:150',
            'penanggung_jawab_user_id' => 'nullable|exists:users,id',
            'catatan'          => 'nullable|string',
        ]);

        $detailLama = $invKabel->toArray();

        $invKabel->update($validated);

        InvLogTransaksi::create([
            'user_id'         => auth()->id(),
            'tipe'            => 'penyesuaian',
            'referensi_tabel' => 'inv_kabel',
            'referensi_id'    => $invKabel->id,
            'detail_lama'     => $detailLama,
            'detail_baru'     => $invKabel->fresh()->toArray(),
            'created_at'      => now(),
        ]);

        session()->flash('success', 'Data haspel kabel berhasil diperbarui.');

        return redirect()->route('admin.inventory.kabel.show', $invKabel);
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function destroy(InvKabel $invKabel)
    {
        if ((float) $invKabel->sisa_panjang < (float) $invKabel->panjang_awal) {
            session()->flash('error', 'Kabel sudah pernah digunakan dan tidak dapat dihapus.');

            return redirect()->back();
        }

        $invKabel->delete();

        session()->flash('success', 'Haspel kabel berhasil dihapus.');

        return redirect()->route('admin.inventory.kabel.index');
    }
}
