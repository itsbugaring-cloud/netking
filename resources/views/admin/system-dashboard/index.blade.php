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
    
    <!-- Global Summary -->
    <div class="ms-stat-grid mb-3" id="global-summary" style="display:none;">
        <div class="ms-stat-card" style="--stat-accent:var(--green);--stat-bg:color-mix(in srgb,var(--green) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-check-circle' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Router Online</div>
                <div class="ms-stat-value" style="color:var(--green);" id="sum-online">0</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--red);--stat-bg:color-mix(in srgb,var(--red) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-x-circle' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Router Offline</div>
                <div class="ms-stat-value" style="color:var(--red);" id="sum-offline">0</div>
            </div>
        </div>
        <div class="ms-stat-card" style="--stat-accent:var(--orange);--stat-bg:color-mix(in srgb,var(--orange) 8%,var(--surface));">
            <div class="ms-stat-icon"><i class='bx bx-error' style="font-size:1.3rem;"></i></div>
            <div>
                <div class="ms-stat-label">Kritis (CPU/RAM > 85%)</div>
                <div class="ms-stat-value" style="color:var(--orange);" id="sum-critical">0</div>
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

    <!-- Live Traffic Monitor -->
    <div class="ms-panel mt-3">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title">Live Traffic Monitor (MRTG)</h5>
                <div class="ms-panel-subtitle">Monitor kecepatan Download/Upload secara real-time</div>
            </div>
            <div class="ms-toolbar-right d-flex gap-2">
                <select id="traffic-area-select" class="form-select form-select-sm" style="width:160px;background:var(--surface);color:var(--txt);border-color:var(--border);">
                    <option value="">-- Pilih Router --</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
                <select id="traffic-iface-select" class="form-select form-select-sm" style="width:160px;background:var(--surface);color:var(--txt);border-color:var(--border);display:none;">
                </select>
            </div>
        </div>
        <div class="ms-panel-body">
            <div id="traffic-empty" style="text-align:center;padding:3rem;color:var(--txt-3);">
                <i class='bx bx-line-chart' style="font-size:3rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                Pilih Router untuk melihat grafik Live Traffic
            </div>
            <div id="traffic-chart-container" style="display:none; height:320px; position:relative;">
                <canvas id="trafficChart"></canvas>
            </div>
            <div id="traffic-metrics" style="display:none; justify-content:center; gap:2rem; margin-top:1rem; text-align:center;">
                <div>
                    <div style="font-size:.75rem;color:var(--txt-3);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Download (RX)</div>
                    <div id="traffic-rx-val" style="font-size:1.5rem;font-weight:700;color:var(--green);">0 Mbps</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:var(--txt-3);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Upload (TX)</div>
                    <div id="traffic-tx-val" style="font-size:1.5rem;font-weight:700;color:var(--blue);">0 Mbps</div>
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
        if (pct >= 85) return 'dash-progress-fill--crit';
        if (pct >= 70) return 'dash-progress-fill--warn';
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
            <div class="dash-card-subtitle" style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.4rem;margin-bottom:.5rem;">
                <span style="font-family:monospace;background:color-mix(in srgb,var(--blue) 8%,var(--surface));color:var(--blue);padding:.15rem .45rem;border-radius:4px;border:1px solid color-mix(in srgb,var(--blue) 20%,var(--border));"><i class='bx bx-globe'></i> ${r.router_ip}</span>
                <span style="background:var(--surface-2);color:var(--txt-2);padding:.15rem .45rem;border-radius:4px;border:1px solid var(--border);"><i class='bx bx-chip'></i> ${res.board_name}</span>
                <span style="background:var(--surface-2);color:var(--txt-2);padding:.15rem .45rem;border-radius:4px;border:1px solid var(--border);"><i class='bx bx-code-alt'></i> v${res.version}</span>
            </div>
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
                
                // Update Global Summary
                let online = 0; let offline = 0; let crit = 0;
                data.routers.forEach(r => {
                    if (r.status === 'offline') {
                        offline++;
                    } else {
                        online++;
                        if (r.resource && (r.resource.cpu_load >= 85 || r.resource.mem_used_pct >= 85 || r.resource.disk_used_pct >= 95)) {
                            crit++;
                        }
                    }
                });
                document.getElementById('sum-online').textContent = online;
                document.getElementById('sum-offline').textContent = offline;
                document.getElementById('sum-critical').textContent = crit;
                document.getElementById('global-summary').style.display = 'grid';
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

// --- Live Traffic Monitor (MRTG) ---
(function() {
    const areaSelect = document.getElementById('traffic-area-select');
    const ifaceSelect = document.getElementById('traffic-iface-select');
    const emptyState = document.getElementById('traffic-empty');
    const chartContainer = document.getElementById('traffic-chart-container');
    const metricsContainer = document.getElementById('traffic-metrics');
    const rxVal = document.getElementById('traffic-rx-val');
    const txVal = document.getElementById('traffic-tx-val');

    let trafficTimer = null;
    let chartObj = null;
    let timeLabels = [];
    let rxData = [];
    let txData = [];
    const MAX_POINTS = 30; // Show last 30 data points (~90 seconds)

    function initChart() {
        if (chartObj) chartObj.destroy();
        const ctx = document.getElementById('trafficChart').getContext('2d');
        chartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [
                    {
                        label: 'Download (RX) Mbps',
                        data: rxData,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                    },
                    {
                        label: 'Upload (TX) Mbps',
                        data: txData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 0 },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(148, 163, 184, 0.1)' },
                        ticks: { color: '#94a3b8', maxTicksLimit: 10 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.1)' },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(val) { return val + ' Mbps'; }
                        }
                    }
                }
            }
        });
    }

    function fetchTraffic() {
        const areaId = $(areaSelect).val();
        if (!areaId) return;

        fetch('{{ route("admin.pppoe.traffic") }}?area_id=' + areaId)
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;
                
                // Populate interface select on first load
                if (ifaceSelect.options.length === 0) {
                    ifaceSelect.innerHTML = res.data.map(i => `<option value="${i.name}">${i.name} (${i.type})</option>`).join('');
                    ifaceSelect.style.display = 'inline-block';
                }

                const selIface = $(ifaceSelect).val();
                const ifaceData = res.data.find(i => i.name === selIface);

                if (ifaceData) {
                    const now = new Date();
                    const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                                    now.getMinutes().toString().padStart(2, '0') + ':' + 
                                    now.getSeconds().toString().padStart(2, '0');

                    // Push to array
                    timeLabels.push(timeStr);
                    rxData.push(ifaceData.rx_mbps);
                    txData.push(ifaceData.tx_mbps);

                    // Shift if too many
                    if (timeLabels.length > MAX_POINTS) {
                        timeLabels.shift();
                        rxData.shift();
                        txData.shift();
                    }

                    // Update metrics text
                    rxVal.textContent = ifaceData.rx_mbps.toFixed(2) + ' Mbps';
                    txVal.textContent = ifaceData.tx_mbps.toFixed(2) + ' Mbps';

                    // Update chart
                    if (!chartObj) initChart();
                    chartObj.update();
                }
            })
            .catch(err => console.error('Traffic fetch error:', err));
    }

    $(areaSelect).on('change', function() {
        clearInterval(trafficTimer);
        timeLabels = []; rxData = []; txData = [];
        ifaceSelect.innerHTML = '';
        
        if ($(this).val()) {
            emptyState.style.display = 'none';
            chartContainer.style.display = 'block';
            metricsContainer.style.display = 'flex';
            fetchTraffic(); // Fetch immediately
            trafficTimer = setInterval(fetchTraffic, 3000); // Polling every 3s
        } else {
            emptyState.style.display = 'block';
            chartContainer.style.display = 'none';
            metricsContainer.style.display = 'none';
            ifaceSelect.style.display = 'none';
        }
    });

    $(ifaceSelect).on('change', function() {
        // Reset chart when interface changes
        timeLabels = []; rxData = []; txData = [];
        if (chartObj) chartObj.update();
    });

    // Clean up on unmount/hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(trafficTimer);
        } else if ($(areaSelect).val()) {
            trafficTimer = setInterval(fetchTraffic, 3000);
        }
    });

})();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection
