
<?php $__env->startSection('title', 'NMS Dashboard'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .nms-page .stat-value-sub {
        font-size: .8rem;
        color: var(--txt-3);
    }

    .nms-page .cell-strong {
        font-weight: 600;
        color: var(--txt);
    }

    .nms-page .cell-muted {
        font-size: .72rem;
        color: var(--txt-3);
    }

    .nms-page .metric-ok {
        color: var(--green) !important;
        font-weight: 600;
    }

    .nms-page .metric-bad {
        color: var(--red) !important;
        font-weight: 700;
    }

    .nms-page .metric-idle {
        color: var(--txt-3) !important;
        font-weight: 600;
    }

    .nms-page .nms-meter {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-width: 112px;
    }

    .nms-page .nms-meter-track {
        flex: 1;
        height: 6px;
        background: var(--surface-2);
        border-radius: 999px;
        overflow: hidden;
    }

    .nms-page .nms-meter-fill {
        height: 100%;
        border-radius: 999px;
    }

    .nms-page .nms-meter-value {
        font-size: .7rem;
        font-weight: 700;
    }

    .nms-page .nms-action-btn {
        background: color-mix(in srgb, var(--blue) 12%, var(--surface));
        color: var(--blue);
        border: 1px solid color-mix(in srgb, var(--blue) 22%, var(--border));
        border-radius: 8px;
        padding: 2px 8px;
    }

    .nms-page code {
        font-size: .75rem;
        background: var(--surface-2);
        color: var(--txt);
        padding: 4px 8px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-pulse'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Dasbor NMS</h1>
        </div>
        <div class="ms-page-actions">
            <span class="ms-kpi-chip is-success"><span class="live-dot" style="width:8px;height:8px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-green 2s infinite;"></span> Perbarui otomatis 30 detik</span>
        </div>
    </div>

<!-- NMS Stat Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">OLTs</div>
      <div class="stat-value" id="nms-olts"><?php echo e($stats['olt_count']); ?></div>
      <div class="stat-change neutral">Perangkat Terkelola</div>
    </div>
    <div class="stat-icon si-orange"><i class='bx bx-server'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">ONTs Online</div>
      <div class="stat-value" id="nms-ont-online" style="color:var(--green);"><?php echo e($stats['ont_online']); ?></div>
      <div class="stat-change up">↑ dari <?php echo e($stats['ont_total']); ?> total</div>
    </div>
    <div class="stat-icon si-green"><i class='bx bx-check-circle'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">ONTs Offline</div>
      <div class="stat-value" id="nms-ont-offline" style="color:var(--red);"><?php echo e($stats['ont_offline']); ?></div>
      <div class="stat-change down">↓ Tidak Terjangkau</div>
    </div>
    <div class="stat-icon si-red"><i class='bx bx-error-circle'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">Perangkat ACS</div>
      <div class="stat-value"><span id="nms-acs-online" style="color:var(--green);"><?php echo e($stats['acs_online']); ?></span> <span class="stat-value-sub">/ <?php echo e($stats['acs_total']); ?></span></div>
      <div class="stat-change up">↑ NETKING-ACS Online</div>
    </div>
    <div class="stat-icon si-blue"><i class='bx bx-chip'></i></div>
  </div>
</div>

<div class="row g-3 mb-3">
    <?php $__currentLoopData = $quickAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-6 col-md-3">
        <div class="ms-panel h-100">
            <div class="ms-panel-body py-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-label"><?php echo e($alert['label']); ?></div>
                    <div class="stat-value" style="font-size:1.5rem;"><?php echo e($alert['value']); ?></div>
                </div>
                <span class="badge-status <?php echo e($alert['tone']); ?>"><?php echo e($alert['label']); ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title"><i class='bx bx-map-pin me-2' style="color:var(--orange);"></i>Area Bermasalah Teratas</span>
            </div>
            <div class="ms-table-shell">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>ONT Offline</th>
                                <th>ACS Offline</th>
                                <th>Kesehatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $topProblemAreas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="cell-strong"><?php echo e($row['name']); ?></td>
                                <td><span class="<?php echo e($row['ont_offline'] > 0 ? 'metric-bad' : 'metric-idle'); ?>"><?php echo e($row['ont_offline']); ?></span></td>
                                <td><span class="<?php echo e($row['acs_offline'] > 0 ? 'metric-bad' : 'metric-idle'); ?>"><?php echo e($row['acs_offline']); ?></span></td>
                                <td>
                                    <?php
                                        $problemHealthColor = $row['health_score'] >= 90 ? 'var(--green)' : ($row['health_score'] >= 70 ? 'var(--orange)' : 'var(--red)');
                                    ?>
                                    <span class="nms-meter-value" style="color:<?php echo e($problemHealthColor); ?>;"><?php echo e($row['health_score']); ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada masalah area ditemukan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title"><i class='bx bx-server me-2' style="color:var(--red);"></i>OLT Terburuk</span>
            </div>
            <div class="ms-table-shell">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>OLT</th>
                                <th>Offline</th>
                                <th>Online</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $worstOlts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oltRow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <a href="<?php echo e(route('admin.olts.show', $oltRow['id'])); ?>" style="font-weight:600;color:inherit;text-decoration:none;">
                                        <?php echo e($oltRow['name']); ?>

                                    </a>
                                    <div class="cell-muted"><?php echo e($oltRow['brand'] ?: 'OLT'); ?></div>
                                </td>
                                <td><span class="<?php echo e($oltRow['offline_count'] > 0 ? 'metric-bad' : 'metric-idle'); ?>"><?php echo e($oltRow['offline_count']); ?></span></td>
                                <td><span class="metric-ok"><?php echo e($oltRow['online_count']); ?></span></td>
                                <td><?php echo e($oltRow['total_count']); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data OLT tersedia</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-8 col-lg-7">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title"><i class='bx bx-map-alt me-2' style="color:var(--blue);"></i>Matriks Kesehatan Area</span>
            </div>
            <div class="ms-table-shell">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="min-width:720px;">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>Pelanggan</th>
                                <th>ONT</th>
                                <th>ACS</th>
                                <th>Router</th>
                                <th>Kesehatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $areaHealth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $healthColor = $row['health_score'] >= 90 ? 'var(--green)' : ($row['health_score'] >= 70 ? 'var(--orange)' : 'var(--red)');
                            ?>
                            <tr>
                                <td class="cell-strong"><?php echo e($row['name']); ?></td>
                                <td><?php echo e($row['customers_count']); ?></td>
                                <td>
                                    <span class="metric-ok"><?php echo e($row['ont_online']); ?></span>
                                    <span class="cell-muted">/ <?php echo e($row['ont_total']); ?></span>
                                </td>
                                <td>
                                    <span class="metric-ok"><?php echo e($row['acs_online']); ?></span>
                                    <span class="cell-muted">/ <?php echo e($row['acs_total']); ?></span>
                                </td>
                                <td>
                                    <?php if($row['router_ready']): ?>
                                    <span class="badge-status badge-active">Siap</span>
                                    <?php else: ?>
                                    <span class="badge-status badge-inactive">Tidak Ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="nms-meter">
                                        <div class="nms-meter-track">
                                            <div class="nms-meter-fill" style="width:<?php echo e($row['health_score']); ?>%;background:<?php echo e($healthColor); ?>;"></div>
                                        </div>
                                        <span class="nms-meter-value" style="color:<?php echo e($healthColor); ?>;"><?php echo e($row['health_score']); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title"><i class='bx bx-signal-5 me-2' style="color:var(--red);"></i>Optik Kritis</span>
            </div>
            <div class="ms-table-shell">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="min-width:520px;">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Area</th>
                                <th>OLT</th>
                                <th>RX</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $criticalOpticals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ont): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="cell-strong"><?php echo e($ont->customer->name ?? $ont->serial_number); ?></td>
                                <td><?php echo e($ont->area->name ?? '—'); ?></td>
                                <td><?php echo e($ont->olt->name ?? '—'); ?></td>
                                <td><span class="metric-bad"><?php echo e(number_format((float) $ont->rx_power, 2)); ?> dBm</span></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada peringatan optik kritis</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OLT Health Cards -->
<div class="ms-panel mb-3">
    <div class="ms-panel-head">
        <span class="ms-panel-title"><i class='bx bx-server me-2' style="color:var(--orange);"></i>Ikhtisar Kesehatan OLT</span>
    </div>
    <div class="ms-table-shell">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Nama OLT</th>
                        <th>IP</th>
                        <th>Merek</th>
                        <th>ONTs Online</th>
                        <th>ONTs Offline</th>
                        <th>Kesehatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $onlineCount = $olt->online_count;
                        $totalCount = $olt->onts_count;
                        $offlineCount = $totalCount - $onlineCount;
                        $healthPct = $totalCount > 0 ? round(($onlineCount / $totalCount) * 100) : 0;
                        $healthColor = $healthPct > 90 ? 'var(--green)' : ($healthPct > 70 ? 'var(--orange)' : 'var(--red)');
                    ?>
                    <tr>
                        <td class="cell-strong">
                            <a href="<?php echo e(route('admin.olts.show', $olt)); ?>" style="color:inherit;text-decoration:none;">
                                <?php echo e($olt->name); ?>

                            </a>
                        </td>
                        <td><code><?php echo e($olt->ip_address); ?></code></td>
                        <td><?php echo e($olt->brand); ?> <?php echo e($olt->model); ?></td>
                        <td>
                            <span class="metric-ok"><?php echo e($onlineCount); ?></span>
                        </td>
                        <td>
                            <span class="<?php echo e($offlineCount > 0 ? 'metric-bad' : 'metric-idle'); ?>"><?php echo e($offlineCount); ?></span>
                        </td>
                        <td>
                            <div class="nms-meter">
                                <div class="nms-meter-track">
                                    <div class="nms-meter-fill" style="width:<?php echo e($healthPct); ?>%;background:<?php echo e($healthColor); ?>;transition:width .3s;"></div>
                                </div>
                                <span class="nms-meter-value" style="color:<?php echo e($healthColor); ?>;"><?php echo e($healthPct); ?>%</span>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo e(route('admin.olts.show', $olt)); ?>" class="btn btn-sm nms-action-btn">
                                <i class='bx bx-show' style="font-size:.85rem;"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Area / MikroTik Router Status -->
<div class="ms-panel mb-3">
    <div class="ms-panel-head">
        <span class="ms-panel-title"><i class='bx bx-wifi me-2' style="color:var(--blue);"></i>Status Area / Router MikroTik</span>
        <span class="cell-muted">Klik Uji untuk memeriksa konektivitas</span>
    </div>
    <div class="ms-table-shell">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>IP Router</th>
                        <th>Pelanggan</th>
                        <th>Status</th>
                        <th>Uji</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="cell-strong"><?php echo e($area->name); ?></td>
                        <td><code><?php echo e($area->router_ip ?? '—'); ?></code></td>
                        <td><?php echo e($area->customers_count); ?></td>
                        <td>
                            <span id="router-status-<?php echo e($area->id); ?>" class="badge-status badge-pending" style="font-size:.7rem;">Tidak Diketahui</span>
                        </td>
                        <td>
                            <?php if($area->router_ip): ?>
                            <button class="btn btn-sm nms-action-btn"
                                onclick="testRouter(<?php echo e($area->id); ?>)">
                                <i class='bx bx-refresh'></i> Uji
                            </button>
                        <?php else: ?>
                            <span class="cell-muted">Tidak ada router</span>
                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<style>
@keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.3; } }
</style>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    function testRouter(areaId) {
        var badge = document.getElementById('router-status-' + areaId);
        badge.className = 'badge-status badge-pending';
        badge.textContent = 'Menguji...';

        $.getJSON('<?php echo e(route("admin.nms.api-data")); ?>', { action: 'mikrotik_test', area_id: areaId })
            .done(function(d) {
                if (d.success) {
                    badge.className = 'badge-status badge-active';
                    badge.textContent = '● Online — ' + (d.identity || 'Terhubung');
                } else {
                    badge.className = 'badge-status badge-inactive';
                    badge.textContent = '○ Offline';
                }
            })
            .fail(function() {
                badge.className = 'badge-status badge-inactive';
                badge.textContent = '○ Gagal';
            });
    }

    // Auto-refresh NMS stats
    function refreshNmsStats() {
        $.getJSON('<?php echo e(route("admin.nms.api-data")); ?>', { action: 'summary' })
            .done(function(d) {
                $('#nms-ont-online').text(d.ont_online);
                $('#nms-ont-offline').text(d.ont_total - d.ont_online);
                $('#nms-acs-online').text(d.acs_online);
            });
    }
    setInterval(refreshNmsStats, 30000);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/dashboard.blade.php ENDPATH**/ ?>