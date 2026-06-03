
<?php $__env->startSection('title', 'NMS Port Traffic'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-transfer'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Lalu Lintas Port</h1>
        </div>
    </div>

    <?php $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="ms-panel mb-3">
        <div class="ms-panel-head">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon si-blue" style="width:36px;height:36px;min-width:36px;">
                    <i class='bx bx-server'></i>
                </div>
                <div>
                    <div style="font-weight:700;color:var(--txt);"><?php echo e($olt->name); ?></div>
                    <div style="font-size:.75rem;color:var(--txt-3);">Merek: <?php echo e($olt->brand); ?> | IP: <?php echo e($olt->ip_address); ?></div>
                </div>
            </div>
            <span class="ms-chip"><?php echo e($olt->onts->count()); ?> ONTs</span>
        </div>
        <div class="ms-table-shell">
            <?php if($olt->onts->isEmpty()): ?>
            <div class="empty-state" style="padding:2rem 1rem;">
                <div class="empty-state-icon"><i class='bx bx-info-circle'></i></div>
                <div class="empty-state-title">Tidak ada ONT terdaftar</div>
                <div class="empty-state-desc">OLT ini belum memiliki inventaris ONT.</div>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="cell-nowrap">Port</th>
                            <th class="cell-nowrap cell-index">ID ONT</th>
                            <th class="cell-nowrap cell-serial">SN / Mac</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th>Sinyal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $currentPort = null; ?>
                        <?php $__currentLoopData = $olt->onts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ont): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($currentPort !== $ont->pon_port): ?>
                                <?php if($currentPort !== null): ?>
                                    <tr><td colspan="6" style="padding:0;height:4px;background:#f8fafc;"></td></tr>
                                <?php endif; ?>
                                <?php $currentPort = $ont->pon_port; ?>
                            <?php endif; ?>
                            <tr>
                                <td class="fw-semibold">
                                    <span class="ms-chip" style="min-height:28px;">PON <?php echo e($ont->pon_port); ?></span>
                                </td>
                                <td class="cell-nowrap cell-index">#<?php echo e($ont->olt_port_index ?? '—'); ?></td>
                                <td class="cell-nowrap cell-serial">
                                    <code class="bg-primary-subtle text-primary px-2 py-1 rounded" style="font-size:0.75rem;"><?php echo e($ont->serial_number ?: '—'); ?></code>
                                </td>
                                <td>
                                    <?php if($ont->customer): ?>
                                        <span class="fw-semibold" style="color:var(--txt);"><?php echo e($ont->customer->name); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Belum Ditetapkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($ont->status === 'online'): ?>
                                    <span class="badge-status badge-active">Online</span>
                                    <?php else: ?>
                                    <span class="badge-status badge-danger">Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $sig = (float)$ont->rx_power;
                                        $color = 'var(--txt-3)';
                                        if($sig < 0 && $sig > -30) {
                                            $color = $sig > -25 ? 'var(--green)' : ($sig > -28 ? 'var(--orange)' : 'var(--red)');
                                        }
                                    ?>
                                    <span style="color:<?php echo e($color); ?>;font-weight:700;"><?php echo e($ont->rx_power ?: '—'); ?> dBm</span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/ports.blade.php ENDPATH**/ ?>