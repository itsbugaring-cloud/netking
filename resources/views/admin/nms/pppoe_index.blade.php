@extends('layouts.app')
@section('title', 'PPPoE Management')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-wifi me-2' style="color:var(--orange);"></i>PPPoE Management</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">PPPoE</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Area Selector --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form id="area-form" method="GET" action="{{ route('admin.pppoe.index') }}" class="d-flex align-items-end gap-3">
            <div class="flex-fill">
                <label class="form-label fw-500" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);">
                    <i class='bx bx-router me-1'></i>Select Router / Area
                </label>
                <select id="area-select" name="area_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choose Area --</option>
                    @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ $selectedArea?->id == $area->id ? 'selected' : '' }}>
                        {{ $area->name }} &mdash; {{ $area->router_ip }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class='bx bx-refresh me-1'></i> {{ $selectedArea ? 'Refresh' : 'Load' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sel = document.getElementById('area-select');
        if (sel && sel.options.length === 2 && !sel.value) {
            sel.value = sel.options[1].value;
            document.getElementById('area-form').submit();
        }
    });
</script>

@if($error)
<div class="alert d-flex align-items-start gap-2" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;font-size:.875rem;border-radius:.5rem;">
    <i class='bx bx-error-circle mt-1' style="font-size:1.1rem;"></i>
    <div>
        <strong>Cannot Connect to Router</strong><br>
        {{ $error }}<br>
        <small style="color:var(--text-muted);">Check that the MikroTik router is online, API is enabled (port 8728), and credentials are correct in <strong>Areas → Edit</strong>.</small>
    </div>
</div>
@endif

@if($selectedArea && !$error)

@php
    $totalSecrets = count($secrets);
    $activeCount = $activeSessions->count();
    $offlineCount = $totalSecrets - $activeCount;
    $disabledCount = collect($secrets)->where('disabled', 'true')->count();
    $onlinePct = $totalSecrets > 0 ? round($activeCount / $totalSecrets * 100) : 0;
@endphp

{{-- Router Info Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--blue);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(37,99,235,.12);color:var(--blue);">
                    <i class='bx bx-server' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Router</div>
                    <div style="font-weight:700;font-size:.95rem;">{{ $routerInfo['identity'] ?? '-' }}</div>
                    <div style="font-size:.7rem;color:var(--text-muted);">{{ $selectedArea->router_ip }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--orange);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(249,115,22,.12);color:var(--orange);">
                    <i class='bx bx-key' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Total Secrets</div>
                    <div class="stat-value">{{ $totalSecrets }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--green);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(34,197,94,.12);color:var(--green);">
                    <i class='bx bx-check-circle' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Active Sessions</div>
                    <div class="stat-value" style="color:var(--green);">{{ $activeCount }}</div>
                </div>
                <div class="ms-auto text-end">
                    <div style="font-size:.65rem;color:var(--green);font-weight:600;">{{ $onlinePct }}%</div>
                    <div style="width:50px;height:4px;border-radius:2px;background:rgba(255,255,255,.08);overflow:hidden;">
                        <div style="width:{{ $onlinePct }}%;height:100%;background:var(--green);border-radius:2px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left:3px solid var(--red);">
            <div class="card-body py-3 d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(239,68,68,.12);color:var(--red);">
                    <i class='bx bx-x-circle' style="font-size:1.3rem;"></i>
                </div>
                <div>
                    <div class="stat-label">Offline</div>
                    <div class="stat-value" style="color:var(--red);">{{ $offlineCount }}</div>
                </div>
                @if($disabledCount > 0)
                <div class="ms-auto">
                    <span class="badge" style="background:rgba(234,179,8,.15);color:#eab308;font-size:.65rem;">{{ $disabledCount }} disabled</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- PPPoE Secrets Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title mb-0">
            <i class='bx bx-wifi me-2' style="color:var(--orange);"></i>PPPoE Secrets
            <span class="badge ms-1" style="background:rgba(249,115,22,.15);color:var(--orange);font-weight:600;font-size:.7rem;">{{ $selectedArea->name }}</span>
        </span>
        <div class="d-flex gap-2">
            <span class="d-flex align-items-center gap-1 me-2" style="font-size:.7rem;">
                <span style="width:7px;height:7px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-green 2s infinite;"></span>
                <span style="color:var(--green);">{{ $activeCount }} online</span>
            </span>
            <form method="POST" action="{{ route('admin.pppoe.sync-customers') }}" class="d-inline"
                data-confirm="Import semua PPPoE secrets dari MikroTik ke database Customers? User yang sudah ada akan dilewati.">
                @csrf
                <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                <button type="submit" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;">
                    <i class='bx bx-import me-1'></i> Sync ke Customers
                </button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="pppoe-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Profile</th>
                    <th>Caller ID</th>
                    <th>Status</th>
                    <th>IP / Uptime</th>
                    <th>Traffic (RX/TX)</th>
                    <th style="width:90px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($secrets as $secret)
                @php
                $session = $activeSessions->get($secret['name']);
                $isActive = !is_null($session);
                $isDisabled = ($secret['disabled'] ?? 'false') === 'true';
                $fmtBytes = function($bytes) {
                    $bytes = (int)$bytes;
                    if ($bytes >= 1073741824) return number_format($bytes/1073741824, 2).' GB';
                    if ($bytes >= 1048576) return number_format($bytes/1048576, 2).' MB';
                    if ($bytes >= 1024) return number_format($bytes/1024, 2).' KB';
                    return $bytes.' B';
                };
                @endphp
                <tr class="{{ !$isActive && !$isDisabled ? 'pppoe-offline-row' : '' }} {{ $isDisabled ? 'pppoe-disabled-row' : '' }}">
                    <td>
                        <div style="font-weight:600;font-size:.85rem;">{{ $secret['name'] }}</div>
                        @if($secret['comment'] ?? '')
                        <div style="font-size:.7rem;color:var(--text-muted);">
                            <i class='bx bx-user me-1' style="font-size:.6rem;"></i>{{ $secret['comment'] }}
                        </div>
                        @endif
                    </td>
                    <td>
                        <code style="font-size:.75rem;background:rgba(249,115,22,.08);color:var(--orange);padding:2px 6px;border-radius:3px;">{{ $secret['profile'] ?? 'default' }}</code>
                    </td>
                    <td>
                        @if($isActive && ($session['caller-id'] ?? ''))
                        <span style="font-size:.75rem;font-family:monospace;">{{ $session['caller-id'] }}</span>
                        @else
                        <span style="font-size:.75rem;color:var(--text-muted);">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($isDisabled)
                        <span class="badge-status badge-warning">
                            <i class='bx bx-pause-circle' style="font-size:.55rem;margin-right:3px;"></i>Disabled
                        </span>
                        @elseif($isActive)
                        <span class="badge-status badge-active">
                            <i class='bx bxs-circle bx-flashing' style="font-size:.4rem;margin-right:3px;vertical-align:middle;"></i>Online
                        </span>
                        @else
                        <span class="badge-status badge-inactive">Offline</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;">
                        @if($session)
                        <div><i class='bx bx-globe me-1' style="color:var(--blue);font-size:.7rem;"></i>{{ $session['address'] ?? '-' }}</div>
                        <div style="color:var(--text-muted);"><i class='bx bx-time me-1' style="font-size:.7rem;"></i>{{ $session['uptime'] ?? '-' }}</div>
                        @else
                        <span style="color:var(--text-muted);">&mdash;</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;">
                        @if($isActive && $session)
                        <div style="color:var(--green);"><i class='bx bx-down-arrow-alt'></i> {{ $fmtBytes($session['bytes-in'] ?? 0) }}</div>
                        <div style="color:var(--blue);"><i class='bx bx-up-arrow-alt'></i> {{ $fmtBytes($session['bytes-out'] ?? 0) }}</div>
                        @else
                        <span style="color:var(--text-muted);">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('admin.pppoe.toggle') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                                <input type="hidden" name="username" value="{{ $secret['name'] }}">
                                <input type="hidden" name="enable" value="{{ $isDisabled ? '1' : '0' }}">
                                <button type="submit" class="btn btn-icon btn-sm" title="{{ $isDisabled ? 'Enable' : 'Disable' }}"
                                    style="background:{{ $isDisabled ? 'rgba(34,197,94,.1)' : 'rgba(234,179,8,.1)' }};color:{{ $isDisabled ? 'var(--green)' : '#eab308' }};border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                    <i class='bx {{ $isDisabled ? "bx-play-circle" : "bx-pause-circle" }}' style="font-size:.85rem;"></i>
                                </button>
                            </form>
                            @if($isActive)
                            <form method="POST" action="{{ route('admin.pppoe.disconnect') }}" class="d-inline" data-confirm="Disconnect this PPPoE session?">
                                @csrf
                                <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                                <input type="hidden" name="username" value="{{ $secret['name'] }}">
                                <button type="submit" class="btn btn-icon btn-sm" title="Disconnect"
                                    style="background:rgba(239,68,68,.1);color:var(--red);border:none;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;">
                                    <i class='bx bx-link-alt' style="font-size:.85rem;"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4" style="color:var(--text-muted);">
                        <i class='bx bx-wifi-off' style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                        No PPPoE secrets found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

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
    .pppoe-disabled-row { opacity: .35; }
    .pppoe-disabled-row:hover { opacity: .8; }
    @keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.4; } }
</style>
@endsection
@section('scripts')
<script>
    $(function() {
        if (document.getElementById('pppoe-table')) {
            $('#pppoe-table').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>><rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                pageLength: 50,
                order: [[3, 'asc']],
                language: {
                    search: '', searchPlaceholder: 'Search PPPoE...',
                    lengthMenu: 'Show _MENU_', info: '_START_-_END_ of _TOTAL_',
                    paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
                },
                columnDefs: [{ orderable: false, targets: [6] }]
            });
        }
        $('form[data-confirm]').on('submit', function(e) {
            if (!confirm($(this).data('confirm'))) e.preventDefault();
        });
    });
</script>
@endsection