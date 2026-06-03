@extends('layouts.app')

@section('title', 'Internet Packages')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-package me-2' style="color:var(--orange);"></i>Internet Packages</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Packages</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.packages.sync-mikrotik') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class='bx bx-refresh me-1'></i> Sync from MikroTik
            </button>
        </form>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary btn-sm">
            <i class='bx bx-plus me-1'></i> Add New Package
        </a>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success mb-3" style="border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="alert mb-3" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
</div>
@endif

{{-- Stats --}}
@php
    $totalPkg = $packages->count();
    $activePkg = $packages->where('is_active', true)->count();
    $totalCust = $packages->sum('customers_count');
    $totalMRR = $packages->sum(function($p) { return $p->is_active ? $p->price * $p->customers_count : 0; });
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--blue);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(37,99,235,.12);color:var(--blue);">
                    <i class='bx bx-package' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Total Packages</div>
                    <div class="stat-value">{{ $totalPkg }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--green);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(34,197,94,.12);color:var(--green);">
                    <i class='bx bx-check-shield' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Active</div>
                    <div class="stat-value" style="color:var(--green);">{{ $activePkg }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--orange);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(249,115,22,.12);color:var(--orange);">
                    <i class='bx bx-user-check' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Subscribers</div>
                    <div class="stat-value">{{ $totalCust }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid #a855f7;">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(168,85,247,.12);color:#a855f7;">
                    <i class='bx bx-money' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Est. MRR</div>
                    <div style="font-weight:700;font-size:1rem;">Rp {{ number_format($totalMRR, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Packages Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title mb-0">
            <i class='bx bx-package me-2' style="color:var(--orange);"></i>All Packages
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="packages-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Name / Code</th>
                    <th>Area</th>
                    <th>Speed (DL/UL)</th>
                    <th>Price</th>
                    <th>MikroTik Profile</th>
                    <th>Customers</th>
                    <th style="width:80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                <tr class="{{ !$package->is_active ? 'pppoe-offline-row' : '' }}">
                    <td>
                        @if($package->is_active)
                        <span class="badge-status badge-active">Active</span>
                        @else
                        <span class="badge-status badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:.85rem;">{{ $package->name }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted);">{{ $package->code }}</div>
                    </td>
                    <td>
                        @if($package->area)
                        <span class="badge" style="background:rgba(37,99,235,.12);color:var(--blue);font-size:.7rem;">{{ $package->area->name }}</span>
                        @else
                        <span style="font-size:.7rem;color:var(--text-muted);">Global</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:.8rem;">
                            <i class='bx bx-down-arrow-alt' style="color:var(--green);font-size:.7rem;"></i>
                            <strong>{{ $package->speed_down }}</strong> Mbps
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);">
                            <i class='bx bx-up-arrow-alt' style="color:var(--blue);font-size:.7rem;"></i>
                            {{ $package->speed_up }} Mbps
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:.85rem;">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                        <div style="font-size:.65rem;color:var(--text-muted);">/ month</div>
                    </td>
                    <td>
                        @if($package->mikrotik_profile)
                        <code style="font-size:.75rem;background:rgba(249,115,22,.08);color:var(--orange);padding:2px 6px;border-radius:3px;">{{ $package->mikrotik_profile }}</code>
                        @else
                        <span style="font-size:.75rem;color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:rgba(37,99,235,.12);color:var(--blue);font-weight:600;">
                            {{ $package->customers_count }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-icon btn-sm"
                                title="Edit" style="background:rgba(37,99,235,.1);color:var(--blue);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                <i class='bx bx-edit-alt' style="font-size:.85rem;"></i>
                            </a>
                            <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="d-inline" data-confirm="Delete this package? This cannot be undone if customers are assigned.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-sm" title="Delete"
                                    style="background:rgba(239,68,68,.1);color:var(--red);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                    <i class='bx bx-trash' style="font-size:.85rem;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4" style="color:var(--text-muted);">
                        <i class='bx bx-package' style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                        No packages found. <a href="{{ route('admin.packages.create') }}">Create your first package</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
    .stat-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .stat-label { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:2px; }
    .stat-value { font-size:1.4rem; font-weight:700; line-height:1.2; }
    .pppoe-offline-row { opacity: .5; }
    .pppoe-offline-row:hover { opacity: 1; }
</style>
@endsection
@section('scripts')
<script>
    $(function() {
        $('#packages-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>><rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            pageLength: 25,
            order: [[1, 'asc']],
            language: {
                search: '', searchPlaceholder: 'Search packages...',
                lengthMenu: 'Show _MENU_', info: '_START_-_END_ of _TOTAL_',
                paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
            },
            columnDefs: [{ orderable: false, targets: [7] }]
        });
        $('form[data-confirm]').on('submit', function(e) {
            if (!confirm($(this).data('confirm'))) e.preventDefault();
        });
    });
</script>
@endsection