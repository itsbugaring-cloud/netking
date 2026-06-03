<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InvKategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // ── index ─────────────────────────────────────────────────────────

    public function index()
    {
        $kategoris = InvKategori::withCount('masterBarangs')
            ->latest()
            ->get();

        return view('admin.inventory.kategori.index', compact('kategoris'));
    }

    // ── store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:inv_kategori,nama_kategori',
        ]);

        InvKategori::create(['nama_kategori' => $request->nama_kategori]);

        session()->flash('success', 'Kategori berhasil ditambahkan.');

        return redirect()->back();
    }

    // ── update ────────────────────────────────────────────────────────

    public function update(Request $request, InvKategori $invKategori)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:inv_kategori,nama_kategori,' . $invKategori->id,
        ]);

        $invKategori->update(['nama_kategori' => $request->nama_kategori]);

        session()->flash('success', 'Kategori berhasil diperbarui.');

        return redirect()->back();
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function destroy(InvKategori $invKategori)
    {
        if ($invKategori->masterBarangs()->count() > 0) {
            session()->flash('error', 'Kategori masih digunakan oleh master barang dan tidak dapat dihapus.');

            return redirect()->back();
        }

        $invKategori->delete();

        session()->flash('success', 'Kategori berhasil dihapus.');

        return redirect()->back();
    }
}
