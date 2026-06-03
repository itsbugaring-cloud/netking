@extends('layouts.app')
@section('title', 'Lokasi Inventaris')

@section('content')
<div class="ms-page nk-list-page inv-lokasi-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-map-pin'></i> Inventaris</div>
      <h1 class="ms-page-title">Lokasi Gudang</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.lokasi.create') }}" class="ms-btn"><i class='bx bx-plus'></i> Tambah Lokasi</a>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  @php
    $countGudang = $lokasi->where('jenis', 'gudang_utama')->count();
    $countPop    = $lokasi->where('jenis', 'pop_distribusi')->count();
  @endphp

  <div class="ms-stat-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 1rem;">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info"><i class='bx bx-building'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Gudang Utama</div>
        <div class="ms-stat-value">{{ $countGudang }}</div>
        <div class="ms-stat-meta">lokasi gudang</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success"><i class='bx bx-network-chart'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">POP Distribusi</div>
        <div class="ms-stat-value">{{ $countPop }}</div>
        <div class="ms-stat-meta">titik distribusi</div>
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Daftar Lokasi</h5>
        <div class="ms-panel-subtitle">Semua gudang dan titik distribusi</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> {{ $lokasi->total() }} lokasi</span>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Nama Lokasi</th>
              <th>Jenis</th>
              <th>PIC</th>
              <th class="text-end">Unit</th>
              <th class="text-end">Kabel Haspel</th>
              <th class="text-end">Stok Qty</th>
              <th style="width:100px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lokasi as $loc)
            <tr>
              <td><span style="font-weight:500;">{{ $loc->nama_lokasi }}</span></td>
              <td>
                @if($loc->jenis === 'gudang_utama')
                  <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Gudang Utama</span>
                @elseif($loc->jenis === 'pop_distribusi')
                  <span class="badge-status badge-active">POP Distribusi</span>
                @else
                  <span class="badge-status badge-inactive">{{ $loc->jenis }}</span>
                @endif
              </td>
              <td>
                @if($loc->picUser)
                  <span style="font-size:.78rem;font-weight:500;color:var(--blue);">{{ $loc->picUser->name }}</span>
                @elseif($loc->pic_nama)
                  {{ $loc->pic_nama }}
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </td>
              <td class="text-end">{{ number_format($loc->inv_units_count ?? 0) }}</td>
              <td class="text-end">{{ number_format($loc->inv_kabels_count ?? 0) }}</td>
              <td class="text-end">{{ number_format($loc->inv_qty_stocks_count ?? 0) }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.inventory.lokasi.edit', $loc) }}" class="nk-action-btn edit" title="Edit">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="{{ route('admin.inventory.lokasi.destroy', $loc) }}" method="POST" class="m-0"
                        onsubmit="return confirm('Hapus lokasi ini?')">
                    @csrf @method('DELETE')
                    <button class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="7">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-map'></i></div>
                <div class="empty-state-title">Belum ada lokasi</div>
                <div class="empty-state-desc">Mulai tambahkan lokasi gudang pertama</div>
              </div>
            </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($lokasi->hasPages())
    <div class="ms-panel-footer">{{ $lokasi->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
@endsection
