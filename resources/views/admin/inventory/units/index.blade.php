@extends('layouts.app')
@section('title', 'Unit Perangkat (SN)')

@section('content')
<div class="ms-page nk-list-page inv-units-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-chip'></i> Inventaris</div>
      <h1 class="ms-page-title">Unit Perangkat (SN)</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.units.create') }}" class="ms-btn"><i class='bx bx-plus'></i> Tambah Unit</a>
    </div>
  </div>



  <div class="ms-stat-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1rem;">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-primary"><i class='bx bx-chip'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Unit</div>
        <div class="ms-stat-value">{{ number_format($total_unit ?? 0) }}</div>
        <div class="ms-stat-meta">semua status</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success"><i class='bx bx-buildings'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Di Gudang</div>
        <div class="ms-stat-value">{{ number_format($total_gudang ?? 0) }}</div>
        <div class="ms-stat-meta">siap pakai</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info"><i class='bx bx-wifi'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Terpasang</div>
        <div class="ms-stat-value">{{ number_format($total_terpasang ?? 0) }}</div>
        <div class="ms-stat-meta">di lokasi pelanggan</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-danger"><i class='bx bx-error-circle'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Rusak</div>
        <div class="ms-stat-value">{{ number_format($total_rusak ?? 0) }}</div>
        <div class="ms-stat-meta">perlu tindak lanjut</div>
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Daftar Unit</h5>
        <div class="ms-panel-subtitle">Unit perangkat dengan serial number</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> {{ $units->total() }} unit</span>
      </div>
    </div>

    <div class="ms-toolbar">
      <div class="ms-toolbar-left">
        <form method="GET" action="{{ route('admin.inventory.units.index') }}" class="ms-filter-form">
          <input type="text" name="search" value="{{ request('search') }}"
                 placeholder="Cari SN / MAC..." class="form-control form-control-sm">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach($status_options as $val => $label)
              <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <select name="lokasi_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Lokasi</option>
            @foreach($lokasi_list as $loc)
              <option value="{{ $loc->id }}" {{ request('lokasi_id') == $loc->id ? 'selected' : '' }}>
                {{ $loc->nama_lokasi }}
              </option>
            @endforeach
          </select>
          <button type="submit" class="ms-btn-secondary ms-btn-sm"><i class='bx bx-search'></i></button>
          @if(request()->anyFilled(['search','status','lokasi_id']))
          <a href="{{ route('admin.inventory.units.index') }}" class="ms-btn-ghost ms-btn-sm">Reset Filter</a>
          @endif
        </form>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Serial Number</th>
              <th>MAC Address</th>
              <th>Barang</th>
              <th>Status</th>
              <th>Lokasi</th>
              <th>Penanggung Jawab</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($units as $unit)
            <tr>
              <td><code>{{ $unit->serial_number }}</code></td>
              <td>
                @if($unit->mac_address)
                  <code>{{ $unit->mac_address }}</code>
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </td>
              <td>
                <div>{{ $unit->masterBarang->merek ?? '-' }} {{ $unit->masterBarang->tipe ?? '' }}</div>
                <div style="font-size:.78rem;color:var(--txt-3)">{{ $unit->masterBarang->kategori->nama ?? '' }}</div>
              </td>
              <td>
                @php $st = $unit->status ?? ''; @endphp
                @if($st === 'gudang')
                  <span class="badge-status badge-active">Gudang</span>
                @elseif($st === 'terpasang')
                  <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Terpasang</span>
                @elseif($st === 'dibawa_teknisi')
                  <span class="badge-status badge-pending">Teknisi</span>
                @elseif($st === 'rusak')
                  <span class="badge-status badge-inactive">Rusak</span>
                @elseif($st === 'rma')
                  <span class="badge-status badge-inactive">RMA</span>
                @elseif($st === 'terjual')
                  <span class="badge-status badge-inactive">Terjual</span>
                @elseif($st === 'hilang')
                  <span class="badge-status badge-inactive">Hilang</span>
                @else
                  <span class="badge-status badge-inactive">{{ $st }}</span>
                @endif
              </td>
              <td>{{ $unit->lokasi->nama_lokasi ?? '-' }}</td>
              <td>{{ $unit->penanggung_jawab ?? '-' }}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                    Opsi
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="{{ route('admin.inventory.units.show', $unit) }}"><i class='bx bx-show'></i> Detail</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.inventory.units.edit', $unit) }}"><i class='bx bx-edit'></i> Edit Unit</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form action="{{ route('admin.inventory.units.destroy', $unit) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus unit ini?')">
                        @csrf @method('DELETE')
                        <button class="dropdown-item text-danger"><i class='bx bx-trash' style="color:var(--red);"></i> Hapus</button>
                      </form>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="7">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-chip'></i></div>
                <div class="empty-state-title">Belum ada unit</div>
                <div class="empty-state-desc">Mulai tambahkan unit perangkat pertama</div>
              </div>
            </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($units->hasPages())
    <div class="ms-panel-footer">{{ $units->withQueryString()->links() }}</div>
    @endif
  </div>
</div>
@endsection
