@extends('admin.layout.app')
@section('title', 'System Dashboard')

@section('content')
<div class="page-header d-print-none mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h2 class="page-title"><i class="ti ti-server me-2"></i>System Dashboard</h2>
            <div class="text-muted mt-1">Monitor semua router MikroTik — CPU, RAM, Disk, Uptime</div>
        </div>
        <div class="col-auto ms-auto">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:.8rem;">Auto-refresh:</span>
                <select id="refresh-interval" class="form-select form-select-sm" style="width:100px;">
                    <option value="10">10s</option>
                    <option value="30" selected>30s</option>
                    <option value="60">60s</option>
                    <option value="120">120s</option>
                </select>
                <button id="refresh-now" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-refresh"></i> Refresh
                </button>
                <span id="last-update" class="text-muted" style="font-size:.75rem;"></span>
            </div>
        </div>
    </div>
</div>

<div id="router-grid" class="row row-cards">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="text-muted mt-2">Memuat data router...</div>
    </div>
</div>
@endsection

@push('scripts')
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

    function progressColor(pct) {
        if (pct >= 80) return 'bg-danger';
        if (pct >= 60) return 'bg-warning';
        return 'bg-success';
    }

    function healthBadge(health) {
        const map = {
            online: '<span class="badge bg-success">Online</span>',
            warning: '<span class="badge bg-warning text-dark">Warning</span>',
            critical: '<span class="badge bg-danger">Critical</span>',
            offline: '<span class="badge bg-secondary">Offline</span>',
        };
        return map[health] || map.offline;
    }

    function renderCard(r) {
        const res = r.resource;
        const hw = r.hardware;
        const lic = r.license;

        if (r.status === 'offline') {
            return `<div class="col-sm-6 col-lg-4">
                <div class="card border-secondary" style="opacity:.7">
                    <div class="card-header"><h3 class="card-title">${r.identity || r.area_name}</h3>${healthBadge('offline')}</div>
                    <div class="card-body">
                        <div class="text-muted mb-1">${r.area_name} — ${r.router_ip}</div>
                        <div class="text-danger"><i class="ti ti-wifi-off me-1"></i>${r.error || 'Unreachable'}</div>
                    </div>
                </div>
            </div>`;
        }

        let hwHtml = '';
        if (hw) {
            hwHtml = '<div class="mt-2 d-flex gap-3" style="font-size:.75rem;">';
            if (hw.temperature !== null) hwHtml += `<span><i class="ti ti-temperature me-1"></i>${hw.temperature}°C</span>`;
            if (hw.voltage !== null) hwHtml += `<span><i class="ti ti-bolt me-1"></i>${hw.voltage}V</span>`;
            hwHtml += '</div>';
        }

        let licHtml = '';
        if (lic) {
            licHtml = `<div class="mt-2" style="font-size:.72rem;color:var(--tblr-secondary);">License: L${lic.level} · ${lic.software_id}</div>`;
        }

        return `<div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">${r.identity || r.area_name}</h3>
                    ${healthBadge(r.health)}
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2" style="font-size:.78rem;">
                        <span class="text-muted">${r.area_name} — ${r.router_ip}</span>
                        <span>${res.version} / ${res.board_name}</span>
                    </div>
                    <div class="mb-1" style="font-size:.78rem;"><i class="ti ti-clock me-1"></i>Uptime: <strong>${res.uptime}</strong></div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between" style="font-size:.72rem;"><span>CPU (${res.cpu_count} core)</span><span>${res.cpu_load}%</span></div>
                        <div class="progress progress-sm"><div class="progress-bar ${progressColor(res.cpu_load)}" style="width:${res.cpu_load}%"></div></div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between" style="font-size:.72rem;"><span>RAM</span><span>${res.mem_used_pct}% (${formatBytes(res.mem_total - res.mem_free)} / ${formatBytes(res.mem_total)})</span></div>
                        <div class="progress progress-sm"><div class="progress-bar ${progressColor(res.mem_used_pct)}" style="width:${res.mem_used_pct}%"></div></div>
                    </div>
                    <div class="mb-1">
                        <div class="d-flex justify-content-between" style="font-size:.72rem;"><span>Disk</span><span>${res.disk_used_pct}% (${formatBytes(res.disk_total - res.disk_free)} / ${formatBytes(res.disk_total)})</span></div>
                        <div class="progress progress-sm"><div class="progress-bar ${progressColor(res.disk_used_pct)}" style="width:${res.disk_used_pct}%"></div></div>
                    </div>
                    ${hwHtml}
                    ${licHtml}
                </div>
            </div>
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
@endpush
