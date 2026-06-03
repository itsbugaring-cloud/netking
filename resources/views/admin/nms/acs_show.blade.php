@extends('layouts.app')
@section('title', 'ONT Detail — ' . ($device['ssid'] ?: $device['serial']))

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1">
            <i class='bx bx-chip me-2' style="color:var(--orange);"></i>ONT Detail
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.acs.index') }}">ACS Devices</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($device['serial'], 24) }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.acs.refresh', rawurlencode($device['id'])) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class='bx bx-refresh me-1'></i> Refresh Data
            </button>
        </form>
        <form method="POST" action="{{ route('admin.acs.reboot', rawurlencode($device['id'])) }}" data-confirm="Yakin reboot ONT ini?">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class='bx bx-reset me-1'></i> Reboot
            </button>
        </form>
        <a href="{{ $genieacsUrl }}/devices/{{ rawurlencode($device['id']) }}" target="_blank"
            class="btn btn-sm btn-outline-secondary">
            <i class='bx bx-link-external me-1'></i> GenieACS
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

{{-- Status Hero --}}
<div class="card mb-4" style="border-left:3px solid {{ $device['online'] ? 'var(--green)' : 'var(--red)' }};">
    <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;border-radius:12px;background:{{ $device['online'] ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.12)' }};display:flex;align-items:center;justify-content:center;">
                    <i class='bx {{ $device["online"] ? "bx-wifi" : "bx-wifi-off" }}' style="font-size:1.5rem;color:{{ $device['online'] ? 'var(--green)' : 'var(--red)' }};"></i>
                </div>
                <div>
                    <h5 class="mb-0" style="font-weight:700;">{{ $device['ssid'] ?: $device['serial'] }}</h5>
                    <div style="font-size:.8rem;color:var(--text-muted);">
                        {{ $device['manufacturer'] }} {{ $device['model'] }}
                        <span class="mx-1">•</span>
                        <code style="font-size:.75rem;background:rgba(249,115,22,.08);color:var(--orange);padding:1px 5px;border-radius:3px;">{{ $device['serial'] }}</code>
                    </div>
                </div>
            </div>
            <div class="text-end">
                @if($device['online'])
                <span class="badge-status badge-active" style="font-size:.8rem;padding:4px 12px;">
                    <i class='bx bxs-circle bx-flashing' style="font-size:.45rem;margin-right:4px;vertical-align:middle;"></i>Online
                </span>
                @else
                <span class="badge-status badge-inactive" style="font-size:.8rem;padding:4px 12px;">Offline</span>
                @endif
                <div style="font-size:.7rem;color:var(--text-muted);margin-top:4px;">
                    <i class='bx bx-time-five me-1'></i>{{ $device['last_seen'] }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--orange);">
            <div class="card-body py-3 d-flex align-items-center gap-2">
                <div class="stat-icon-sm" style="background:rgba(249,115,22,.12);color:var(--orange);">
                    <i class='bx bx-globe'></i>
                </div>
                <div>
                    <div class="stat-label">WAN IP</div>
                    <code style="font-size:.8rem;">{{ $device['wan_ip'] ?? '—' }}</code>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--blue);">
            <div class="card-body py-3 d-flex align-items-center gap-2">
                <div class="stat-icon-sm" style="background:rgba(37,99,235,.12);color:var(--blue);">
                    <i class='bx bx-code-block'></i>
                </div>
                <div>
                    <div class="stat-label">Firmware</div>
                    <div style="font-size:.8rem;font-weight:600;">{{ $device['firmware'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-left:3px solid var(--green);">
            <div class="card-body py-3 d-flex align-items-center gap-2">
                <div class="stat-icon-sm" style="background:rgba(34,197,94,.12);color:var(--green);">
                    <i class='bx bx-time'></i>
                </div>
                <div>
                    <div class="stat-label">Uptime</div>
                    @php
                        $uptime = $device['uptime'] ?? 0;
                        if ($uptime > 86400) { $uptimeStr = number_format($uptime / 86400, 1) . ' days'; }
                        elseif ($uptime > 3600) { $uptimeStr = number_format($uptime / 3600, 1) . ' hours'; }
                        elseif ($uptime > 0) { $uptimeStr = number_format($uptime / 60) . ' min'; }
                        else { $uptimeStr = '—'; }
                    @endphp
                    <div style="font-size:.8rem;font-weight:600;">{{ $uptimeStr }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card" style="border-left:3px solid #a855f7;">
            <div class="card-body py-3 d-flex align-items-center gap-2">
                <div class="stat-icon-sm" style="background:rgba(168,85,247,.12);color:#a855f7;">
                    <i class='bx bx-tag'></i>
                </div>
                <div>
                    <div class="stat-label">Tags</div>
                    <div>
                        @if(!empty($device['tags']))
                            @foreach($device['tags'] as $tag)
                            <span class="acs-tag">{{ $tag }}</span>
                            @endforeach
                        @else
                            <span style="font-size:.75rem;color:var(--text-muted);">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Device Info --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title mb-0"><i class='bx bx-info-circle me-2' style="color:var(--blue);"></i>Device Information</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th style="width:40%;font-size:.8rem;padding:.6rem .75rem;">Serial Number</th>
                        <td><code style="font-size:.8rem;">{{ $device['serial'] }}</code></td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">Device ID</th>
                        <td style="font-size:.75rem;word-break:break-all;color:var(--text-muted);">{{ $device['id'] }}</td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">Manufacturer</th>
                        <td style="font-size:.8125rem;">{{ $device['manufacturer'] }}</td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">Model</th>
                        <td style="font-size:.8125rem;">{{ $device['model'] }}</td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">Product Class</th>
                        <td style="font-size:.8125rem;">{{ $device['product_class'] ?? $device['model'] }}</td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">Firmware</th>
                        <td><code style="font-size:.75rem;">{{ $device['firmware'] ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">WAN IP</th>
                        <td><code style="font-size:.75rem;background:rgba(249,115,22,.08);color:var(--orange);padding:1px 5px;border-radius:3px;">{{ $device['wan_ip'] ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <th style="font-size:.8rem;padding:.6rem .75rem;">Last Inform</th>
                        <td style="font-size:.8125rem;">{{ $device['last_seen'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- WiFi Management --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <span class="card-title mb-0"><i class='bx bx-wifi me-2' style="color:var(--green);"></i>WiFi 2.4GHz Management</span>
            </div>
            <div class="card-body">
                {{-- Current SSID display --}}
                <div class="mb-3 p-3" style="background:rgba(34,197,94,.06);border:1px solid rgba(34,197,94,.15);border-radius:.5rem;">
                    <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Current SSID</div>
                    <div style="font-size:1.1rem;font-weight:700;">
                        <i class='bx bx-wifi me-1' style="color:var(--green);"></i>{{ $device['ssid'] ?? '—' }}
                    </div>
                    <div style="font-size:.7rem;color:var(--text-muted);margin-top:4px;">
                        <i class='bx bx-lock-alt me-1'></i>Password: <em>Protected (tidak dapat dibaca dari TR-069)</em>
                    </div>
                </div>

                {{-- Update Form --}}
                <form method="POST" action="{{ route('admin.acs.updateSsid', rawurlencode($device['id'])) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem;font-weight:600;">
                            <i class='bx bx-edit me-1'></i>Ganti SSID Name
                        </label>
                        <input type="text" name="ssid" class="form-control form-control-sm"
                            value="{{ $device['ssid'] ?? '' }}" maxlength="32" required
                            placeholder="Masukkan SSID baru...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem;font-weight:600;">
                            <i class='bx bx-key me-1'></i>Password Baru
                            <small style="color:var(--text-muted);font-weight:400;">(opsional, min 8 karakter)</small>
                        </label>
                        <input type="text" name="password" class="form-control form-control-sm"
                            placeholder="Kosongkan jika tidak ingin mengubah" minlength="8" maxlength="63">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class='bx bx-save me-1'></i> Update WiFi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Raw Data --}}
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between" style="cursor:pointer;" onclick="document.getElementById('raw-data').classList.toggle('d-none'); this.querySelector('.toggle-icon').classList.toggle('bx-chevron-down'); this.querySelector('.toggle-icon').classList.toggle('bx-chevron-up');">
        <span class="card-title mb-0">
            <i class='bx bx-code-alt me-2' style="color:var(--text-muted);"></i>Raw GenieACS Data
        </span>
        <i class='bx bx-chevron-down toggle-icon' style="font-size:1.2rem;color:var(--text-muted);"></i>
    </div>
    <div class="card-body d-none" id="raw-data">
        <pre style="font-size:.72rem;max-height:400px;overflow:auto;background:var(--bg-body);padding:1rem;border-radius:.5rem;border:1px solid var(--border-color);color:var(--text-primary);">{{ json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
</div>

<style>
    .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
    .stat-icon-sm {
        width: 34px; height: 34px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: 1rem;
    }
    .stat-label { font-size:.65rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); margin-bottom:1px; }
    .acs-tag {
        background: rgba(37,99,235,.1); color: var(--blue);
        border-radius: 4px; padding: 1px 6px; font-size: .65rem; font-weight: 500;
    }
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