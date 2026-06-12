@extends('layouts.app')
@section('title', 'Paket Internet')

@section('content')
<div class="ms-page nk-list-page packages-index-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-package'></i> Katalog Layanan</div>
        <h1 class="ms-page-title">Paket Internet</h1>
    </div>
    <div class="ms-page-actions">
        <form method="POST" action="{{ route('admin.packages.sync-mikrotik') }}">
            @csrf
            <button type="submit" class="ms-btn-secondary">
                <i class='bx bx-refresh'></i> Sinkronisasi dari MikroTik
            </button>
        </form>
        <a href="{{ route('admin.packages.create') }}" class="ms-btn">
            <i class='bx bx-plus'></i> Tambah Paket Baru
        </a>
    </div>
</div>

@if(isset($areas) && $areas->isNotEmpty())
<div class="ms-panel mb-3">
    <div class="ms-panel-body py-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <label for="area_id" style="font-size:.82rem;color:var(--txt-3);font-weight:600;">Filter Area</label>
            <select name="area_id" id="area_id" class="form-select" data-hide-search style="max-width:280px;">
                <option value="">Semua Area</option>
                @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="ms-btn-secondary">Terapkan</button>
            @if(request()->filled('area_id'))
            <a href="{{ route('admin.packages.index') }}" class="ms-btn-ghost">Reset</a>
            @endif
        </form>
    </div>
</div>
@endif



@php
    $totalPkg = $packages->count();
    $activePkg = $packages->where('is_active', true)->count();
    $totalCust = $packages->sum('customers_count');
    $totalMRR = $packages->sum(function($p) { return $p->is_active ? $p->price * $p->customers_count : 0; });
@endphp
<div class="ms-stat-grid mb-4">
    <div class="ms-stat-card" style="--stat-accent:var(--blue);--stat-bg:color-mix(in srgb,var(--blue) 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-package'></i></div>
        <div><div class="ms-stat-label">Total Paket</div><div class="ms-stat-value">{{ $totalPkg }}</div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:var(--green);--stat-bg:color-mix(in srgb,var(--green) 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-check-circle'></i></div>
        <div><div class="ms-stat-label">Aktif</div><div class="ms-stat-value" style="color:var(--green);">{{ $activePkg }}</div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:var(--orange,#f97316);--stat-bg:color-mix(in srgb,var(--orange,#f97316) 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-user-check'></i></div>
        <div><div class="ms-stat-label">Pelanggan</div><div class="ms-stat-value">{{ $totalCust }}</div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#a855f7;--stat-bg:color-mix(in srgb,#a855f7 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-money'></i></div>
        <div><div class="ms-stat-label">Estimasi MRR</div><div class="ms-stat-value" style="font-size:1rem;">Rp {{ number_format($totalMRR, 0, ',', '.') }}</div></div>
    </div>
</div>

<div class="ms-panel">
    <div class="ms-panel-head d-flex align-items-center justify-content-between">
        <span class="ms-panel-title">
            <i class='bx bx-package me-2' style="color:var(--orange,#f97316);"></i>Semua Paket
        </span>
    </div>
    <div class="ms-table-shell">
    <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
            <i class='bx bx-search'></i>
            <input type="text" id="pkg-search" class="nk-search-input" placeholder="Cari paket...">
        </div>
        
    </div>
    <div class="table-responsive">
        <table class="table table-flat mb-0" id="packages-table" style="min-width:1260px;">
            <thead>
                <tr>
                    <th style="min-width:120px;">Status</th>
                    <th style="min-width:220px;">Nama / Kode</th>
                    <th style="min-width:170px;">Area</th>
                    <th style="min-width:170px;">Kecepatan (DL/UL)</th>
                    <th style="min-width:140px;">Harga</th>
                    <th style="min-width:180px;">Profil MikroTik</th>
                    <th style="min-width:110px;">Pelanggan</th>
                    <th style="min-width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                <tr class="{{ !$package->is_active ? 'pppoe-offline-row' : '' }}">
                    <td>
                        @if($package->is_active)
                        <span class="badge-status badge-active">Aktif</span>
                        @else
                        <span class="badge-status badge-inactive">Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:.85rem;">{{ $package->name }}</div>
                        <div style="font-size:.7rem;color:var(--txt-3);">{{ $package->code }}</div>
                    </td>
                    <td>
                        @if($package->area)
                        <span class="badge" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);font-size:.7rem;">{{ $package->area->name }}</span>
                        @else
                        <span style="font-size:.7rem;color:var(--txt-3);">Global</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:.8rem;">
                            <i class='bx bx-down-arrow-alt' style="color:var(--green);font-size:.7rem;"></i>
                            <strong>{{ $package->speed_down }}</strong> Mbps
                        </div>
                        <div style="font-size:.75rem;color:var(--txt-3);">
                            <i class='bx bx-up-arrow-alt' style="color:var(--blue);font-size:.7rem;"></i>
                            {{ $package->speed_up }} Mbps
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:.85rem;">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                        <div style="font-size:.65rem;color:var(--txt-3);">/ bulan</div>
                    </td>
                    <td>
                        @if($package->mikrotik_profile)
                        <code>{{ $package->mikrotik_profile }}</code>
                        @else
                        <span style="font-size:.75rem;color:var(--txt-3);">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);font-weight:600;font-size:.78rem;padding:2px 8px;border-radius:4px;">
                            {{ $package->customers_count }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:6px;font-size:0.8rem;padding:0.25rem 0.5rem;background:var(--surface);border:1px solid var(--border);">
                                Opsi
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="{{ route('admin.packages.edit', $package) }}"><i class='bx bx-edit-alt'></i> Edit Paket</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="m-0" data-confirm="Hapus paket ini?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class='bx bx-trash' style="color:var(--red);"></i> Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4" style="color:var(--txt-3);">
                        <i class='bx bx-package' style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                        Belum ada paket. <a href="{{ route('admin.packages.create') }}">Buat paket pertama</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
</div>

<style>
    .pppoe-offline-row { opacity: .5; }
    .pppoe-offline-row:hover { opacity: 1; }
</style>
@endsection

@section('scripts')
<script>
    $(function() {
        var table = $('#packages-table').DataTable({
            dom: 'rt<"row align-items-center mt-3"<"col-sm-12 col-md-4"i><"col-sm-12 col-md-4 d-flex justify-content-center"l><"col-sm-12 col-md-4 d-flex justify-content-end"p>>',
            pageLength: 25,
            autoWidth: false,
            scrollX: true,
            order: [[1, 'asc']],
            language: {
                info: 'Menampilkan <b>_START_</b> hingga <b>_END_</b> dari <b>_TOTAL_</b> hasil',
        lengthMenu: '_MENU_ per hal',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Tidak ditemukan',
                paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
            },
            columnDefs: [{ orderable: false, targets: [7] }]
        });
        $('#pkg-search').on('input', function() { table.search(this.value).draw(); });
        
        $('form[data-confirm]').on('submit', function(e) {
            if (!confirm($(this).data('confirm'))) e.preventDefault();
        });
    });
</script>
@endsection
