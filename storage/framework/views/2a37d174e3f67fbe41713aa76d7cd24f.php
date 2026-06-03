
<?php $__env->startSection('title', 'NMS IP Pool per Area'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class='bx bx-network-chart me-2' style="color:var(--orange);"></i>IP Pool per Area
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('admin.nms.dashboard')); ?>">NMS</a></li>
                <li class="breadcrumb-item active">IP Pool</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span id="lastUpdate" style="font-size:.8rem;color:var(--text-muted);">Memuat...</span>
        <button class="btn btn-sm" id="refreshBtn"
                style="background:var(--orange);color:#fff;font-size:.78rem;padding:.25rem .75rem;">
            <i class='bx bx-refresh me-1'></i>Refresh
        </button>
    </div>
</div>

<!-- Loading -->
<div id="loadingState" class="text-center py-5 text-muted">
    <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;"></i>
    <div style="margin-top:.5rem;">Mengambil data IP pool dari semua router...</div>
</div>

<!-- Error -->
<div id="errorState" style="display:none;" class="alert alert-danger">
    <i class='bx bx-error-circle me-2'></i><span id="errorMsg"></span>
</div>

<!-- Area Grid -->
<div id="areaGrid" class="row g-3" style="display:none;"></div>

<style>
.pool-bar-wrap { background:#e5e7eb; border-radius:4px; height:10px; overflow:hidden; }
.pool-bar-fill { height:100%; border-radius:4px; transition: width .6s ease; }
.pool-bar-fill.critical { background: linear-gradient(90deg,#ef4444,#f87171); }
.pool-bar-fill.warning  { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
.pool-bar-fill.ok       { background: linear-gradient(90deg,#22c55e,#4ade80); }
</style>

<script>
const IP_POOL_URL = '<?php echo e(route("admin.nms.ip-pool.data")); ?>';

async function fetchData() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('errorState').style.display   = 'none';
    document.getElementById('areaGrid').style.display     = 'none';

    try {
        const res  = await fetch(IP_POOL_URL);
        const json = await res.json();
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('areaGrid').style.display     = '';
        document.getElementById('lastUpdate').textContent     = 'Update: ' + (json.updated_at || '—');
        renderData(json.data || []);
    } catch (e) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('errorState').style.display   = 'block';
        document.getElementById('errorMsg').textContent       = 'Gagal memuat data: ' + e.message;
    }
}

function renderData(rows) {
    const grid = document.getElementById('areaGrid');
    grid.innerHTML = '';

    rows.forEach(row => {
        const totalPools = row.pools ? row.pools.length : 0;
        const totalIPs   = row.pools ? row.pools.reduce((a, p) => a + (p.total || 0), 0) : 0;
        const usedIPs    = row.pools ? row.pools.reduce((a, p) => a + (p.used  || 0), 0) : 0;
        const usePct     = totalIPs > 0 ? Math.round(usedIPs / totalIPs * 100) : 0;

        let statusBadge, statusClass;
        if (!row.online) {
            statusBadge  = 'OFFLINE';
            statusClass  = 'background:rgba(239,68,68,.15);color:#ef4444;';
        } else {
            statusBadge  = 'ONLINE';
            statusClass  = 'background:rgba(34,197,94,.15);color:#22c55e;';
        }

        let poolsHtml = '';
        if (!row.online) {
            poolsHtml = '<div class="text-center py-3 text-muted" style="font-size:.8rem;">'
                      + '<i class=\'bx bx-wifi-off me-1\'></i>Router offline'
                      + (row.error ? '<div style="font-size:.68rem;margin-top:.2rem;">' + row.error.substring(0, 80) + '</div>' : '')
                      + '</div>';
        } else if (!totalPools) {
            poolsHtml = '<div class="text-center py-3 text-muted" style="font-size:.8rem;">'
                      + '<i class=\'bx bx-info-circle me-1\'></i>Tidak ada IP pool ditemukan</div>';
        } else {
            poolsHtml = row.pools.map(p => {
                const pct    = p.total > 0 ? Math.round(p.used / p.total * 100) : 0;
                const cls    = pct >= 90 ? 'critical' : (pct >= 70 ? 'warning' : 'ok');
                const color  = pct >= 90 ? '#ef4444'  : (pct >= 70 ? '#f59e0b' : '#22c55e');
                return '<div style="margin-bottom:.7rem;padding:.4rem .5rem;background:var(--bg-card-alt,#f8f9fa);border-radius:5px;">'
                     + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">'
                     + '<span style="font-weight:600;font-size:.78rem;">' + p.name + '</span>'
                     + '<span style="font-size:.68rem;color:' + color + ';font-weight:700;">' + pct + '%</span>'
                     + '</div>'
                     + '<div style="font-size:.68rem;color:var(--text-muted);margin-bottom:.25rem;font-family:monospace;">' + (p.ranges || '—') + '</div>'
                     + '<div class="pool-bar-wrap"><div class="pool-bar-fill ' + cls + '" style="width:' + pct + '%;"></div></div>'
                     + '<div style="display:flex;justify-content:space-between;font-size:.65rem;color:var(--text-muted);margin-top:.2rem;">'
                     + '<span>Used: <strong style="color:' + color + ';">' + p.used + '</strong></span>'
                     + '<span>Free: <strong>' + p.free + '</strong></span>'
                     + '<span>Total: <strong>' + p.total + '</strong></span>'
                     + '</div>'
                     + (p.comment ? '<div style="font-size:.65rem;color:var(--text-muted);margin-top:.15rem;font-style:italic;">' + p.comment + '</div>' : '')
                     + '</div>';
            }).join('');
        }

        const pctColor = usePct >= 90 ? '#ef4444' : (usePct >= 70 ? '#f59e0b' : '#22c55e');

        const cardHtml = '<div class="col-xl-4 col-lg-6">'
            + '<div class="card h-100">'
            + '<div class="card-body p-3">'
            + '<div class="d-flex align-items-start justify-content-between mb-2">'
            + '<div>'
            + '<div style="font-weight:700;font-size:.9rem;">' + row.area_name + '</div>'
            + '<code style="font-size:.72rem;color:var(--text-muted);">' + row.router_ip + '</code>'
            + '</div>'
            + '<span class="badge" style="' + statusClass + 'font-size:.7rem;">' + statusBadge + '</span>'
            + '</div>'
            + (row.online ? (
                '<div style="display:flex;gap:1rem;margin-bottom:.75rem;">'
                + '<div style="text-align:center;">'
                + '<div style="font-size:1.2rem;font-weight:700;color:var(--orange);">' + totalPools + '</div>'
                + '<div style="font-size:.65rem;color:var(--text-muted);">Pool</div>'
                + '</div>'
                + '<div style="text-align:center;">'
                + '<div style="font-size:1.2rem;font-weight:700;color:#3b82f6;">' + usedIPs + '</div>'
                + '<div style="font-size:.65rem;color:var(--text-muted);">Used IPs</div>'
                + '</div>'
                + '<div style="text-align:center;">'
                + '<div style="font-size:1.2rem;font-weight:700;color:#22c55e;">' + (totalIPs - usedIPs) + '</div>'
                + '<div style="font-size:.65rem;color:var(--text-muted);">Free IPs</div>'
                + '</div>'
                + '<div style="text-align:center;">'
                + '<div style="font-size:1.2rem;font-weight:700;color:' + pctColor + ';">' + usePct + '%</div>'
                + '<div style="font-size:.65rem;color:var(--text-muted);">Utilisasi</div>'
                + '</div>'
                + '</div>'
            ) : '')
            + poolsHtml
            + '</div>'
            + '</div>'
            + '</div>';

        grid.insertAdjacentHTML('beforeend', cardHtml);
    });
}

document.getElementById('refreshBtn').addEventListener('click', fetchData);

// Boot
fetchData();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/nms_ip_pool.blade.php ENDPATH**/ ?>