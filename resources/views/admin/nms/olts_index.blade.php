@extends('layouts.app')
@section('title', 'OLT Devices')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-server me-2' style="color:var(--orange);"></i>OLT Devices</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">OLT Devices</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.olts.create') }}" class="btn btn-primary btn-sm">
        <i class='bx bx-plus me-1'></i> Add OLT
    </a>
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

@php
    $totalOnts = $olts->sum(fn($o) => $o->onts->count());
    $onlineOnts = $olts->sum(fn($o) => $o->onts->where('status','online')->count());
    $offlineOnts = $totalOnts - $onlineOnts;
    $onlinePct = $totalOnts > 0 ? round($onlineOnts / $totalOnts * 100) : 0;
@endphp

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card" style="border-left:3px solid var(--blue);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(37,99,235,.12);color:var(--blue);">
                    <i class='bx bx-server' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Total OLTs</div>
                    <div class="stat-value">{{ $olts->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card" style="border-left:3px solid var(--green);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(34,197,94,.12);color:var(--green);">
                    <i class='bx bx-chip' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Total ONTs</div>
                    <div class="stat-value">{{ $totalOnts }}</div>
                </div>
                <div class="ms-auto text-end">
                    <div style="font-size:.65rem;color:var(--green);font-weight:600;">{{ $onlinePct }}% online</div>
                    <div style="width:60px;height:4px;border-radius:2px;background:rgba(255,255,255,.08);overflow:hidden;">
                        <div style="width:{{ $onlinePct }}%;height:100%;background:var(--green);border-radius:2px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card" style="border-left:3px solid var(--orange);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(249,115,22,.12);color:var(--orange);">
                    <i class='bx bx-signal-5' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Online ONTs</div>
                    <div class="stat-value" style="color:var(--green);">{{ $onlineOnts }}</div>
                </div>
                <div class="ms-auto">
                    <span style="font-size:.75rem;color:var(--red);">{{ $offlineOnts }} offline</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- OLT Cards --}}
<div class="row g-3 mb-4">
    @forelse($olts as $olt)
    @php
        $oltTotal = $olt->onts->count();
        $oltOnline = $olt->onts->where('status','online')->count();
        $oltOffline = $oltTotal - $oltOnline;
        $oltPct = $oltTotal > 0 ? round($oltOnline / $oltTotal * 100) : 0;
    @endphp
    <div class="col-md-6">
        <div class="card olt-card" style="cursor:pointer;" onclick="window.location='{{ route('admin.olts.show', $olt) }}'">
            <div class="card-body py-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="mb-0" style="font-weight:700;">
                            <i class='bx bx-server me-1' style="color:var(--orange);"></i>
                            <a href="{{ route('admin.olts.show', $olt) }}" style="text-decoration:none;color:var(--text-primary);">{{ $olt->name }}</a>
                        </h6>
                        <div style="font-size:.75rem;color:var(--text-muted);">{{ $olt->brand }} {{ $olt->model }}</div>
                    </div>
                    <span class="badge" style="background:rgba(249,115,22,.12);color:var(--orange);font-size:.7rem;text-transform:uppercase;font-weight:600;">{{ $olt->preferred_protocol }}</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-2">
                    <code style="font-size:.75rem;background:rgba(249,115,22,.08);color:var(--orange);padding:2px 8px;border-radius:4px;">{{ $olt->ip_address }}</code>
                    <span style="font-size:.75rem;color:var(--text-muted);">{{ $olt->area->name ?? '—' }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <span style="font-size:.8rem;">
                            <i class='bx bxs-circle' style="font-size:.45rem;color:var(--green);vertical-align:middle;"></i>
                            <strong style="color:var(--green);">{{ $oltOnline }}</strong>
                            <span style="font-size:.7rem;color:var(--text-muted);">online</span>
                        </span>
                        <span style="font-size:.8rem;">
                            <i class='bx bxs-circle' style="font-size:.45rem;color:var(--red);vertical-align:middle;"></i>
                            <strong style="color:var(--red);">{{ $oltOffline }}</strong>
                            <span style="font-size:.7rem;color:var(--text-muted);">offline</span>
                        </span>
                        <span style="font-size:.8rem;">
                            <strong>{{ $oltTotal }}</strong>
                            <span style="font-size:.7rem;color:var(--text-muted);">total</span>
                        </span>
                    </div>
                    <div style="width:80px;height:6px;border-radius:3px;background:rgba(255,255,255,.08);overflow:hidden;">
                        <div style="width:{{ $oltPct }}%;height:100%;background:var(--green);border-radius:3px;transition:width .6s;"></div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="d-flex gap-1 mt-2 pt-2" style="border-top:1px solid var(--border-color);" onclick="event.stopPropagation();">
                    <a href="{{ route('admin.olts.show', $olt) }}" class="btn btn-icon btn-sm" title="View ONTs"
                        style="background:rgba(37,99,235,.1);color:var(--blue);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                        <i class='bx bx-chip' style="font-size:.85rem;"></i>
                    </a>
                    <form action="{{ route('admin.olts.sync', $olt) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-icon btn-sm" title="Sync ONTs"
                            style="background:rgba(34,197,94,.1);color:var(--green);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class='bx bx-refresh' style="font-size:.85rem;"></i>
                        </button>
                    </form>
                    <a href="{{ route('admin.olts.edit', $olt) }}" class="btn btn-icon btn-sm" title="Edit"
                        style="background:rgba(255,255,255,.06);color:var(--text-muted);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                        <i class='bx bx-edit-alt' style="font-size:.85rem;"></i>
                    </a>
                    <form action="{{ route('admin.olts.destroy', $olt) }}" method="POST" class="d-inline"
                        data-confirm="Delete OLT {{ $olt->name }} and all ONT records?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-icon btn-sm" title="Delete"
                            style="background:rgba(239,68,68,.1);color:var(--red);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                            <i class='bx bx-trash' style="font-size:.85rem;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5" style="color:var(--text-muted);">
                <i class='bx bx-server' style="font-size:2.5rem;opacity:.3;"></i>
                <p class="mt-2 mb-1 fw-semibold">No OLT devices yet</p>
                <a href="{{ route('admin.olts.create') }}" class="btn btn-primary btn-sm mt-2">
                    <i class='bx bx-plus me-1'></i> Add your first OLT
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<style>
    .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
    .stat-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .stat-label { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:2px; }
    .stat-value { font-size:1.4rem;font-weight:700;line-height:1.2; }
    .olt-card { transition: transform .15s ease, box-shadow .15s ease; }
    .olt-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
</style>
@endsection
@section('scripts')
<script>
    $(function() {
        $('form[data-confirm]').on('submit', function(e) {
            if (!confirm($(this).data('confirm'))) e.preventDefault();
        });
    });
</script>
@endsection