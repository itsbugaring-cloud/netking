@php
if (request()->has('debug_mikrotik')) {
    $area = \App\Models\Area::where('name', 'like', '%Bayongbong%')->first();
    $mikrotik = \App\Services\MikroTikService::forArea($area);
    $res = $mikrotik->getAllSecrets();
    if (!$res['success']) dd($res);
    $filtered = collect($res['data'])->filter(function($s) {
        return in_array($s['name'] ?? '', ['BYB-012', 'BYB-013', 'BYB-014', 'BYB-015']);
    })->values();
    dd([
        'ip' => $area->router_ip,
        'secrets' => $filtered
    ]);
}
@endphp
@extends('layouts.app')
@section('title', 'Manajemen PPPoE')

@section('styles')
<style>
    /* ── Router/Area Cards ── */
    .router-kanban {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: .75rem;
    }
    .router-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: .9rem 1rem;
        display: flex; flex-direction: column; gap: .45rem;
        cursor: pointer;
        text-decoration: none;
        transition: box-shadow .15s, border-color .15s, background .12s;
        position: relative;
    }
    .router-card:hover {
        border-color: color-mix(in srgb, var(--blue) 45%, var(--border));
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
        text-decoration: none;
    }
    .router-card--active {
        border-color: var(--blue) !important;
        background: color-mix(in srgb, var(--blue) 6%, var(--surface));
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 18%, transparent);
    }
    .router-card-name {
        font-size: .875rem; font-weight: 700; color: var(--txt);
        display: flex; align-items: center; gap: .4rem;
    }
    .router-card-ip {
        font-size: .7rem; font-family: monospace;
        background: color-mix(in srgb, var(--orange) 10%, var(--surface-2));
        color: var(--orange); padding: .12rem .45rem; border-radius: 5px;
        border: 1px solid color-mix(in srgb, var(--orange) 20%, var(--border));
        display: inline-block; width: fit-content;
    }
    .router-card-active-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--blue); flex-shrink: 0;
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--blue) 25%, transparent);
    }

    /* ── PPPoE Kanban Grid ── */
    .pppoe-kanban {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: .75rem;
    }

    /* ── PPPoE Card ── */
    .pppoe-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: .9rem;
        display: flex; flex-direction: column; gap: .5rem;
        transition: box-shadow .15s, border-color .15s;
        position: relative;
    }
    .pppoe-card:hover {
        border-color: color-mix(in srgb, var(--blue) 40%, var(--border));
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
    }
    .pppoe-card--offline { opacity: .55; }
    .pppoe-card--offline:hover { opacity: 1; }
    .pppoe-card--disabled { opacity: .35; border-style: dashed; }
    .pppoe-card--disabled:hover { opacity: .85; }
    .pppoe-card--online {
        border-color: color-mix(in srgb, var(--green) 30%, var(--border));
    }

    .pppoe-card-name {
        font-size: .875rem; font-weight: 700; color: var(--txt);
        display: flex; align-items: center; gap: .4rem;
        word-break: break-all;
    }
    .pppoe-card-comment {
        font-size: .72rem; color: var(--txt-3);
        display: flex; align-items: center; gap: .25rem;
        margin-top: -.2rem;
    }
    .pppoe-card-row {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: .3rem;
    }
    .pppoe-card-ip {
        font-size: .7rem; font-family: monospace;
        background: color-mix(in srgb, var(--blue) 10%, var(--surface-2));
        color: var(--blue); padding: .12rem .45rem; border-radius: 5px;
        border: 1px solid color-mix(in srgb, var(--blue) 20%, var(--border));
        display: inline-flex; align-items: center; gap: .2rem;
    }
    .pppoe-card-traffic {
        display: flex; align-items: center; gap: .5rem; font-size: .72rem;
        padding: .35rem .5rem; border-radius: 7px;
        background: color-mix(in srgb, var(--green) 6%, var(--surface-2));
        border: 1px solid color-mix(in srgb, var(--green) 15%, var(--border));
    }
    .pppoe-card-traffic-rx { color: var(--green); display: flex; align-items: center; gap: .15rem; }
    .pppoe-card-traffic-tx { color: var(--blue);  display: flex; align-items: center; gap: .15rem; }
    .pppoe-card-uptime { font-size: .7rem; color: var(--txt-3); display: flex; align-items: center; gap: .2rem; }
    .pppoe-card-caller { font-size: .68rem; color: var(--txt-3); font-family: monospace; display: flex; align-items: center; gap: .2rem; }

    .pppoe-card-actions {
        display: flex; gap: .3rem; padding-top: .55rem;
        border-top: 1px solid var(--border); margin-top: auto;
    }
    .pppoe-card-btn {
        flex: 1; display: inline-flex; align-items: center; justify-content: center;
        height: 30px; border-radius: 7px; font-size: .78rem;
        border: 1px solid var(--border); background: transparent; color: var(--txt-3);
        cursor: pointer; text-decoration: none; transition: all .12s; gap: .3rem;
    }
    .pppoe-card-btn:hover { background: var(--surface-2); color: var(--txt); }
    .pppoe-card-btn--danger:hover {
        background: color-mix(in srgb, var(--red) 8%, var(--surface));
        color: var(--red); border-color: color-mix(in srgb, var(--red) 28%, var(--border));
    }
    .pppoe-card-btn--enable:hover {
        background: color-mix(in srgb, var(--green) 8%, var(--surface));
        color: var(--green); border-color: color-mix(in srgb, var(--green) 28%, var(--border));
    }

    /* search bar */
    .pppoe-search-wrap {
        display: flex; align-items: center; gap: .5rem;
        padding: .5rem .75rem;
        border: 1px solid var(--border); border-radius: 9px;
        background: var(--surface); margin-bottom: .75rem;
        max-width: 420px;
    }
    .pppoe-search-wrap input { flex: 1; border: none; background: transparent; color: var(--txt); font-size: .8125rem; outline: none; font-family: inherit; }
    .pppoe-search-wrap i { color: var(--blue); font-size:1rem; flex-shrink:0; }

    /* filter pills */
    .pppoe-filter-pills { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .75rem; }
    .pppoe-pill {
        padding: .22rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 600;
        border: 1px solid var(--border); background: var(--surface); color: var(--txt-3);
        cursor: pointer; transition: all .12s;
    }
    .pppoe-pill.active        { background: var(--blue); color: #fff; border-color: var(--blue); }
    .pppoe-pill.active-green  { background: var(--green); color: #fff; border-color: var(--green); }
    .pppoe-pill.active-red    { background: color-mix(in srgb,var(--red) 80%,#c00); color: #fff; border-color: var(--red); }

    /* health bar */
    .pppoe-health-bar { height: 4px; border-radius: 999px; background: var(--surface-2); overflow: hidden; }
    .pppoe-health-fill { height: 100%; background: var(--green); border-radius: 999px; }
</style>
@endsection

@section('content')
<div class="ms-page pppoe-index-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-wifi'></i> Jaringan Akses</div>
            <h1 class="ms-page-title">Manajemen PPPoE</h1>
        </div>
        @if($selectedArea)
        <div class="ms-page-actions">
            <span id="pppoe-ar-chip" style="display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;padding:.28rem .75rem;border-radius:999px;background:color-mix(in srgb,var(--orange) 12%,var(--surface));color:var(--orange);border:1px solid color-mix(in srgb,var(--orange) 25%,var(--border));cursor:pointer;" onclick="pppoeArToggle()" title="Klik untuk matikan auto-refresh">
                <i class='bx bx-time-five'></i>
                <span id="pppoe-ar-label">Refresh dalam 30d</span>
            </span>
        </div>
        @endif
    </div>

    {{-- ── Router / Area Card Selector ── --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Pilih Router / Area</h5>
                <div class="ms-panel-subtitle">Klik kartu area untuk memuat data PPPoE</div>
            </div>
            <div class="ms-toolbar-right">
                @if($selectedArea)
                <span class="ms-chip" style="color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));background:color-mix(in srgb,var(--blue) 8%,var(--surface));">
                    <i class='bx bx-check-circle'></i> {{ $selectedArea->name }}
                </span>
                @endif
                <a href="{{ route('admin.areas.create') }}" class="ms-btn">
                    <i class='bx bx-plus'></i> Tambah Router
                </a>
            </div>
        </div>
        <div class="ms-panel-body">
            <div class="router-kanban">
                @foreach($areas as $area)
                @php $isActive = $selectedArea?->id == $area->id; @endphp
                <a href="{{ route('admin.pppoe.index', ['area_id' => $area->id]) }}"
                   class="router-card {{ $isActive ? 'router-card--active' : '' }}">
                    <div class="router-card-name">
                        @if($isActive)
                            <div class="router-card-active-dot"></div>
                        @else
                            <i class='bx bx-router' style="color:var(--txt-3);font-size:.95rem;flex-shrink:0;"></i>
                        @endif
                        {{ $area->router_identity ?: $area->name }}
                    </div>
                    <div class="router-card-ip">{{ $area->router_ip }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($error)
    <div class="ms-panel" style="border-color:color-mix(in srgb,var(--red) 25%,var(--border));background:color-mix(in srgb,var(--red) 6%,var(--surface));">
        <div class="ms-panel-body d-flex align-items-start gap-2" style="color:var(--red);">
            <i class='bx bx-error-circle mt-1' style="font-size:1.1rem;"></i>
            <div>
                <strong>Tidak Dapat Terhubung ke Router</strong><br>
                {{ $error }}<br>
                <small style="color:color-mix(in srgb,var(--red) 80%,var(--txt));">Pastikan router MikroTik aktif, API diaktifkan (port 8728), dan kredensial sudah benar di <strong>Area → Ubah</strong>.</small>
            </div>
        </div>
    </div>
    @endif

    @if($selectedArea && !$error)

    @php
        $totalSecrets  = count($secrets);
        $activeCount   = $activeSessions->count();
        $offlineCount  = $totalSecrets - $activeCount;
        $disabledCount = collect($secrets)->where('disabled', 'true')->count();
        $onlinePct     = $totalSecrets > 0 ? round($activeCount / $totalSecrets * 100) : 0;

        $fmtBytes = function($bytes) {
            $bytes = (int)$bytes;
            if ($bytes >= 1073741824) return number_format($bytes/1073741824, 2).' GB';
            if ($bytes >= 1048576)    return number_format($bytes/1048576, 2).' MB';
            if ($bytes >= 1024)       return number_format($bytes/1024, 2).' KB';
            return $bytes.' B';
        };
    @endphp

    {{-- Stats --}}
    <div class="ms-stat-grid">
        <div class="ms-stat-card" style="--stat-accent:var(--blue);--stat-bg:color-mix(in srgb,var(--blue) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-server' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Router</div>
                <div class="ms-stat-value" style="font-size:1.05rem;line-height:1.2;">{{ $routerInfo['identity'] ?? '-' }}</div>
                <div class="ms-stat-meta">{{ $selectedArea->router_ip }}</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--orange,#f97316);--stat-bg:color-mix(in srgb,var(--orange,#f97316) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-key' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Total Secret</div>
                <div class="ms-stat-value">{{ $totalSecrets }}</div>
                @if($disabledCount > 0)
                <div class="ms-stat-meta">{{ $disabledCount }} dinonaktifkan</div>
                @endif
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--green);--stat-bg:color-mix(in srgb,var(--green) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-check-circle' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Sesi Aktif</div>
                <div class="ms-stat-value" style="color:var(--green);">{{ $activeCount }}</div>
                <div class="ms-stat-meta">{{ $onlinePct }}% rasio online</div>
            </div>
            <div class="ms-auto" style="width:72px;">
                <div class="pppoe-health-bar"><div class="pppoe-health-fill" style="width:{{ $onlinePct }}%;"></div></div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--red,#ef4444);--stat-bg:color-mix(in srgb,var(--red,#ef4444) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-x-circle' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Offline</div>
                <div class="ms-stat-value" style="color:var(--red,#ef4444);">{{ $offlineCount }}</div>
                <div class="ms-stat-meta">&nbsp;</div>
            </div>
        </div>
    </div>

    {{-- Panel: Kartu PPPoE --}}
    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">PPPoE Secrets</h5>
                <div class="ms-panel-subtitle">{{ $selectedArea->name }} — {{ $totalSecrets }} secret</div>
            </div>
            <div class="ms-toolbar-right">
                <form method="POST" action="{{ route('admin.pppoe.sync-customers') }}" class="d-inline"
                    data-confirm="Import semua PPPoE secrets dari MikroTik ke database Customers?">
                    @csrf
                    <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                    <button type="submit" class="ms-btn-secondary">
                        <i class='bx bx-import'></i> Sync ke Customers
                    </button>
                </form>
            </div>
        </div>
        <div class="ms-panel-body" style="padding-bottom:.25rem;">
            <div class="pppoe-search-wrap">
                <i class='bx bx-search'></i>
                <input type="text" id="pppoe-search" placeholder="Cari username, profil, IP, customer..." autocomplete="off">
            </div>
            <div class="pppoe-filter-pills">
                <span class="pppoe-pill active" data-filter="all">Semua ({{ $totalSecrets }})</span>
                <span class="pppoe-pill" data-filter="online">Online ({{ $activeCount }})</span>
                <span class="pppoe-pill" data-filter="offline">Offline ({{ $offlineCount }})</span>
                @if($disabledCount > 0)
                <span class="pppoe-pill" data-filter="disabled">Disabled ({{ $disabledCount }})</span>
                @endif
            </div>
        </div>

        <div class="ms-panel-body" style="padding-top:0;">
            @if($totalSecrets === 0)
            <div style="text-align:center;padding:3rem;color:var(--txt-3);">
                <i class='bx bx-wifi-off' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                <p class="mb-0 fw-semibold">Tidak ada PPPoE secret ditemukan</p>
            </div>
            @else
            <div class="pppoe-kanban" id="pppoe-kanban">
                @foreach($secrets as $secret)
                @php
                    $secretName = $secret['name'] ?? '';
                    if (!$secretName) continue;
                    $session    = $activeSessions->get($secretName);
                    $isActive   = !is_null($session);
                    $isDisabled = ($secret['disabled'] ?? 'false') === 'true';
                    $cardState  = $isDisabled ? 'disabled' : ($isActive ? 'online' : 'offline');
                @endphp
                <div class="pppoe-card pppoe-card--{{ $cardState }}"
                     data-state="{{ $cardState }}"
                     data-search="{{ strtolower($secretName . ' ' . ($secret['profile'] ?? '') . ' ' . ($secret['comment'] ?? '') . ' ' . ($session['address'] ?? '') . ' ' . ($session['caller-id'] ?? '')) }}">

                    <div class="pppoe-card-name">
                        @if($isActive)
                            <i class='bx bxs-circle' style="color:var(--green);font-size:.6rem;flex-shrink:0;margin-top:2px;"></i>
                        @elseif($isDisabled)
                            <i class='bx bx-pause-circle' style="color:var(--txt-3);font-size:.85rem;flex-shrink:0;"></i>
                        @else
                            <i class='bx bx-circle' style="color:var(--txt-3);font-size:.6rem;flex-shrink:0;margin-top:2px;"></i>
                        @endif
                        {{ $secretName }}
                    </div>

                    @if($secret['comment'] ?? '')
                    <div class="pppoe-card-comment">
                        <i class='bx bx-user' style="font-size:.7rem;"></i>
                        {{ $secret['comment'] }}
                    </div>
                    @endif

                    <div class="pppoe-card-row">
                        <code style="font-size:.72rem;padding:.1rem .35rem;background:var(--surface-2);border-radius:4px;border:1px solid var(--border);">{{ $secret['profile'] ?? 'default' }}</code>
                        @if($isDisabled)
                        <span class="badge-status badge-warning" style="font-size:.65rem;">
                            <i class='bx bx-pause-circle' style="font-size:.55rem;margin-right:2px;"></i>Disabled
                        </span>
                        @elseif($isActive)
                        <span class="badge-status badge-active" style="font-size:.65rem;">
                            <i class='bx bxs-circle bx-flashing' style="font-size:.38rem;margin-right:2px;vertical-align:middle;"></i>Online
                        </span>
                        @else
                        <span class="badge-status badge-inactive" style="font-size:.65rem;">Offline</span>
                        @endif
                    </div>

                    @if($isActive && $session)
                    <div class="pppoe-card-ip">
                        <i class='bx bx-globe' style="font-size:.7rem;"></i>
                        {{ $session['address'] ?? '-' }}
                    </div>
                    <div class="pppoe-card-traffic">
                        <span class="pppoe-card-traffic-rx">
                            <i class='bx bx-down-arrow-alt'></i>{{ $fmtBytes($session['bytes-in'] ?? 0) }}
                        </span>
                        <span style="color:var(--border);">|</span>
                        <span class="pppoe-card-traffic-tx">
                            <i class='bx bx-up-arrow-alt'></i>{{ $fmtBytes($session['bytes-out'] ?? 0) }}
                        </span>
                    </div>
                    <div class="pppoe-card-row" style="gap:.2rem;">
                        @if($session['uptime'] ?? '')
                        <span class="pppoe-card-uptime">
                            <i class='bx bx-time' style="font-size:.7rem;"></i>{{ $session['uptime'] }}
                        </span>
                        @endif
                        @if($session['caller-id'] ?? '')
                        <span class="pppoe-card-caller">
                            <i class='bx bx-wifi' style="font-size:.65rem;"></i>{{ $session['caller-id'] }}
                        </span>
                        @endif
                    </div>
                    @endif

                    <div class="pppoe-card-actions">
                        <form method="POST" action="{{ route('admin.pppoe.toggle') }}" style="flex:1;display:contents;">
                            @csrf
                            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                            <input type="hidden" name="username" value="{{ $secretName }}">
                            <input type="hidden" name="enable" value="{{ $isDisabled ? '1' : '0' }}">
                            <button type="submit"
                                class="pppoe-card-btn {{ $isDisabled ? 'pppoe-card-btn--enable' : '' }}"
                                title="{{ $isDisabled ? 'Aktifkan' : 'Nonaktifkan' }}"
                                style="flex:1;">
                                <i class='bx {{ $isDisabled ? "bx-play-circle" : "bx-pause-circle" }}'></i>
                                <span style="font-size:.7rem;">{{ $isDisabled ? 'Aktifkan' : 'Pause' }}</span>
                            </button>
                        </form>
                        @if($isActive)
                        <form method="POST" action="{{ route('admin.pppoe.disconnect') }}" style="flex:1;display:contents;"
                            data-confirm="Putuskan sesi PPPoE {{ $secretName }}?">
                            @csrf
                            <input type="hidden" name="area_id" value="{{ $selectedArea->id }}">
                            <input type="hidden" name="username" value="{{ $secretName }}">
                            <button type="submit" class="pppoe-card-btn pppoe-card-btn--danger" title="Putuskan" style="flex:1;">
                                <i class='bx bx-link-alt'></i>
                                <span style="font-size:.7rem;">Putus</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div id="pppoe-empty" style="display:none;text-align:center;padding:2.5rem;color:var(--txt-3);">
                <i class='bx bx-search-alt' style="font-size:2rem;opacity:.3;display:block;margin-bottom:.4rem;"></i>
                Tidak ada hasil yang cocok
            </div>
            @endif
        </div>
    </div>

    @endif
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // data-confirm handled by global layout script
    });

    // ── Search + Filter ────────────────────────────────────────────────────
    var pppoeCurrentFilter = 'all';

    function pppoeApplyFilter() {
        var q       = (document.getElementById('pppoe-search')?.value || '').toLowerCase().trim();
        var cards   = document.querySelectorAll('#pppoe-kanban .pppoe-card');
        var visible = 0;
        cards.forEach(function(c) {
            var stateOk  = pppoeCurrentFilter === 'all' || c.dataset.state === pppoeCurrentFilter;
            var searchOk = !q || (c.dataset.search || '').includes(q);
            var show = stateOk && searchOk;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        var empty = document.getElementById('pppoe-empty');
        if (empty) empty.style.display = visible === 0 ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        var searchEl = document.getElementById('pppoe-search');
        if (searchEl) searchEl.addEventListener('input', pppoeApplyFilter);

        document.querySelectorAll('.pppoe-pill').forEach(function(pill) {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.pppoe-pill').forEach(function(p) {
                    p.classList.remove('active', 'active-green', 'active-red');
                });
                var f = this.dataset.filter;
                pppoeCurrentFilter = f;
                if (f === 'online') this.classList.add('active-green');
                else if (f === 'offline') this.classList.add('active-red');
                else this.classList.add('active');
                pppoeApplyFilter();
            });
        });
    });

    // ── Auto-refresh ───────────────────────────────────────────────────────
    @if($selectedArea)
    var pppoeArSecs    = 30;
    var pppoeArCurrent = pppoeArSecs;
    var pppoeArActive  = true;
    var pppoeArTimer   = null;

    function pppoeArTick() {
        pppoeArCurrent--;
        var label = document.getElementById('pppoe-ar-label');
        if (label) label.textContent = 'Refresh dalam ' + pppoeArCurrent + 'd';
        if (pppoeArCurrent <= 0) { clearInterval(pppoeArTimer); location.reload(); }
    }

    window.pppoeArToggle = function() {
        var label = document.getElementById('pppoe-ar-label');
        var chip  = document.getElementById('pppoe-ar-chip');
        if (pppoeArActive) {
            clearInterval(pppoeArTimer); pppoeArTimer = null; pppoeArActive = false; pppoeArCurrent = pppoeArSecs;
            if (label) label.textContent = 'Auto-refresh: OFF';
            if (chip)  { chip.style.background='var(--surface)'; chip.style.color='var(--txt-3)'; chip.style.borderColor='var(--border)'; }
        } else {
            pppoeArActive = true; pppoeArCurrent = pppoeArSecs;
            pppoeArTimer  = setInterval(pppoeArTick, 1000);
            if (chip)  { chip.style.background=''; chip.style.color='var(--orange)'; chip.style.borderColor=''; }
            pppoeArTick();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        pppoeArTimer = setInterval(pppoeArTick, 1000);
        pppoeArTick();
    });
    @endif
</script>
@endsection
