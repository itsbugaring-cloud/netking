
<?php $__env->startSection('title', 'NMS Devices'); ?>

<?php use App\Services\AcsService; ?>

<?php $__env->startSection('styles'); ?>
<style>
    .nms-devices-page [style*="color:#64748b"],
    .nms-devices-page [style*="color: #64748b"],
    .nms-devices-page [style*="color:#94a3b8"],
    .nms-devices-page [style*="color: #94a3b8"] {
        color: var(--txt-3) !important;
    }

    .nms-devices-page [style*="background:rgba(0,0,0,.06)"] {
        background: var(--surface-2) !important;
    }

    .nms-devices-page [style*="background:rgba(37,99,235,.08)"] {
        background: color-mix(in srgb, var(--nk-info) 10%, var(--surface)) !important;
        border-color: color-mix(in srgb, var(--nk-info) 22%, var(--border)) !important;
        color: var(--blue) !important;
    }

    .nms-devices-page code {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--blue);
    }

    .nms-devices-page .table a {
        text-decoration: none;
    }

    .nms-devices-page #nms-acs-table {
        min-width: 1540px !important;
    }

    .nms-devices-page #nms-acs-table td,
    .nms-devices-page #nms-acs-table th {
        vertical-align: top !important;
    }

    .nms-devices-page #nms-acs-table td code {
        white-space: nowrap;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nms-devices-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-chip'></i> Pemantauan Perangkat</div>
            <h1 class="ms-page-title">Perangkat NMS</h1>
        </div>
    </div>

    <div class="ms-panel mb-4">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title"><i class='bx bx-server me-2' style="color:var(--orange);"></i>Perangkat OLT</h5>
            </div>
            <span class="ms-kpi-chip"><strong><?php echo e($olts->count()); ?></strong> total</span>
        </div>
        <div class="ms-table-shell">
            <div class="table-responsive">
                <table class="table ms-table-wide mb-0" style="min-width:1040px;">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Nama</th>
                            <th style="min-width:150px;">IP Address</th>
                            <th style="min-width:200px;">Merek / Model</th>
                            <th style="min-width:110px;">Total ONT</th>
                            <th style="min-width:120px;">ONT Online</th>
                            <th style="min-width:180px;">Kesehatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $healthPct = $olt->onts_count > 0 ? round(($olt->online_count / $olt->onts_count) * 100) : 0; ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('admin.olts.show', $olt)); ?>" style="font-weight:600;color:inherit;text-decoration:none;"><?php echo e($olt->name); ?></a>
                            </td>
                            <td><code style="font-size:.78rem;"><?php echo e($olt->ip_address); ?></code></td>
                            <td><?php echo e($olt->brand); ?> <?php echo e($olt->model); ?></td>
                            <td><?php echo e($olt->onts_count); ?></td>
                            <td><span style="color:var(--green);font-weight:700;"><?php echo e($olt->online_count); ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="min-width:145px;">
                                    <div style="flex:1;height:6px;background:rgba(0,0,0,.06);border-radius:999px;overflow:hidden;">
                                        <div style="width:<?php echo e($healthPct); ?>%;height:100%;background:<?php echo e($healthPct > 90 ? 'var(--green)' : ($healthPct > 70 ? 'var(--orange)' : 'var(--red)')); ?>;"></div>
                                    </div>
                                    <span style="font-size:.72rem;font-weight:700;"><?php echo e($healthPct); ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ms-panel mb-4">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title"><i class='bx bx-devices me-2' style="color:var(--blue);"></i>Perangkat ACS</h5>
            </div>
            <div class="ms-toolbar-right">
                <span class="ms-kpi-chip"><strong><?php echo e($acsDevices->count()); ?></strong> total</span>
            </div>
        </div>
        <div class="ms-panel-body pt-0">
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <div class="d-flex gap-3 flex-wrap align-items-center">
                    <div class="input-group input-group-sm" style="max-width:320px;">
                        <span class="input-group-text" style="background:rgba(37,99,235,.08);border:1px solid rgba(37,99,235,.18);color:var(--blue);">
                            <i class='bx bx-search'></i>
                        </span>
                        <input type="text" id="nms-acs-search" class="form-control form-control-sm" placeholder="Cari serial, pelanggan, area..." style="border:1px solid rgba(37,99,235,.18);">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:.76rem;color:#64748b;font-weight:600;">Tampilkan</span>
                        <select id="nms-acs-length" class="form-select form-select-sm" style="width:86px;">
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="ms-btn-ghost nms-acs-filter-btn active" data-filter="all">Semua</button>
                <button type="button" class="ms-btn-ghost nms-acs-filter-btn" data-filter="offline">Offline</button>
                <button type="button" class="ms-btn-ghost nms-acs-filter-btn" data-filter="unassigned">Belum Ditetapkan</button>
                <button type="button" class="ms-btn-ghost nms-acs-filter-btn" data-filter="ambiguous">Ambigu</button>
                <button type="button" class="ms-btn-ghost nms-acs-filter-btn" data-filter="no-pppoe">Tanpa PPPoE</button>
                <button type="button" class="ms-btn-ghost nms-acs-filter-btn" data-filter="no-optical">Tanpa Optik</button>
                </div>
            </div>
        </div>
        <div class="ms-table-shell">
            <div class="table-responsive">
                <table class="table ms-table-wide mb-0" id="nms-acs-table" style="min-width:1460px;">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Serial / Perangkat</th>
                            <th style="min-width:220px;">Merek / Model</th>
                            <th style="min-width:250px;">Area / Pelanggan</th>
                            <th style="min-width:150px;">WAN IP</th>
                            <th style="min-width:140px;">PPPoE</th>
                            <th style="min-width:120px;">Daya RX</th>
                            <th style="min-width:180px;">SSID</th>
                            <th style="min-width:120px;">Terakhir Terlihat</th>
                            <th style="min-width:110px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $acsDevices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr
                            data-online="<?php echo e($device['online'] ? '1' : '0'); ?>"
                            data-area="<?php echo e(strtolower($device['area'] ?? 'unassigned')); ?>"
                            data-has-pppoe="<?php echo e(!empty($device['pppoe_user']) ? '1' : '0'); ?>"
                            data-has-optical="<?php echo e(!empty($device['rx_power']) ? '1' : '0'); ?>"
                        >
                            <td>
                                <span style="font-weight:600;font-size:.84rem;color:var(--blue);">
                                    <?php echo e(Str::limit($device['serial'] ?: $device['id'], 28)); ?>

                                </span>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?php echo e($device['manufacturer']); ?> <?php echo e($device['model']); ?></div>
                                <?php if(!empty($device['firmware'])): ?>
                                <div style="font-size:.74rem;color:#94a3b8;"><?php echo e($device['firmware']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?php echo e($device['customer_name'] ?: 'Pelanggan Belum Ditetapkan'); ?></div>
                                <div style="font-size:.76rem;color:#64748b;"><?php echo e($device['area'] ?? 'Belum Ditetapkan'); ?></div>
                            </td>
                            <td>
                                <?php if($device['wan_ip']): ?>
                                <code style="font-size:.78rem;"><?php echo e($device['wan_ip']); ?></code>
                                <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($device['pppoe_user'])): ?>
                                <code style="font-size:.78rem;"><?php echo e($device['pppoe_user']); ?></code>
                                <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($device['rx_power'])): ?>
                                <?php
                                    $rxVal = (float) str_replace(' dBm', '', $device['rx_power']);
                                    $rxColor = $rxVal > -25 ? 'var(--green)' : ($rxVal > -28 ? 'var(--orange)' : 'var(--red)');
                                ?>
                                <span style="font-size:.78rem;font-weight:700;color:<?php echo e($rxColor); ?>;"><?php echo e($device['rx_power']); ?></span>
                                <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($device['ssid'] ?: '—'); ?></td>
                            <td style="color:#64748b;"><?php echo e($device['last_seen']); ?></td>
                            <td>
                                <?php if($device['online']): ?>
                                <span class="badge-status badge-active">Online</span>
                                <?php else: ?>
                                <span class="badge-status badge-inactive">Offline</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada perangkat ACS ditemukan</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title"><i class='bx bx-wifi me-2' style="color:var(--green);"></i>Router Area</h5>
            </div>
        </div>
        <div class="ms-table-shell">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Area</th>
                            <th style="min-width:150px;">IP Router</th>
                            <th style="min-width:160px;">Pengguna</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo e($area->name); ?></td>
                            <td><code style="font-size:.78rem;"><?php echo e($area->router_ip ?? '—'); ?></code></td>
                            <td><?php echo e($area->router_user ?? '—'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
$(function() {
    var currentQuickFilter = 'all';
    var table = null;

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'nms-acs-table') return true;
        if (!table) return true;

        var row = table.row(dataIndex).node();
        if (!row) return true;

        var $row = $(row);
        if (currentQuickFilter === 'all') return true;
        if (currentQuickFilter === 'offline') return $row.data('online') === 0;
        if (currentQuickFilter === 'unassigned') return String($row.data('area')) === 'unassigned';
        if (currentQuickFilter === 'ambiguous') return String($row.data('area')) === 'ambiguous pppoe';
        if (currentQuickFilter === 'no-pppoe') return $row.data('has-pppoe') === 0;
        if (currentQuickFilter === 'no-optical') return $row.data('has-optical') === 0;
        return true;
    });

    <?php if($acsDevices->isNotEmpty()): ?>
    if ($.fn.DataTable) {
        table = $('#nms-acs-table').DataTable({
            dom: 'rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            pageLength: 25,
            order: [[7, 'asc']],
            autoWidth: false,
            scrollX: true,
            language: {
                search: '',
                searchPlaceholder: 'Filter perangkat...'
            }
        });
    }
    <?php endif; ?>

    $('.nms-acs-filter-btn').on('click', function() {
        currentQuickFilter = $(this).data('filter');
        $('.nms-acs-filter-btn').removeClass('ms-btn').addClass('ms-btn-ghost');
        $(this).removeClass('ms-btn-ghost').addClass('ms-btn');
        if (table) table.draw();
    });

    $('#nms-acs-search').on('keyup input', function() {
        if (table) table.search(this.value).draw();
    });

    $('#nms-acs-length').on('change', function() {
        if (table) table.page.len(parseInt(this.value, 10)).draw();
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/devices.blade.php ENDPATH**/ ?>