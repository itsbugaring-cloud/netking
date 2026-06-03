
<?php $__env->startSection('title', 'Stok Kabel (Haspel)'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page nk-list-page inv-kabel-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-transfer'></i> Inventaris</div>
      <h1 class="ms-page-title">Stok Kabel Haspel</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.inventory.kabel.create')); ?>" class="ms-btn"><i class='bx bx-plus'></i> Tambah Kabel</a>
    </div>
  </div>

  <?php if(session('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
  <?php if(session('error')): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="ms-stat-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1rem;">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info"><i class='bx bx-transfer'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Haspel</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_haspel ?? 0)); ?></div>
        <div class="ms-stat-meta">haspel terdaftar</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success"><i class='bx bx-ruler'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Sisa Meter</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_sisa ?? 0, 1)); ?></div>
        <div class="ms-stat-meta">meter tersisa</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-warning"><i class='bx bx-dollar-circle'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Nilai Kabel</div>
        <div class="ms-stat-value">Rp <?php echo e(number_format(($total_nilai ?? 0) / 1000000, 1)); ?>M</div>
        <div class="ms-stat-meta">estimasi nilai stok</div>
      </div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title">Daftar Kabel Haspel</h5>
        <div class="ms-panel-subtitle">Stok kabel fiber dan copper per haspel</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-data'></i> <?php echo e($kabels->total()); ?> haspel</span>
      </div>
    </div>

    <div class="ms-toolbar">
      <div class="ms-toolbar-left">
        <form method="GET" action="<?php echo e(route('admin.inventory.kabel.index')); ?>" class="ms-filter-form">
          <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                 placeholder="Cari ID haspel..." class="form-control form-control-sm">
          <select name="lokasi_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Lokasi</option>
            <?php $__currentLoopData = $lokasi_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($loc->id); ?>" <?php echo e(request('lokasi_id') == $loc->id ? 'selected' : ''); ?>>
                <?php echo e($loc->nama_lokasi); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <button type="submit" class="ms-btn-secondary ms-btn-sm"><i class='bx bx-search'></i></button>
          <?php if(request()->anyFilled(['search','lokasi_id'])): ?>
          <a href="<?php echo e(route('admin.inventory.kabel.index')); ?>" class="ms-btn-ghost ms-btn-sm">Reset Filter</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>ID Haspel</th>
              <th>Jenis Kabel</th>
              <th class="text-end">Panjang Awal (m)</th>
              <th class="text-end">Sisa (m)</th>
              <th style="min-width:120px">Penggunaan</th>
              <th class="text-end">Nilai/m (Rp)</th>
              <th>Lokasi</th>
              <th style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $kabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
              $pct = $kabel->panjang_awal > 0
                ? round((($kabel->panjang_awal - ($kabel->sisa_meter ?? $kabel->panjang_awal)) / $kabel->panjang_awal) * 100)
                : 0;
              $barColor = $pct >= 80 ? 'var(--red,#ef4444)' : ($pct >= 50 ? 'var(--orange,#f59e0b)' : 'var(--green)');
            ?>
            <tr>
              <td><code><?php echo e($kabel->id_haspel); ?></code></td>
              <td><?php echo e($kabel->masterBarang->merek ?? ''); ?> <?php echo e($kabel->masterBarang->tipe ?? $kabel->jenis_kabel ?? '-'); ?></td>
              <td class="text-end"><?php echo e(number_format($kabel->panjang_awal, 1)); ?></td>
              <td class="text-end"><?php echo e(number_format($kabel->sisa_meter ?? $kabel->panjang_awal, 1)); ?></td>
              <td>
                <div style="height:6px;border-radius:999px;background:var(--border);overflow:hidden;">
                  <div style="width:<?php echo e($pct); ?>%;height:100%;background:<?php echo e($barColor); ?>;border-radius:999px;"></div>
                </div>
                <div style="font-size:.72rem;color:var(--txt-3);margin-top:2px"><?php echo e($pct); ?>% terpakai</div>
              </td>
              <td class="text-end">
                <?php if($kabel->nilai_per_meter): ?>
                  <?php echo e(number_format($kabel->nilai_per_meter, 0, ',', '.')); ?>

                <?php else: ?>
                  <span style="color:var(--txt-3)">-</span>
                <?php endif; ?>
              </td>
              <td><?php echo e($kabel->lokasi->nama_lokasi ?? '-'); ?></td>
              <td>
                <div class="d-flex gap-1">
                  <a href="<?php echo e(route('admin.inventory.kabel.show', $kabel)); ?>" class="nk-action-btn view" title="Detail">
                    <i class='bx bx-show'></i>
                  </a>
                  <a href="<?php echo e(route('admin.inventory.kabel.edit', $kabel)); ?>" class="nk-action-btn edit" title="Edit">
                    <i class='bx bx-edit'></i>
                  </a>
                  <form action="<?php echo e(route('admin.inventory.kabel.destroy', $kabel)); ?>" method="POST" class="m-0"
                        onsubmit="return confirm('Hapus haspel ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8">
              <div class="empty-state">
                <div class="empty-state-icon"><i class='bx bx-transfer'></i></div>
                <div class="empty-state-title">Belum ada kabel haspel</div>
                <div class="empty-state-desc">Mulai tambahkan haspel pertama</div>
              </div>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if($kabels->hasPages()): ?>
    <div class="ms-panel-footer"><?php echo e($kabels->withQueryString()->links()); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/inventory/kabel/index.blade.php ENDPATH**/ ?>