<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvLokasi;
use App\Models\User;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    /** @var array<string,string> */
    private const JENIS_OPTIONS = [
        'gudang_utama'   => 'Gudang Utama',
        'pop_distribusi' => 'POP Distribusi',
    ];

    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $lokasi = InvLokasi::withCount(['units', 'kabels', 'qtys'])
            ->with('picUser')
            ->latest()
            ->paginate(20);

        return view('admin.inventory.lokasi.index', compact('lokasi'));
    }

    // ── create ────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.inventory.lokasi.form', [
            'lokasi'        => null,
            'jenis_options' => self::JENIS_OPTIONS,
            'partner_list'  => User::where('role', 'partner')->orderBy('name')->get(),
        ]);
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi'  => 'required|string|max:200',
            'alamat'       => 'nullable|string',
            'jenis'        => 'required|in:gudang_utama,pop_distribusi',
            'pic_nama'     => 'nullable|string|max:150',
            'pic_user_id'  => 'nullable|exists:users,id',
        ]);

        // Sync pic_nama from selected partner if not manually filled
        if (!empty($validated['pic_user_id']) && empty($validated['pic_nama'])) {
            $validated['pic_nama'] = User::find($validated['pic_user_id'])?->name;
        }

        InvLokasi::create($validated);

        return redirect()->route('admin.inventory.lokasi.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    // ── edit ──────────────────────────────────────────────────────────

    public function edit(InvLokasi $lokasi)
    {
        return view('admin.inventory.lokasi.form', [
            'lokasi'        => $lokasi,
            'jenis_options' => self::JENIS_OPTIONS,
            'partner_list'  => User::where('role', 'partner')->orderBy('name')->get(),
        ]);
    }

    // ── update ────────────────────────────────────────────────────────

    public function update(Request $request, InvLokasi $lokasi)
    {
        $validated = $request->validate([
            'nama_lokasi'  => 'required|string|max:200',
            'alamat'       => 'nullable|string',
            'jenis'        => 'required|in:gudang_utama,pop_distribusi',
            'pic_nama'     => 'nullable|string|max:150',
            'pic_user_id'  => 'nullable|exists:users,id',
        ]);

        if (!empty($validated['pic_user_id']) && empty($validated['pic_nama'])) {
            $validated['pic_nama'] = User::find($validated['pic_user_id'])?->name;
        }

        $lokasi->update($validated);

        return redirect()->route('admin.inventory.lokasi.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function destroy(InvLokasi $lokasi)
    {
        $hasItems = $lokasi->units()->count() > 0
            || $lokasi->kabels()->count() > 0
            || $lokasi->qtys()->count() > 0;

        if ($hasItems) {
            return redirect()->back()
                ->with('error', 'Lokasi masih memiliki item inventory dan tidak dapat dihapus.');
        }

        $lokasi->delete();

        return redirect()->route('admin.inventory.lokasi.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
