@extends('layouts.app')
@section('title', 'Stok Kabel (Haspel)')

@section('content')
<div class="ms-page nk-list-page inv-kabel-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-transfer'></i> Inventaris</div>
      <h1 class="ms-page-title">Stok Kabel Haspel</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.kabel.create') }}" class="ms-btn"><i class='bx bx-plus'></i> Tambah Kabel</a>
    </div>
  </div>



  <div class="ms-stat-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1rem;">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info"><i class='bx bx-transfer'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Haspel</div>
        <div class="ms-stat-value">{{ number_format($total_haspel ?? 0) }}</div>
        <div class="ms-stat-meta">haspel terdaftar</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success"><i class='bx bx-ruler'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Sisa Meter</div>
        <div class="ms-stat-value">{{ number_format($total_sisa ?? 0, 1) }}</div>
        <div class="ms-stat-meta">meter tersisa</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-warning"><i class='bx bx-dollar-circle'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Nilai Kabel</div>
        <div class="ms-stat-value">Rp {{ number_format(($total_nilai ?? 0) / 1000000, 1) }}M</div>
        <div class="ms-stat-meta">estimasi nilai stok</div>
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Daftar Kabel Haspel</h5>
        <div class="ms-panel-subtitle">Stok kabel fiber dan copper per haspel</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> {{ $kabels->total() }} haspel</span>
      </div>
    </div>

    <div class="ms-toolbar">
      <div class="ms-toolbar-left">
        <form method="GET" action="{{ route('admin.inventory.kabel.index') }}" class="ms-filter-form">
          <input type="text" name="search" value="{{ request('search') }}"
                 placeholder="Cari ID haspel..." class="form-control form-control-sm">
          <select name="lokasi_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Lokasi</option>
            @foreach($lokasi_list as $loc)
              <option value="{{ $loc->id }}" {{ request('lokasi_id') == $loc->id ? 'selected' : '' }}>
                {{ $loc->nama_lokasi }}
              </option>
            @endforeach
          </select>
          <button type="submit" class="ms-btn-secondary ms-btn-sm"><i class='bx bx-search'></i></button>
          @if(request()->anyFilled(['search','lokasi_id']))
          <a href="{{ route('admin.inventory.kabel.index') }}" class="ms-btn-ghost ms-btn-sm">Reset Filter</a>
          @endif
        </form>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>ID Haspel</th>
              <th>Jenis Kabel</th>
              <th class="text-end">Panjang Awal (m)</th>
              <th class="text-end">Sisa (m)</th>
              <th style="min-width:120px">Penggunaan</th>
              <th class="text-end">Nilai/m (Rp)</th>
              <th>Lokasi</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kabels as $kabel)
            @php
              $pct = $kabel->panjang_awal > 0
                ? round((($kabel->panjang_awal - ($kabel->sisa_meter ?? $kabel->panjang_awal)) / $kabel->panjang_awal) * 100)
                : 0;
              $barColor = $pct >= 80 ? 'var(--red,#ef4444)' : ($pct >= 50 ? 'var(--orange,#f59e0b)' : 'var(--green)');
            @endphp
            <tr>
              <td><code>{{ $kabel->id_haspel }}</code></td>
              <td>{{ $kabel->masterBarang->merek ?? '' }} {{ $kabel->masterBarang->tipe ?? $kabel->jenis_kabel ?? '-' }}</td>
              <td class="text-end">{{ number_format($kabel->panjang_awal, 1) }}</td>
              <td class="text-end">{{ number_format($kabel->sisa_meter ?? $kabel->panjang_awal, 1) }}</td>
              <td>
                <div style="height:6px;border-radius:999px;background:var(--border);overflow:hidden;">
                  <div style="width:{{ $pct }}%;height:100%;background:{{ $barColor }};border-radius:999px;"></div>
                </div>
                <div style="font-size:.72rem;color:var(--txt-3);margin-top:2px">{{ $pct }}% terpakai</div>
              </td>
              <td class="text-end">
                @if($kabel->nilai_per_meter)
                  {{ number_format($kabel->nilai_per_meter, 0, ',', '.') }}
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </td>
              <td>{{ $kabel->lokasi->nama_lokasi ?? '-' }}</td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.inventory.kabel.show', $kabel) }}" class="nk-action-btn view" title="Detail">
                    <i class='bx bx-show'></i>
                  </a>
                  <a href="{{ route('admin.inventory.kabel.edit', $kabel) }}" class="nk-action-btn edit" title="Edit">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="{{ route('admin.inventory.kabel.destroy', $kabel) }}" method="POST" class="m-0"
                        onsubmit="return confirm('Hapus haspel ini?')">
                    @csrf @method('DELETE')
                    <button class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="8">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-transfer'></i></div>
                <div class="empty-state-title">Belum ada kabel haspel</div>
                <div class="empty-state-desc">Mulai tambahkan haspel pertama</div>
              </div>
            </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($kabels->hasPages())
    <div class="ms-panel-footer">{{ $kabels->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
@endsection
