
<?php $__env->startSection('title', 'NMS Alert Rules'); ?>

<?php $__env->startSection('styles'); ?>
<style>
  .alert-rules-page .icon-wrap.bg-danger-subtle,
  .alert-rules-page .bg-danger-subtle {
    background: color-mix(in srgb, var(--nk-danger) 12%, var(--surface)) !important;
    color: color-mix(in srgb, var(--nk-danger) 82%, var(--txt)) !important;
  }

  .alert-rules-page .icon-wrap.bg-warning-subtle,
  .alert-rules-page .bg-warning-subtle {
    background: color-mix(in srgb, var(--nk-warning) 12%, var(--surface)) !important;
    color: color-mix(in srgb, var(--nk-warning) 82%, var(--txt)) !important;
  }

  .alert-rules-page .text-dark {
    color: var(--txt) !important;
  }

  .alert-rules-page .text-muted {
    color: var(--txt-3) !important;
  }

  .alert-rules-page [style*="background:var(--bg-lighter)"] {
    background: var(--surface-2) !important;
    border-color: var(--border) !important;
  }

  .alert-rules-page .list-group-item {
    background: transparent !important;
    color: var(--txt) !important;
  }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page alert-rules-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-bell'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Aturan Peringatan</h1>
        </div>
    </div>

<div class="row g-3">
    <!-- Active Alerts: Offline Devices -->
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title mb-0 d-flex align-items-center">
                    <div class="icon-wrap bg-danger-subtle text-danger me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-error-circle'></i>
                    </div>
                    Peringatan Aktif (ONT Offline)
                </span>
                <span class="ms-chip"><?php echo e($offlineOnts->count()); ?></span>
            </div>
            <div class="ms-panel-body p-0">
                <?php if($offlineOnts->isEmpty()): ?>
                <div class="text-center py-4 text-muted mx-4 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
          <i class='bx bx-check-circle text-success fs-1 mb-2'></i>
                    <p class="mb-0 fw-medium">Sistem sehat. Tidak ada perangkat offline.</p>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php $__currentLoopData = $offlineOnts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ont): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-group-item border-0 py-3 px-4" style="border-bottom:1px solid var(--border-color) !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">
                                    <?php if($ont->customer_id && $ont->customer): ?>
                                      <a href="<?php echo e(route('admin.customers.show', $ont->customer_id)); ?>" class="text-dark text-decoration-none">
                                          <?php echo e($ont->customer->name); ?>

                                      </a>
                                    <?php else: ?>
                                      <span class="text-dark">ONT Belum Ditetapkan</span>
                                    <?php endif; ?>
                                </h6>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    <i class='bx bx-server pe-1'></i><?php echo e($ont->olt->name ?? 'OLT Tidak Diketahui'); ?> / PON <?php echo e($ont->pon_port); ?>

                                </div>
                                <div class="mt-1">
                                    <code class="bg-light px-2 py-1 rounded text-dark border shadow-sm" style="font-size:0.75rem;"><?php echo e($ont->sn ?: $ont->mac_address); ?></code>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold shadow-sm mb-2" style="font-size:0.7rem;">
                                    Offline
                                </span>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    <?php echo e($ont->updated_at->diffForHumans()); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent System Events -->
    <div class="col-lg-6">
        <div class="ms-panel h-100">
            <div class="ms-panel-head">
                <span class="ms-panel-title mb-0 d-flex align-items-center">
                    <div class="icon-wrap bg-warning-subtle text-warning me-2 rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class='bx bx-history'></i>
                    </div>
                    Kejadian Sistem Terbaru
                </span>
                <a href="<?php echo e(route('admin.nms.syslog')); ?>" class="ms-btn-secondary">Lihat Semua</a>
            </div>
            <div class="ms-panel-body p-0">
                <?php if($alerts->isEmpty()): ?>
                <div class="text-center py-4 text-muted mx-4 rounded-3" style="background:var(--bg-lighter);border:1px dashed var(--border-color);">
                    <i class='bx bx-info-circle fs-2 mb-2'></i>
                    <p class="mb-0 fw-medium">Tidak ada kejadian peringatan terbaru yang tercatat.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $icon = 'bx-info-circle';
                                $color = 'primary';
                                if(str_contains($alert->action, 'failed') || str_contains($alert->action, 'deleted')) { $icon = 'bx-error'; $color = 'danger'; }
                                elseif(str_contains($alert->action, 'suspended')) { $icon = 'bx-pause-circle'; $color = 'warning'; }
                                elseif(str_contains($alert->action, 'provisioned')) { $icon = 'bx-check-circle'; $color = 'success'; }
                            ?>
                            <tr>
                                <td class="ps-4" style="width:50px;">
                                    <div class="bg-<?php echo e($color); ?>-subtle text-<?php echo e($color); ?> rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:32px;height:32px;">
                                        <i class="bx <?php echo e($icon); ?> fs-6"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size:0.85rem;"><?php echo e($alert->description); ?></div>
                                    <div class="text-muted mt-1 d-flex align-items-center gap-2" style="font-size:0.75rem;">
                                        <span><i class='bx bx-user pe-1'></i><?php echo e($alert->user->name ?? 'Sistem'); ?></span>
                                        <span>&bull;</span>
                                        <span><?php echo e($alert->subject_type ? basename(str_replace('\\', '/', $alert->subject_type)).' #'.$alert->subject_id : '—'); ?></span>
                                    </div>
                                </td>
                                <td class="text-end pe-4 text-muted fw-medium" style="font-size:0.75rem;white-space:nowrap;">
                                    <?php echo e($alert->created_at->format('M d, H:i')); ?>

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
</div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/nms/alert-rules.blade.php ENDPATH**/ ?>