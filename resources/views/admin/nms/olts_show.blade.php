@extends('layouts.app')
@section('title', $olt->name . ' — ONT Inventory')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1">
            <i class='bx bx-server me-2' style="color:var(--orange);"></i>{{ $olt->name }}
            <small style="font-size:.8rem;color:var(--text-muted);">{{ $olt->brand }} {{ $olt->model }}</small>
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.olts.index') }}">OLT Devices</a></li>
                <li class="breadcrumb-item active">{{ $olt->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.olts.sync', $olt) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">
                <i class='bx bx-refresh me-1'></i> Sync ONTs from OLT
            </button>
        </form>
        <form action="{{ route('admin.olts.auto-link', $olt) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary" title="Auto-link unlinked ONTs to customers by area">
                <i class='bx bx-link me-1'></i> Auto-Link
            </button>
        </form>
        <a href="{{ route('admin.olts.edit', $olt) }}" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-edit me-1'></i> Edit
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-3" style="border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert mb-3" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
</div>
@endif
@if(session('info'))
<div class="alert mb-3" style="background:rgba(37,99,235,.1);border:1px solid rgba(37,99,235,.25);color:#60a5fa;border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-info-circle me-2'></i>{{ session('info') }}
</div>
@endif

@php
    $onlinePct = $stats['total'] > 0 ? round($stats['online'] / $stats['total'] * 100) : 0;
@endphp

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--blue);">
            <div class="card-body py-3 text-center">
                <div class="stat-label">Total ONTs</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--green);">
            <div class="card-body py-3 text-center">
                <div class="stat-label">Online</div>
                <div class="stat-value" style="color:var(--green);">{{ $stats['online'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--red);">
            <div class="card-body py-3 text-center">
                <div class="stat-label">Offline</div>
                <div class="stat-value" style="color:var(--red);">{{ $stats['offline'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--orange);">
            <div class="card-body py-3 text-center">
                <div class="stat-label">Linked</div>
                <div class="stat-value">{{ $stats['linked'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card stat-card" style="border-left:3px solid #a855f7;">
            <div class="card-body py-3 text-center">
                <div class="stat-label">Unlinked</div>
                <div class="stat-value" style="color:#a855f7;">{{ $stats['unlinked'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--green);">
            <div class="card-body py-3 text-center">
                <div class="stat-label">IP / Protocol</div>
                <code style="font-size:.75rem;background:rgba(249,115,22,.08);color:var(--orange);padding:2px 6px;border-radius:3px;">{{ $olt->ip_address }}</code>
                <span class="badge ms-1" style="background:rgba(249,115,22,.15);color:var(--orange);font-size:.6rem;text-transform:uppercase;">{{ $olt->preferred_protocol }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ONT Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="card-title mb-0">
            <i class='bx bx-chip me-2' style="color:var(--orange);"></i>ONT Inventory
            <span class="badge ms-1" style="background:rgba(249,115,22,.15);color:var(--orange);font-weight:600;font-size:.7rem;">{{ $stats['total'] }} devices</span>
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center gap-1" style="font-size:.7rem;">
                <span style="width:7px;height:7px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-green 2s infinite;"></span>
                <span style="color:var(--green);">{{ $stats['online'] }} online</span>
            </span>
            <span style="font-size:.7rem;color:var(--text-muted);">{{ $onts->first()?->last_synced_at?->diffForHumans() ?? 'Never synced' }}</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="ont-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Serial Number</th>
                    <th>PON Port</th>
                    <th>Status</th>
                    <th>Rx Power</th>
                    <th>Tx Power</th>
                    <th>Distance</th>
                    <th>Customer</th>
                    <th style="width:60px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($onts as $i => $ont)
                @php
                    $q = $ont->signal_quality ?? 'unknown';
                    $rxColor = match($q) {
                        'excellent' => 'var(--green)',
                        'good' => 'var(--blue)',
                        'fair' => '#eab308',
                        'weak' => 'var(--red)',
                        default => 'var(--text-muted)',
                    };
                @endphp
                <tr class="{{ $ont->status === 'offline' ? 'ont-offline-row' : '' }}">
                    <td style="font-size:.75rem;color:var(--text-muted);">{{ $i + 1 }}</td>
                    <td>
                        <code style="font-size:.75rem;font-weight:600;">{{ $ont->serial_number }}</code>
                    </td>
                    <td>
                        <span style="font-size:.8rem;">{{ $ont->pon_port ?? '—' }}:{{ $ont->olt_port_index ?? '—' }}</span>
                    </td>
                    <td>
                        @if($ont->status === 'online')
                        <span class="badge-status badge-active">
                            <i class='bx bxs-circle bx-flashing' style="font-size:.4rem;margin-right:3px;vertical-align:middle;"></i>Online
                        </span>
                        @elseif($ont->status === 'offline')
                        <span class="badge-status badge-inactive">Offline</span>
                        @else
                        <span class="badge-status" style="background:rgba(148,163,184,.1);color:#94a3b8;">Unknown</span>
                        @endif
                    </td>
                    <td>
                        @if($ont->rx_power !== null)
                        <span style="color:{{ $rxColor }};font-weight:600;font-size:.8rem;">{{ number_format($ont->rx_power, 2) }} dBm</span>
                        @if($q !== 'unknown')
                        <span style="font-size:.55rem;color:{{ $rxColor }};text-transform:uppercase;margin-left:3px;">{{ $q }}</span>
                        @endif
                        @else
                        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($ont->tx_power !== null)
                        <span style="font-size:.8rem;">{{ number_format($ont->tx_power, 2) }} dBm</span>
                        @else
                        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($ont->distance)
                        <span style="font-size:.8rem;">{{ number_format($ont->distance) }}m</span>
                        @else
                        <span style="color:var(--text-muted);font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($ont->customer)
                        <a href="{{ route('admin.customers.show', $ont->customer) }}" style="text-decoration:none;font-size:.8rem;font-weight:500;">
                            <i class='bx bx-user me-1' style="font-size:.7rem;"></i>{{ $ont->customer->name }}
                        </a>
                        @else
                        <form action="{{ route('admin.olts.link-customer', $ont) }}" method="POST" class="d-inline">
                            @csrf
                            <select name="customer_id" class="form-select form-select-sm" style="width:140px;font-size:.7rem;" onchange="this.form.submit()">
                                <option value="">Link customer…</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </form>
                        @endif
                    </td>
                    <td>
                        @if($ont->customer_id)
                        <form action="{{ route('admin.olts.link-customer', $ont) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="customer_id" value="">
                            <button type="submit" class="btn btn-icon btn-sm" title="Unlink"
                                style="background:rgba(234,179,8,.1);color:#eab308;border:none;width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-size:.75rem;">
                                <i class='bx bx-unlink'></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5" style="color:var(--text-muted);">
                        <i class='bx bx-chip' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        <p class="fw-semibold mb-1">No ONTs found</p>
                        <p class="mb-0">Click <strong>"Sync ONTs from OLT"</strong> to fetch the inventory</p>
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
    .stat-label { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:2px; }
    .stat-value { font-size:1.4rem;font-weight:700;line-height:1.2; }
    .ont-offline-row { opacity: .45; }
    .ont-offline-row:hover { opacity: 1; }
    @keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.4; } }
</style>
@endsection
@section('scripts')
<script>
    $(function() {
        if ($('#ont-table tbody tr td[colspan]').length === 0) {
            $('#ont-table').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>><rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                pageLength: 50,
                order: [[3, 'asc'], [2, 'asc']],
                language: {
                    search: '', searchPlaceholder: 'Search ONTs...',
                    lengthMenu: 'Show _MENU_', info: '_START_-_END_ of _TOTAL_',
                    paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
                },
                columnDefs: [{ orderable: false, targets: [8] }]
            });
        }
    });
</script>
@endsection