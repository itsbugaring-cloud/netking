
<?php $__env->startSection('title', 'Paket Internet'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nk-list-page packages-index-page">
<div class="ms-page-head">
    <div>
        <div class="ms-page-kicker"><i class='bx bx-package'></i> Katalog Layanan</div>
        <h1 class="ms-page-title">Paket Internet</h1>
    </div>
    <div class="ms-page-actions">
        <form method="POST" action="<?php echo e(route('admin.packages.sync-mikrotik')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="ms-btn-secondary">
                <i class='bx bx-refresh'></i> Sinkronisasi dari MikroTik
            </button>
        </form>
        <a href="<?php echo e(route('admin.packages.create')); ?>" class="ms-btn">
            <i class='bx bx-plus'></i> Tambah Paket Baru
        </a>
    </div>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success mb-3" style="border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-check-circle me-2'></i><?php echo e(session('success')); ?>

</div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert mb-3" style="background:color-mix(in srgb,var(--red) 10%,var(--surface));border:1px solid color-mix(in srgb,var(--red) 25%,var(--border));color:var(--red);border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-error-circle me-2'></i><?php echo e(session('error')); ?>

</div>
<?php endif; ?>

<?php
    $totalPkg = $packages->count();
    $activePkg = $packages->where('is_active', true)->count();
    $totalCust = $packages->sum('customers_count');
    $totalMRR = $packages->sum(function($p) { return $p->is_active ? $p->price * $p->customers_count : 0; });
?>
<div class="ms-stat-grid mb-4">
    <div class="ms-stat-card" style="--stat-accent:var(--blue);--stat-bg:color-mix(in srgb,var(--blue) 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-package'></i></div>
        <div><div class="ms-stat-label">Total Paket</div><div class="ms-stat-value"><?php echo e($totalPkg); ?></div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:var(--green);--stat-bg:color-mix(in srgb,var(--green) 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-check-circle'></i></div>
        <div><div class="ms-stat-label">Aktif</div><div class="ms-stat-value" style="color:var(--green);"><?php echo e($activePkg); ?></div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:var(--orange,#f97316);--stat-bg:color-mix(in srgb,var(--orange,#f97316) 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-user-check'></i></div>
        <div><div class="ms-stat-label">Pelanggan</div><div class="ms-stat-value"><?php echo e($totalCust); ?></div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#a855f7;--stat-bg:color-mix(in srgb,#a855f7 8%,var(--surface));">
        <div class="ms-stat-icon"><i class='bx bx-money'></i></div>
        <div><div class="ms-stat-label">Estimasi MRR</div><div class="ms-stat-value" style="font-size:1rem;">Rp <?php echo e(number_format($totalMRR, 0, ',', '.')); ?></div></div>
    </div>
</div>

<div class="ms-panel">
    <div class="ms-panel-head d-flex align-items-center justify-content-between">
        <span class="ms-panel-title">
            <i class='bx bx-package me-2' style="color:var(--orange,#f97316);"></i>Semua Paket
        </span>
    </div>
    <div class="ms-table-shell">
    <div class="nk-table-controls">
        <div class="nk-search-wrap nk-table-search-trigger">
            <i class='bx bx-search'></i>
            <input type="text" id="pkg-search" class="nk-search-input" placeholder="Cari paket...">
        </div>
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:.78rem;color:var(--txt-3);">Tampilkan</span>
            <select id="pkg-length" class="nk-length-select">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-flat mb-0" id="packages-table" style="min-width:1260px;">
            <thead>
                <tr>
                    <th style="min-width:120px;">Status</th>
                    <th style="min-width:220px;">Nama / Kode</th>
                    <th style="min-width:170px;">Area</th>
                    <th style="min-width:170px;">Kecepatan (DL/UL)</th>
                    <th style="min-width:140px;">Harga</th>
                    <th style="min-width:180px;">Profil MikroTik</th>
                    <th style="min-width:110px;">Pelanggan</th>
                    <th style="min-width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e(!$package->is_active ? 'pppoe-offline-row' : ''); ?>">
                    <td>
                        <?php if($package->is_active): ?>
                        <span class="badge-status badge-active">Aktif</span>
                        <?php else: ?>
                        <span class="badge-status badge-inactive">Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:.85rem;"><?php echo e($package->name); ?></div>
                        <div style="font-size:.7rem;color:var(--txt-3);"><?php echo e($package->code); ?></div>
                    </td>
                    <td>
                        <?php if($package->area): ?>
                        <span class="badge" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);font-size:.7rem;"><?php echo e($package->area->name); ?></span>
                        <?php else: ?>
                        <span style="font-size:.7rem;color:var(--txt-3);">Global</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:.8rem;">
                            <i class='bx bx-down-arrow-alt' style="color:var(--green);font-size:.7rem;"></i>
                            <strong><?php echo e($package->speed_down); ?></strong> Mbps
                        </div>
                        <div style="font-size:.75rem;color:var(--txt-3);">
                            <i class='bx bx-up-arrow-alt' style="color:var(--blue);font-size:.7rem;"></i>
                            <?php echo e($package->speed_up); ?> Mbps
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:.85rem;">Rp <?php echo e(number_format($package->price, 0, ',', '.')); ?></div>
                        <div style="font-size:.65rem;color:var(--txt-3);">/ bulan</div>
                    </td>
                    <td>
                        <?php if($package->mikrotik_profile): ?>
                        <code><?php echo e($package->mikrotik_profile); ?></code>
                        <?php else: ?>
                        <span style="font-size:.75rem;color:var(--txt-3);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);font-weight:600;font-size:.78rem;padding:2px 8px;border-radius:4px;">
                            <?php echo e($package->customers_count); ?>

                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?php echo e(route('admin.packages.edit', $package)); ?>" class="nk-action-btn edit" title="Edit">
                                <i class='bx bx-edit-alt'></i>
                            </a>
                            <form action="<?php echo e(route('admin.packages.destroy', $package)); ?>" method="POST" class="d-inline" data-confirm="Hapus paket ini?">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="nk-action-btn delete" title="Hapus">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-4" style="color:var(--txt-3);">
                        <i class='bx bx-package' style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                        Belum ada paket. <a href="<?php echo e(route('admin.packages.create')); ?>">Buat paket pertama</a>.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
</div>
</div>

<style>
    .pppoe-offline-row { opacity: .5; }
    .pppoe-offline-row:hover { opacity: 1; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    $(function() {
        var table = $('#packages-table').DataTable({
            dom: '<rt><"d-flex justify-content-between align-items-center mt-3"ip>',
            pageLength: 25,
            autoWidth: false,
            scrollX: true,
            order: [[1, 'asc']],
            language: {
                info: '_START_-_END_ dari _TOTAL_',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Tidak ditemukan',
                paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
            },
            columnDefs: [{ orderable: false, targets: [7] }]
        });
        $('#pkg-search').on('input', function() { table.search(this.value).draw(); });
        $('#pkg-length').on('change', function() { table.page.len(+this.value).draw(); });
        $('form[data-confirm]').on('submit', function(e) {
            if (!confirm($(this).data('confirm'))) e.preventDefault();
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/packages/index.blade.php ENDPATH**/ ?>