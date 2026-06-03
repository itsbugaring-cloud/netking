@extends('layouts.app')
@section('title', 'Pantau OLT — Semua Perangkat')

@section('content')
<div class="ms-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-broadcast'></i> Inventaris OLT</div>
        <h1 class="ms-page-title">Pantau OLT</h1>
    </div>
    <div class="ms-page-actions">
        <span id="last-refresh" class="ms-chip"></span>
        <span class="ms-kpi-chip is-success">
            <span class="live-dot" style="width:7px;height:7px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-green 2s infinite;"></span>
            Perbarui otomatis 60d
        </span>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="ms-panel h-100">
            <div class="ms-panel-body py-3 text-center">
                <div class="stat-label">Total OLT</div>
                <div class="stat-value" id="g-total-olts">{{ $olts->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ms-panel h-100">
            <div class="ms-panel-body py-3 text-center">
                <div class="stat-label">Total ONT Online</div>
                <div class="stat-value" style="color:var(--green);" id="g-total-online">{{ $globalStats['online'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ms-panel h-100">
            <div class="ms-panel-body py-3 text-center">
                <div class="stat-label">Total ONT Offline</div>
                <div class="stat-value" style="color:var(--red);" id="g-total-offline">{{ $globalStats['offline'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ms-panel h-100">
            <div class="ms-panel-body py-3 text-center">
                <div class="stat-label">Semua ONT</div>
                <div class="stat-value" id="g-total-onts">{{ $globalStats['total'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Grid Kartu OLT --}}
<div class="row g-3" id="olt-cards-grid">
    @foreach($olts as $olt)
    @php
        $s = $oltStats[$olt->id] ?? ['total'=>0,'online'=>0,'offline'=>0];
        $onlinePct = $s['total'] > 0 ? round($s['online'] / $s['total'] * 100) : 0;
        $isHealthy = $onlinePct >= 90;
        $isWarning = $onlinePct >= 50 && $onlinePct < 90;
        $isDanger  = $onlinePct < 50;
        $barColor  = $isHealthy ? 'var(--green)' : ($isWarning ? '#eab308' : 'var(--red)');
        $lastSync  = \App\Models\Ont::where('olt_id', $olt->id)->latest('last_synced_at')->value('last_synced_at');
    @endphp
    <div class="col-md-6 col-xl-4">
        <div class="ms-panel olt-card h-100" data-olt-id="{{ $olt->id }}"
            style="border-left:4px solid {{ $barColor }};transition:transform .2s,box-shadow .2s;">
            <div class="ms-panel-body pb-2">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <a href="{{ route('admin.olts.show', $olt) }}"
                            style="font-weight:700;font-size:.9rem;text-decoration:none;color:inherit;">
                            {{ $olt->name }}
                        </a>
                        <div style="font-size:.7rem;color:var(--text-muted);margin-top:2px;">
                            <code style="color:var(--orange);font-size:.68rem;">{{ $olt->ip_address }}</code>
                            <span class="badge ms-1" style="background:rgba(249,115,22,.15);color:var(--orange);font-size:.6rem;text-transform:uppercase;">
                                {{ $olt->brand }}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:1.6rem;font-weight:800;line-height:1;color:{{ $barColor }};">
                            {{ $onlinePct }}%
                        </div>
                        <div style="font-size:.6rem;color:var(--text-muted);">rasio online</div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div style="height:5px;background:rgba(255,255,255,.08);border-radius:3px;margin-bottom:10px;overflow:hidden;">
                    <div style="width:{{ $onlinePct }}%;height:100%;background:{{ $barColor }};border-radius:3px;transition:width 1s ease;"></div>
                </div>

                {{-- Baris Statistik --}}
                <div class="d-flex gap-3" style="font-size:.75rem;">
                    <span><strong style="color:var(--green);">{{ $s['online'] }}</strong> <span style="color:var(--text-muted);">Online</span></span>
                    <span><strong style="color:var(--red);">{{ $s['offline'] }}</strong> <span style="color:var(--text-muted);">Offline</span></span>
                    <span><strong>{{ $s['total'] }}</strong> <span style="color:var(--text-muted);">Total</span></span>
                </div>

                {{-- Sinkronisasi Terakhir --}}
                <div class="mt-2 d-flex justify-content-between align-items-center">
                    <span style="font-size:.65rem;color:var(--text-muted);">
                        <i class='bx bx-time me-1'></i>
                        Sinkron terakhir: {{ $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Belum pernah' }}
                    </span>
                    <form action="{{ route('admin.olts.sync', $olt) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm" title="Sinkronisasi sekarang"
                            style="background:rgba(249,115,22,.12);border:1px solid rgba(249,115,22,.25);color:var(--orange);font-size:.65rem;padding:2px 8px;">
                            <i class='bx bx-refresh'></i> Sinkron
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Tabel ONT Offline --}}
@if($offlineOnts->isNotEmpty())
<div class="ms-panel mt-4">
    <div class="ms-panel-head d-flex justify-content-between align-items-center">
        <span class="ms-panel-title mb-0" style="color:var(--red);">
            <i class='bx bx-wifi-off me-2'></i>ONT Offline
            <span class="badge ms-1" style="background:rgba(239,68,68,.15);color:var(--red);font-size:.7rem;">{{ $offlineOnts->count() }}</span>
        </span>
    </div>
    <div class="ms-table-shell">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>OLT</th>
                    <th>Serial Number</th>
                    <th>Nama ONT</th>
                    <th>Port PON</th>
                    <th>Pelanggan</th>
                    <th>Sinkron Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($offlineOnts as $ont)
                <tr style="opacity:.85;">
                    <td style="font-size:.75rem;">
                        <a href="{{ route('admin.olts.show', $ont->olt_id) }}" style="color:var(--orange);text-decoration:none;font-weight:600;">
                            {{ $ont->olt->name ?? "OLT #{$ont->olt_id}" }}
                        </a>
                    </td>
                    <td><code style="font-size:.72rem;font-weight:600;color:var(--red);">{{ $ont->serial_number }}</code></td>
                    <td style="font-size:.78rem;">{{ $ont->description ?? '—' }}</td>
                    <td style="font-size:.75rem;">{{ $ont->pon_port }}:{{ $ont->olt_port_index }}</td>
                    <td style="font-size:.75rem;">
                        @if($ont->customer)
                            <a href="{{ route('admin.customers.show', $ont->customer) }}" style="color:var(--orange);">
                                <i class='bx bx-user me-1' style="font-size:.7rem;"></i>{{ $ont->customer->name }}
                            </a>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="font-size:.7rem;color:var(--text-muted);">
                        {{ $ont->last_synced_at ? $ont->last_synced_at->diffForHumans() : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
@endif

<style>
.stat-card { transition: transform .2s ease, box-shadow .2s ease; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
.stat-label { font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:2px; }
.stat-value { font-size:1.4rem;font-weight:700;line-height:1.2; }
.olt-card:hover { transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.2); }
@keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.35; } }
</style>
</div>
@endsection

@section('scripts')
<script>
// Perbarui otomatis halaman pantau setiap 60 detik (reload data dari DB, bukan Telnet)
(function refreshLoop() {
    var secs = 60;
    function tick() {
        document.getElementById('last-refresh').textContent = 'Perbarui dalam ' + secs + 'd';
        secs--;
        if (secs < 0) { location.reload(); }
        else { setTimeout(tick, 1000); }
    }
    tick();
})();
</script>
@endsection
