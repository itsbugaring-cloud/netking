
<?php $__env->startSection('title', 'NMS Syslog'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-file-blank'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Log Aktivitas Sistem</h1>
        </div>
    </div>

<div class="ms-panel">
    <div class="ms-panel-head">
        <h5 class="ms-panel-title"><i class='bx bx-list-ul me-2' style="color:var(--blue);"></i>Riwayat Kejadian</h5>
    </div>
    <div class="ms-table-shell">
        <?php if($logs->isEmpty()): ?>
        <div class="empty-state" style="padding:2rem 1rem;">
            <div class="empty-state-icon"><i class='bx bx-info-circle'></i></div>
            <div class="empty-state-title">Tidak ada log aktivitas sistem ditemukan</div>
            <div class="empty-state-desc">Tindakan terbaru, pekerjaan sinkronisasi, dan perubahan akan tampil di sini.</div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="syslog-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Tindakan</th>
                        <th>Keterangan</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="text-muted fw-medium" style="font-size:0.8rem;white-space:nowrap;"><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                        <td>
                            <?php if($log->user): ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:24px;height:24px;font-size:0.7rem;">
                                    <?php echo e(substr($log->user->name, 0, 1)); ?>

                                </span>
                                <span class="fw-semibold text-dark fs-7"><?php echo e($log->user->name); ?></span>
                            </div>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border px-2 py-1"><i class='bx bx-bot me-1'></i>Sistem</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $color = 'secondary';
                                if(str_contains($log->action, 'create') || str_contains($log->action, 'provision')) $color = 'success';
                                elseif(str_contains($log->action, 'update') || str_contains($log->action, 'change')) $color = 'primary';
                                elseif(str_contains($log->action, 'delete') || str_contains($log->action, 'fail') || str_contains($log->action, 'suspend')) $color = 'danger';
                            ?>
                            <span class="badge bg-<?php echo e($color); ?>-subtle text-<?php echo e($color); ?> px-2 py-1 rounded-pill fw-semibold shadow-sm" style="font-size:0.7rem;">
                                <?php echo e(str_replace('_', ' ', strtoupper($log->action))); ?>

                            </span>
                        </td>
                        <td class="text-dark fw-medium" style="font-size:0.85rem;"><?php echo e($log->description); ?></td>
                        <td class="text-muted" style="font-size:0.75rem;">
                            <?php if($log->subject_type): ?>
                                <?php echo e(basename(str_replace('\\', '/', $log->subject_type))); ?>

                                <code class="bg-light px-1 rounded shadow-sm">#<?php echo e($log->subject_id); ?></code>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
$(function() {
    if ($.fn.DataTable) {
        $('#syslog-table').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            language: { search: 'Filter Log:' }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/syslog.blade.php ENDPATH**/ ?>