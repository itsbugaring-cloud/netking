@extends('layouts.app')
@section('title', 'NMS Dashboard')

@section('content')

<div class="page-header mb-4 d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><i class='bx bx-pulse me-2' style="color:var(--orange);"></i>Network Monitoring</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">NMS Dashboard</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="live-dot" style="width:8px;height:8px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-green 2s infinite;"></span>
        <span style="font-size:.75rem;color:var(--text-muted);">Auto-refresh 30s</span>
    </div>
</div>

<!-- NMS Stat Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">OLTs</div>
      <div class="stat-value" id="nms-olts">{{ $stats['olt_count'] }}</div>
      <div class="stat-change neutral">Managed Devices</div>
    </div>
    <div class="stat-icon si-orange"><i class='bx bx-server'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">ONTs Online</div>
      <div class="stat-value" id="nms-ont-online" style="color:var(--green);">{{ $stats['ont_online'] }}</div>
      <div class="stat-change up">↑ of {{ $stats['ont_total'] }} total</div>
    </div>
    <div class="stat-icon si-green"><i class='bx bx-check-circle'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">ONTs Offline</div>
      <div class="stat-value" id="nms-ont-offline" style="color:var(--red);">{{ $stats['ont_offline'] }}</div>
      <div class="stat-change down">↓ Unreachable</div>
    </div>
    <div class="stat-icon si-red"><i class='bx bx-error-circle'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">ACS Devices</div>
      <div class="stat-value"><span id="nms-acs-online" style="color:var(--green);">{{ $stats['acs_online'] }}</span> <span style="font-size:.8rem;color:var(--text-muted);">/ {{ $stats['acs_total'] }}</span></div>
      <div class="stat-change up">↑ GenieACS Online</div>
    </div>
    <div class="stat-icon si-blue"><i class='bx bx-chip'></i></div>
  </div>
</div>

<!-- OLT Health Cards -->
<div class="card mb-3">
    <div class="card-header">
        <span class="card-title"><i class='bx bx-server me-2' style="color:var(--orange);"></i>OLT Health Overview</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>OLT Name</th>
                        <th>IP</th>
                        <th>Brand</th>
                        <th>ONTs Online</th>
                        <th>ONTs Offline</th>
                        <th>Health</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($olts as $olt)
                    @php
                        $onlineCount = $olt->onts()->where('status', 'online')->count();
                        $totalCount = $olt->onts()->count();
                        $offlineCount = $totalCount - $onlineCount;
                        $healthPct = $totalCount > 0 ? round(($onlineCount / $totalCount) * 100) : 0;
                        $healthColor = $healthPct > 90 ? 'var(--green)' : ($healthPct > 70 ? 'var(--orange)' : 'var(--red)');
                    @endphp
                    <tr>
                        <td style="font-weight:600;">
                            <a href="{{ route('admin.olts.show', $olt) }}" style="color:inherit;text-decoration:none;">
                                {{ $olt->name }}
                            </a>
                        </td>
                        <td><code style="font-size:.75rem;">{{ $olt->ip_address }}</code></td>
                        <td style="font-size:.8rem;">{{ $olt->brand }} {{ $olt->model }}</td>
                        <td>
                            <span style="color:var(--green);font-weight:600;">{{ $onlineCount }}</span>
                        </td>
                        <td>
                            <span style="color:{{ $offlineCount > 0 ? 'var(--red)' : 'var(--text-muted)' }};font-weight:{{ $offlineCount > 0 ? '600' : '400' }};">{{ $offlineCount }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="flex:1;height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden;">
                                    <div style="width:{{ $healthPct }}%;height:100%;background:{{ $healthColor }};border-radius:3px;transition:width .3s;"></div>
                                </div>
                                <span style="font-size:.7rem;font-weight:600;color:{{ $healthColor }};">{{ $healthPct }}%</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.olts.show', $olt) }}" class="btn btn-sm" style="background:rgba(249,115,22,.1);color:var(--orange);border-radius:6px;padding:2px 8px;">
                                <i class='bx bx-show' style="font-size:.85rem;"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Area / MikroTik Router Status -->
<div class="card mb-3">
    <div class="card-header">
        <span class="card-title"><i class='bx bx-wifi me-2' style="color:var(--blue);"></i>Area / MikroTik Router Status</span>
        <span style="font-size:.7rem;color:var(--text-muted);">Click Test to check connectivity</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Router IP</th>
                        <th>Customers</th>
                        <th>Status</th>
                        <th>Test</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areas as $area)
                    <tr>
                        <td style="font-weight:600;">{{ $area->name }}</td>
                        <td><code style="font-size:.75rem;">{{ $area->router_ip ?? '—' }}</code></td>
                        <td>{{ $area->customers_count }}</td>
                        <td>
                            <span id="router-status-{{ $area->id }}" class="badge-status badge-pending" style="font-size:.7rem;">Unknown</span>
                        </td>
                        <td>
                            @if($area->router_ip)
                            <button class="btn btn-sm" style="background:rgba(37,99,235,.1);color:var(--blue);border-radius:6px;padding:2px 8px;font-size:.75rem;"
                                onclick="testRouter({{ $area->id }})">
                                <i class='bx bx-refresh'></i> Test
                            </button>
                            @else
                            <span style="font-size:.7rem;color:var(--text-muted);">No router</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.3; } }
</style>

@endsection

@section('scripts')
<script>
    function testRouter(areaId) {
        var badge = document.getElementById('router-status-' + areaId);
        badge.className = 'badge-status badge-pending';
        badge.textContent = 'Testing...';

        $.getJSON('{{ route("admin.nms.api-data") }}', { action: 'mikrotik_test', area_id: areaId })
            .done(function(d) {
                if (d.success) {
                    badge.className = 'badge-status badge-active';
                    badge.textContent = '● Online — ' + (d.identity || 'Connected');
                } else {
                    badge.className = 'badge-status badge-inactive';
                    badge.textContent = '○ Offline';
                }
            })
            .fail(function() {
                badge.className = 'badge-status badge-inactive';
                badge.textContent = '○ Error';
            });
    }

    // Auto-refresh NMS stats
    function refreshNmsStats() {
        $.getJSON('{{ route("admin.nms.api-data") }}', { action: 'summary' })
            .done(function(d) {
                $('#nms-ont-online').text(d.ont_online);
                $('#nms-ont-offline').text(d.ont_total - d.ont_online);
                $('#nms-acs-online').text(d.acs_online);
            });
    }
    setInterval(refreshNmsStats, 30000);
</script>
@endsection
