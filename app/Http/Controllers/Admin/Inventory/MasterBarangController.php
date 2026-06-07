<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvKategori;
use App\Models\InvMasterBarang;
use Illuminate\Http\Request;

class MasterBarangController extends Controller
{
    /** @var array<string,string> */
    private const JENIS_OPTIONS = [
        'sn'       => 'Serial Number (SN)',
        'meteran'  => 'Meteran (Kabel)',
        'qty'      => 'Quantity (Qty)',
    ];

    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $query = InvMasterBarang::with('kategori')->latest();

        if ($kategori_id = request('kategori_id')) {
            $query->where('kategori_id', $kategori_id);
        }

        if ($jenis = request('jenis')) {
            $query->where('jenis_penghitungan', $jenis);
        }

        $masterBarang     = $query->paginate(20)->withQueryString();
        $kategori_options = InvKategori::orderBy('nama_kategori')->pluck('nama_kategori', 'id');
        $jenis_options    = self::JENIS_OPTIONS;

        $filter_kategori_id = request('kategori_id');
        $filter_jenis       = request('jenis');

        return view('admin.inventory.master-barang.index', compact(
            'masterBarang',
            'kategori_options',
            'jenis_options',
            'filter_kategori_id',
            'filter_jenis',
        ));
    }

    // ── create ────────────────────────────────────────────────────────

    public function create()
    {
        $kategori      = InvKategori::orderBy('nama_kategori')->get();
        $jenis_options = self::JENIS_OPTIONS;

        return view('admin.inventory.master-barang.form', [
            'barang'        => null,
            'kategori'      => $kategori,
            'jenis_options' => $jenis_options,
        ]);
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id'        => 'required|exists:inv_kategori,id',
            'merek'              => 'required|string|max:100',
            'tipe'               => 'required|string|max:150',
            'jenis_penghitungan' => 'required|in:sn,meteran,qty',
            'deskripsi'          => 'nullable|string',
            'harga_default'      => 'nullable|numeric|min:0',
        ]);

        InvMasterBarang::create($validated);

        return redirect()->route('admin.inventory.master-barang.index')
            ->with('success', 'Master barang berhasil ditambahkan.');
    }

    // ── edit ──────────────────────────────────────────────────────────

    public function edit(InvMasterBarang $invMasterBarang)
    {
        $kategori      = InvKategori::orderBy('nama_kategori')->get();
        $jenis_options = self::JENIS_OPTIONS;

        return view('admin.inventory.master-barang.form', [
            'barang'        => $invMasterBarang,
            'kategori'      => $kategori,
            'jenis_options' => $jenis_options,
        ]);
    }

    // ── update ────────────────────────────────────────────────────────

    public function update(Request $request, InvMasterBarang $invMasterBarang)
    {
        $validated = $request->validate([
            'kategori_id'        => 'required|exists:inv_kategori,id',
            'merek'              => 'required|string|max:100',
            'tipe'               => 'required|string|max:150',
            'jenis_penghitungan' => 'required|in:sn,meteran,qty',
            'deskripsi'          => 'nullable|string',
            'harga_default'      => 'nullable|numeric|min:0',
        ]);

        $invMasterBarang->update($validated);

        return redirect()->route('admin.inventory.master-barang.index')
            ->with('success', 'Master barang berhasil diperbarui.');
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function destroy(InvMasterBarang $invMasterBarang)
    {
        $hasRefs = $invMasterBarang->units()->count() > 0
            || $invMasterBarang->kabels()->count() > 0
            || $invMasterBarang->qtys()->count() > 0;

        if ($hasRefs) {
            return redirect()->back()
                ->with('error', 'Master barang masih direferensi oleh data inventory dan tidak dapat dihapus.');
        }

        $invMasterBarang->delete();

        return redirect()->route('admin.inventory.master-barang.index')
            ->with('success', 'Master barang berhasil dihapus.');
    }
}
