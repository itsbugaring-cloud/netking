<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvLogTransaksi;
use App\Models\InvLokasi;
use App\Models\InvMasterBarang;
use App\Models\InvUnit;
use App\Models\User;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $query = InvUnit::with(['masterBarang.kategori', 'lokasi'])->latest();

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($lokasi_id = request('lokasi_id')) {
            $query->where('lokasi_id', $lokasi_id);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%");
            });
        }

        $units          = $query->paginate(25)->withQueryString();
        $lokasi_list    = InvLokasi::orderBy('nama_lokasi')->get();
        $status_options = InvUnit::STATUS_LABELS;
        $total_unit     = InvUnit::count();
        $total_gudang   = InvUnit::where('status', 'gudang')->count();
        $total_terpasang = InvUnit::where('status', 'terpasang')->count();
        $total_rusak    = InvUnit::where('status', 'rusak')->count();

        return view('admin.inventory.units.index', compact(
            'units',
            'lokasi_list',
            'status_options',
            'total_unit',
            'total_gudang',
            'total_terpasang',
            'total_rusak',
        ));
    }

    // ── create ────────────────────────────────────────────────────────

    public function create()
    {
        $master_list = InvMasterBarang::where('jenis_penghitungan', 'sn')
            ->with('kategori')
            ->orderBy('merek')
            ->get();
        $lokasi_list    = InvLokasi::orderBy('nama_lokasi')->get();
        $status_options = InvUnit::STATUS_LABELS;

        return view('admin.inventory.units.form', [
            'unit'           => null,
            'master_list'    => $master_list,
            'lokasi_list'    => $lokasi_list,
            'status_options' => $status_options,
            'partner_list'   => User::where('role', 'partner')->orderBy('name')->get(),
        ]);
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'master_barang_id'  => 'required|exists:inv_master_barang,id',
            'serial_number'     => 'required|string|unique:inv_units,serial_number',
            'mac_address'       => 'nullable|string|max:100',
            'nilai_aset'        => 'nullable|numeric|min:0',
            'lokasi_id'         => 'required|exists:inv_lokasi,id',
            'penanggung_jawab'  => 'nullable|string|max:150',
            'catatan'           => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'gudang';

        $unit = InvUnit::create($validated);

        InvLogTransaksi::create([
            'user_id'          => auth()->id(),
            'tipe'             => 'masuk_baru',
            'referensi_tabel'  => 'inv_units',
            'referensi_id'     => $unit->id,
            'lokasi_tujuan_id' => $unit->lokasi_id,
            'detail_baru'      => $unit->toArray(),
            'created_at'       => now(),
        ]);

        return redirect()->route('admin.inventory.units.show', $unit)
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    // ── show ──────────────────────────────────────────────────────────

    public function show(InvUnit $invUnit)
    {
        $invUnit->load(['masterBarang.kategori', 'lokasi', 'photos']);

        $logs = InvLogTransaksi::with(['user', 'lokasiAsal', 'lokasiTujuan'])
            ->where('referensi_tabel', 'inv_units')
            ->where('referensi_id', $invUnit->id)
            ->latest('created_at')
            ->get();

        $lokasi_list = InvLokasi::orderBy('nama_lokasi')->get();

        return view('admin.inventory.units.show', compact('invUnit', 'logs', 'lokasi_list'));
    }

    // ── edit ──────────────────────────────────────────────────────────

    public function edit(InvUnit $invUnit)
    {
        $master_list = InvMasterBarang::where('jenis_penghitungan', 'sn')
            ->with('kategori')
            ->orderBy('merek')
            ->get();
        $lokasi_list    = InvLokasi::orderBy('nama_lokasi')->get();
        $status_options = InvUnit::STATUS_LABELS;

        return view('admin.inventory.units.form', [
            'unit'           => $invUnit,
            'master_list'    => $master_list,
            'lokasi_list'    => $lokasi_list,
            'status_options' => $status_options,
            'partner_list'   => User::where('role', 'partner')->orderBy('name')->get(),
        ]);
    }

    // ── update ────────────────────────────────────────────────────────

    public function update(Request $request, InvUnit $invUnit)
    {
        $validated = $request->validate([
            'master_barang_id' => 'required|exists:inv_master_barang,id',
            'serial_number'    => 'required|string|unique:inv_units,serial_number,' . $invUnit->id,
            'mac_address'      => 'nullable|string|max:100',
            'nilai_aset'       => 'nullable|numeric|min:0',
            'lokasi_id'        => 'required|exists:inv_lokasi,id',
            'penanggung_jawab'          => 'nullable|string|max:150',
            'penanggung_jawab_user_id'  => 'nullable|exists:users,id',
            'catatan'                   => 'nullable|string',
            'status'                    => 'nullable|in:' . implode(',', array_keys(InvUnit::STATUS_LABELS)),
        ]);

        $detailLama = $invUnit->toArray();

        $invUnit->update($validated);

        InvLogTransaksi::create([
            'user_id'         => auth()->id(),
            'tipe'            => 'penyesuaian',
            'referensi_tabel' => 'inv_units',
            'referensi_id'    => $invUnit->id,
            'detail_lama'     => $detailLama,
            'detail_baru'     => $invUnit->fresh()->toArray(),
            'created_at'      => now(),
        ]);

        return redirect()->route('admin.inventory.units.show', $invUnit)
            ->with('success', 'Unit berhasil diperbarui.');
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function destroy(InvUnit $invUnit)
    {
        $allowedStatuses = ['rusak', 'terjual', 'hilang'];

        if (! in_array($invUnit->status, $allowedStatuses)) {
            return redirect()->back()
                ->with('error', 'Unit hanya bisa dihapus jika status rusak/terjual/hilang.');
        }

        $invUnit->delete();

        return redirect()->route('admin.inventory.units.index')
            ->with('success', 'Unit berhasil dihapus.');
    }

    // ── mutasi ────────────────────────────────────────────────────────

    public function mutasi(Request $request, InvUnit $invUnit)
    {
        $validated = $request->validate([
            'lokasi_id'   => 'required|exists:inv_lokasi,id',
            'keterangan'  => 'nullable|string',
        ]);

        $lokasiAsalId = $invUnit->lokasi_id;

        $invUnit->update(['lokasi_id' => $validated['lokasi_id']]);

        InvLogTransaksi::create([
            'user_id'          => auth()->id(),
            'tipe'             => 'mutasi',
            'referensi_tabel'  => 'inv_units',
            'referensi_id'     => $invUnit->id,
            'lokasi_asal_id'   => $lokasiAsalId,
            'lokasi_tujuan_id' => $validated['lokasi_id'],
            'keterangan'       => $validated['keterangan'] ?? null,
            'created_at'       => now(),
        ]);

        return redirect()->route('admin.inventory.units.show', $invUnit)
            ->with('success', 'Mutasi unit berhasil dicatat.');
    }

    // ── pasang ────────────────────────────────────────────────────────

    public function pasang(Request $request, InvUnit $invUnit)
    {
        $validated = $request->validate([
            'lokasi_id'        => 'required|exists:inv_lokasi,id',
            'penanggung_jawab' => 'required|string|max:150',
            'keterangan'       => 'nullable|string',
        ]);

        $lokasiAsalId = $invUnit->lokasi_id;

        $invUnit->update([
            'status'           => 'terpasang',
            'lokasi_id'        => $validated['lokasi_id'],
            'penanggung_jawab' => $validated['penanggung_jawab'],
        ]);

        InvLogTransaksi::create([
            'user_id'          => auth()->id(),
            'tipe'             => 'pasang',
            'referensi_tabel'  => 'inv_units',
            'referensi_id'     => $invUnit->id,
            'lokasi_asal_id'   => $lokasiAsalId,
            'lokasi_tujuan_id' => $validated['lokasi_id'],
            'keterangan'       => $validated['keterangan'] ?? null,
            'created_at'       => now(),
        ]);

        return redirect()->route('admin.inventory.units.show', $invUnit)
            ->with('success', 'Unit berhasil ditandai sebagai terpasang.');
    }

    // ── retur ─────────────────────────────────────────────────────────

    public function retur(Request $request, InvUnit $invUnit)
    {
        $validated = $request->validate([
            'keterangan' => 'required|string',
        ]);

        $lokasiAsalId = $invUnit->lokasi_id;

        $invUnit->update(['status' => 'rusak']);

        InvLogTransaksi::create([
            'user_id'         => auth()->id(),
            'tipe'            => 'retur',
            'referensi_tabel' => 'inv_units',
            'referensi_id'    => $invUnit->id,
            'lokasi_asal_id'  => $lokasiAsalId,
            'keterangan'      => $validated['keterangan'],
            'created_at'      => now(),
        ]);

        return redirect()->route('admin.inventory.units.show', $invUnit)
            ->with('success', 'Unit berhasil diretur dan ditandai rusak.');
    }
}
