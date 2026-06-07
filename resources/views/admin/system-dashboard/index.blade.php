@extends('layouts.app')
@section('title', 'System Dashboard')

@section('styles')
<style>
    .dash-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; gap: .5rem; transition: box-shadow .15s, border-color .15s; }
    .dash-card:hover { border-color: color-mix(in srgb, var(--blue) 30%, var(--border)); box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .dash-card--offline { opacity: .6; border-style: dashed; }
    .dash-card--offline:hover { opacity: 1; }
    .dash-card-header { display: flex; justify-content: space-between; align-items: center; }
    .dash-card-title { font-size: .875rem; font-weight: 700; color: var(--txt); }
    .dash-card-subtitle { font-size: .72rem; color: var(--txt-3); margin-top: .1rem; }
    .dash-badge { font-size: .65rem; font-weight: 600; padding: .15rem .5rem; border-radius: 999px; display: inline-flex; align-items: center; gap: .2rem; }
    .dash-badge-online { background: color-mix(in srgb, var(--green) 12%, var(--surface)); color: var(--green); border: 1px solid color-mix(in srgb, var(--green) 25%, var(--border)); }
    .dash-badge-warning { background: color-mix(in srgb, var(--orange) 12%, var(--surface)); color: var(--orange); border: 1px solid color-mix(in srgb, var(--orange) 25%, var(--border)); }
    .dash-badge-critical { background: color-mix(in srgb, var(--red) 12%, var(--surface)); color: var(--red); border: 1px solid color-mix(in srgb, var(--red) 25%, var(--border)); }
    .dash-badge-offline { background: color-mix(in srgb, var(--txt-3) 12%, var(--surface)); color: var(--txt-3); border: 1px solid var(--border); }
    .dash-progress { height: 5px; border-radius: 999px; background: var(--surface-2); overflow: hidden; margin-top: .2rem; }
    .dash-progress-fill { height: 100%; border-radius: 999px; transition: width .3s; }
    .dash-progress-fill--ok { background: var(--green); }
    .dash-progress-fill--warn { background: var(--orange); }
    .dash-progress-fill--crit { background: var(--red); }
    .dash-metric { font-size: .72rem; color: var(--txt-3); display: flex; justify-content: space-between; }
    .dash-metric-value { font-weight: 600; color: var(--txt); }
    .dash-uptime { font-size: .72rem; color: var(--txt-3); display: flex; align-items: center; gap: .25rem; }
    .dash-hw { font-size: .68rem; color: var(--txt-3); display: flex; gap: .75rem; }
    .dash-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: .75rem; }
    .dash-refresh-chip { display: inline-flex; align-items: center; gap: .35rem; font-size: .78rem; padding: .28rem .75rem; border-radius: 999px; background: color-mix(in srgb, var(--blue) 10%, var(--surface)); color: var(--blue); border: 1px solid color-mix(in srgb, var(--blue) 25%, var(--border)); cursor: pointer; }
    .dash-refresh-chip:hover { background: color-mix(in srgb, var(--blue) 16%, var(--surface)); }
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-server'></i> Monitoring</div>
            <h1 class="ms-page-title">System Dashboard</h1>
        </div>
        <div class="ms-page-actions">
            <div class="d-flex align-items-center gap-2">
                <select id="refresh-interval" class="form-select form-select-sm" style="width:90px;background:var(--surface);color:var(--txt);border-color:var(--border);font-size:.78rem;">
                    <option value="10">10s</option>
                    <option value="30" selected>30s</option>
                    <option value="60">60s</option>
                    <option value="120">120s</option>
                </select>
                <button id="refresh-now" class="ms-btn-secondary" style="font-size:.78rem;padding:.3rem .7rem;">
                    <i class='bx bx-refresh'></i> Refresh
                </button>
                <span id="last-update" style="font-size:.7rem;color:var(--txt-3);"></span>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Router Status</h5>
                <div class="ms-panel-subtitle">Monitor semua router MikroTik — CPU, RAM, Disk, Uptime</div>
            </div>
        </div>
        <div class="ms-panel-body">
            <div id="router-grid" class="dash-grid">
                <div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--txt-3);">
                    <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                    Memuat data router...
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    let interval = 30;
    let timer = null;
    const grid = document.getElementById('router-grid');
    const intervalSelect = document.getElementById('refresh-interval');
    const refreshBtn = document.getElementById('refresh-now');
    const lastUpdate = document.getElementById('last-update');

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function progressClass(pct) {
        if (pct >= 80) return 'dash-progress-fill--crit';
        if (pct >= 60) return 'dash-progress-fill--warn';
        return 'dash-progress-fill--ok';
    }

    function healthBadge(health) {
        const map = {
            online: '<span class="dash-badge dash-badge-online"><i class="bx bx-check-circle"></i> Online</span>',
            warning: '<span class="dash-badge dash-badge-warning"><i class="bx bx-error"></i> Warning</span>',
            critical: '<span class="dash-badge dash-badge-critical"><i class="bx bx-error-circle"></i> Critical</span>',
            offline: '<span class="dash-badge dash-badge-offline"><i class="bx bx-wifi-off"></i> Offline</span>',
        };
        return map[health] || map.offline;
    }

    function renderCard(r) {
        const res = r.resource;
        const hw = r.hardware;
        const lic = r.license;

        if (r.status === 'offline') {
            return `<div class="dash-card dash-card--offline">
                <div class="dash-card-header">
                    <div class="dash-card-title">${r.identity || r.area_name}</div>
                    ${healthBadge('offline')}
                </div>
                <div class="dash-card-subtitle">${r.area_name} — ${r.router_ip}</div>
                <div style="font-size:.75rem;color:var(--red);display:flex;align-items:center;gap:.25rem;">
                    <i class='bx bx-wifi-off'></i> ${r.error || 'Unreachable'}
                </div>
            </div>`;
        }

        let hwHtml = '';
        if (hw) {
            hwHtml = '<div class="dash-hw">';
            if (hw.temperature !== null) hwHtml += `<span><i class='bx bx-thermometer'></i> ${hw.temperature}°C</span>`;
            if (hw.voltage !== null) hwHtml += `<span><i class='bx bx-bolt-circle'></i> ${hw.voltage}V</span>`;
            hwHtml += '</div>';
        }

        let licHtml = '';
        if (lic) {
            licHtml = `<div style="font-size:.68rem;color:var(--txt-3);">License: L${lic.level} · ${lic.software_id}</div>`;
        }

        return `<div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">${r.identity || r.area_name}</div>
                ${healthBadge(r.health)}
            </div>
            <div class="dash-card-subtitle">${r.area_name} — ${r.router_ip} · ${res.version} / ${res.board_name}</div>
            <div class="dash-uptime"><i class='bx bx-time-five'></i> Uptime: <strong>${res.uptime}</strong></div>
            <div>
                <div class="dash-metric"><span>CPU (${res.cpu_count} core)</span><span class="dash-metric-value">${res.cpu_load}%</span></div>
                <div class="dash-progress"><div class="dash-progress-fill ${progressClass(res.cpu_load)}" style="width:${res.cpu_load}%"></div></div>
            </div>
            <div>
                <div class="dash-metric"><span>RAM</span><span class="dash-metric-value">${res.mem_used_pct}% (${formatBytes(res.mem_total - res.mem_free)} / ${formatBytes(res.mem_total)})</span></div>
                <div class="dash-progress"><div class="dash-progress-fill ${progressClass(res.mem_used_pct)}" style="width:${res.mem_used_pct}%"></div></div>
            </div>
            <div>
                <div class="dash-metric"><span>Disk</span><span class="dash-metric-value">${res.disk_used_pct}% (${formatBytes(res.disk_total - res.disk_free)} / ${formatBytes(res.disk_total)})</span></div>
                <div class="dash-progress"><div class="dash-progress-fill ${progressClass(res.disk_used_pct)}" style="width:${res.disk_used_pct}%"></div></div>
            </div>
            ${hwHtml}
            ${licHtml}
        </div>`;
    }

    function fetchData() {
        fetch('{{ route("admin.system-dashboard.data") }}')
            .then(r => r.json())
            .then(data => {
                grid.innerHTML = data.routers.map(renderCard).join('');
                lastUpdate.textContent = 'Updated: ' + new Date().toLocaleTimeString();
            })
            .catch(err => {
                console.error('Dashboard fetch error:', err);
                setTimeout(() => {
                    fetch('{{ route("admin.system-dashboard.data") }}')
                        .then(r => r.json())
                        .then(data => {
                            grid.innerHTML = data.routers.map(renderCard).join('');
                            lastUpdate.textContent = 'Updated: ' + new Date().toLocaleTimeString();
                        })
                        .catch(() => {
                            lastUpdate.textContent = '⚠️ Connection failed';
                        });
                }, 5000);
            });
    }

    function startPolling() {
        clearInterval(timer);
        timer = setInterval(fetchData, interval * 1000);
    }

    intervalSelect.addEventListener('change', function() {
        interval = parseInt(this.value);
        startPolling();
    });

    refreshBtn.addEventListener('click', fetchData);

    // Pause when tab hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(timer);
        } else {
            fetchData();
            startPolling();
        }
    });

    // Initial load
    fetchData();
    startPolling();
})();
</script>
@endsection
