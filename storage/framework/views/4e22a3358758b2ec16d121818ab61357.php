
<?php $__env->startSection('title', 'Dasbor Inventaris'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page inv-dashboard-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-package'></i> Inventaris</div>
      <h1 class="ms-page-title">Dashboard Inventaris</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.inventory.units.create')); ?>" class="ms-btn"><i class='bx bx-plus'></i> Tambah Unit</a>
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

  <div class="ms-stat-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1rem;">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-primary"><i class='bx bx-chip'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Unit SN</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_unit ?? 0)); ?></div>
        <div class="ms-stat-meta">gudang: <?php echo e(number_format($unit_gudang ?? 0)); ?> · terpasang: <?php echo e(number_format($unit_terpasang ?? 0)); ?></div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info"><i class='bx bx-transfer'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Kabel Haspel</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_kabel_haspel ?? 0)); ?></div>
        <div class="ms-stat-meta"><?php echo e(number_format($total_sisa_meter ?? 0, 1)); ?> m sisa</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success"><i class='bx bx-box'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Stok Qty</div>
        <div class="ms-stat-value"><?php echo e(number_format($total_qty_jenis ?? 0)); ?></div>
        <div class="ms-stat-meta">jenis barang tersedia</div>
      </div>
    </div>
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-warning"><i class='bx bx-dollar-circle'></i></div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Nilai Aset</div>
        <div class="ms-stat-value">Rp <?php echo e(number_format(($total_nilai_aset ?? 0) / 1000000, 1)); ?>M</div>
        <div class="ms-stat-meta">estimasi seluruh aset</div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-7">
      <div class="ms-panel h-100">
        <div class="ms-panel-head">
          <div>
            <h5 class="ms-panel-title">Distribusi per Lokasi</h5>
            <div class="ms-panel-subtitle">Rekap stok per gudang / POP</div>
          </div>
          <div class="ms-toolbar-right">
            <a href="<?php echo e(route('admin.inventory.lokasi.index')); ?>" class="ms-btn-ghost ms-btn-sm">
              <i class='bx bx-map-pin'></i> Kelola Lokasi
            </a>
          </div>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Lokasi</th>
                  <th>Jenis</th>
                  <th class="text-end">Unit</th>
                  <th class="text-end">Haspel</th>
                  <th class="text-end">Qty</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $per_lokasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td><?php echo e($row->nama_lokasi ?? $row['nama_lokasi'] ?? '-'); ?></td>
                  <td>
                    <?php $jenis = $row->jenis ?? $row['jenis'] ?? ''; ?>
                    <?php if($jenis === 'pop_distribusi'): ?>
                      <span class="badge badge-success">POP</span>
                    <?php else: ?>
                      <span class="badge badge-info">Gudang Utama</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end"><?php echo e(number_format($row->total_unit ?? $row['total_unit'] ?? 0)); ?></td>
                  <td class="text-end"><?php echo e(number_format($row->total_haspel ?? $row['total_haspel'] ?? 0)); ?></td>
                  <td class="text-end"><?php echo e(number_format($row->total_qty ?? $row['total_qty'] ?? 0)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5">
                  <div class="empty-state">
                    <div class="empty-state-icon"><i class='bx bx-map'></i></div>
                    <div class="empty-state-title">Belum ada data lokasi</div>
                    <div class="empty-state-desc">Tambahkan lokasi gudang terlebih dahulu</div>
                  </div>
                </td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="ms-panel h-100">
        <div class="ms-panel-head">
          <div>
            <h5 class="ms-panel-title">10 Aktivitas Terakhir</h5>
            <div class="ms-panel-subtitle">Log mutasi inventori terbaru</div>
          </div>
          <div class="ms-toolbar-right">
            <a href="<?php echo e(route('admin.inventory.history.index')); ?>" class="ms-btn-ghost ms-btn-sm">
              <i class='bx bx-history'></i> Semua
            </a>
          </div>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Tipe</th>
                  <th>Pelaku</th>
                </tr>
              </thead>
              <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recent_log; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td style="white-space:nowrap;font-size:0.82rem;color:var(--txt-muted)">
                    <?php echo e(\Carbon\Carbon::parse($log->created_at)->diffForHumans()); ?>

                  </td>
                  <td>
                    <?php
                      $tipeMap = [
                        'masuk_baru'    => ['badge-success', 'Masuk'],
                        'mutasi'        => ['badge-info',    'Mutasi'],
                        'potong_kabel'  => ['badge-warning', 'Potong'],
                        'pasang'        => ['badge-info',    'Pasang'],
                        'retur'         => ['badge-danger',  'Retur'],
                        'barang_keluar' => ['badge-danger',  'Keluar'],
                        'penyesuaian'   => ['badge-inactive','Sesuai'],
                      ];
                      $tc = $tipeMap[$log->tipe] ?? ['badge-inactive', $log->tipe];
                    ?>
                    <span class="badge <?php echo e($tc[0]); ?>"><?php echo e($tc[1]); ?></span>
                  </td>
                  <td style="font-size:0.85rem"><?php echo e($log->user->name ?? $log->pelaku ?? '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="3">
                  <div class="empty-state">
                    <div class="empty-state-icon"><i class='bx bx-history'></i></div>
                    <div class="empty-state-title">Belum ada aktivitas</div>
                  </div>
                </td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  
  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title">Navigasi Cepat</h5>
    </div>
    <div class="ms-panel-body d-flex gap-2 flex-wrap">
      <a href="<?php echo e(route('admin.inventory.units.index')); ?>" class="ms-btn-secondary">
        <i class='bx bx-chip'></i> Unit &amp; SN
      </a>
      <a href="<?php echo e(route('admin.inventory.kabel.index')); ?>" class="ms-btn-secondary">
        <i class='bx bx-transfer'></i> Kabel Haspel
      </a>
      <a href="<?php echo e(route('admin.inventory.qty.index')); ?>" class="ms-btn-secondary">
        <i class='bx bx-box'></i> Stok Qty
      </a>
      <a href="<?php echo e(route('admin.inventory.history.index')); ?>" class="ms-btn-secondary">
        <i class='bx bx-history'></i> Riwayat Log
      </a>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/inventory/dashboard.blade.php ENDPATH**/ ?>