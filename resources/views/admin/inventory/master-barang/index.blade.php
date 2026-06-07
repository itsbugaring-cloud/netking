@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')
<div class="ms-page nk-list-page inv-master-barang-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-list-ul'></i> Inventaris</div>
      <h1 class="ms-page-title">Master Barang</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.kategori.index') }}" class="ms-btn-secondary">
        <i class='bx bx-category'></i> Kategori
      </a>
      <a href="{{ route('admin.inventory.master-barang.create') }}" class="ms-btn">
        <i class='bx bx-plus'></i> Tambah
      </a>
    </div>
  </div>



  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Daftar Master Barang</h5>
        <div class="ms-panel-subtitle">Semua jenis barang inventori</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> {{ $masterBarang->total() }} item</span>
      </div>
    </div>

    <div class="ms-toolbar">
      <div class="ms-toolbar-left">
        <form method="GET" action="{{ route('admin.inventory.master-barang.index') }}" class="ms-filter-form">
          <input type="text" name="search" value="{{ request('search') }}"
                 placeholder="Cari merek / tipe..." class="form-control form-control-sm">
          <select name="kategori_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach($kategori_options as $id => $nama)
              <option value="{{ $id }}" {{ request('kategori_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
            @endforeach
          </select>
          <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Jenis</option>
            @foreach($jenis_options as $val => $label)
              <option value="{{ $val }}" {{ request('jenis') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <button type="submit" class="ms-btn-secondary ms-btn-sm"><i class='bx bx-search'></i></button>
          @if(request()->anyFilled(['search','kategori_id','jenis']))
          <a href="{{ route('admin.inventory.master-barang.index') }}" class="ms-btn-ghost ms-btn-sm">Reset Filter</a>
          @endif
        </form>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Merek</th>
              <th>Tipe / Model</th>
              <th>Kategori</th>
              <th>Jenis</th>
              <th class="text-end">Harga Default</th>
              <th style="width:100px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($masterBarang as $mb)
            <tr>
              <td><span style="font-weight:500;">{{ $mb->merek }}</span></td>
              <td>{{ $mb->tipe }}</td>
              <td>{{ $mb->kategori->nama ?? '-' }}</td>
              <td>
                @if($mb->jenis_penghitungan === 'sn')
                  <span class="badge-status badge-active">SN</span>
                @elseif($mb->jenis_penghitungan === 'meteran')
                  <span class="badge-status badge-pending">Meteran</span>
                @elseif($mb->jenis_penghitungan === 'qty')
                  <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Qty</span>
                @else
                  <span class="badge-status badge-inactive">{{ $mb->jenis_penghitungan }}</span>
                @endif
              </td>
              <td class="text-end">
                @if($mb->harga_default)
                  Rp {{ number_format($mb->harga_default, 0, ',', '.') }}
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.inventory.master-barang.edit', $mb) }}" class="nk-action-btn edit" title="Edit">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="{{ route('admin.inventory.master-barang.destroy', $mb) }}" method="POST" class="m-0"
                        onsubmit="return confirm('Hapus master barang ini?')">
                    @csrf @method('DELETE')
                    <button class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="6">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-package'></i></div>
                <div class="empty-state-title">Belum ada master barang</div>
                <div class="empty-state-desc">Mulai tambahkan barang pertama</div>
              </div>
            </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($masterBarang->hasPages())
    <div class="ms-panel-footer">{{ $masterBarang->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
@endsection
