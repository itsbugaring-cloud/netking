
<?php $__env->startSection('title', 'Ubah Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-user'></i> Manajemen Pelanggan</div>
      <h1 class="ms-page-title">Ubah Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="<?php echo e(route('admin.customers.show', $customer)); ?>" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <form action="<?php echo e(route('admin.customers.update', $customer)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:#2563eb;"></i>Informasi Pribadi</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $customer->name)); ?>" required>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('phone', $customer->phone)); ?>">
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Username App</label>
                <input type="text" name="username" class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                  value="<?php echo e(old('username', $customer->username)); ?>"
                  placeholder="Username untuk login ke aplikasi pelanggan"
                  autocomplete="off">
                <div class="form-text">Dipakai customer untuk login ke aplikasi. Boleh dikosongkan.</div>
                <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label d-flex justify-content-between align-items-center">
                  <span>Password App</span>
                  <?php if($customer->portal_password): ?>
                    <span style="font-size:.7rem;font-weight:500;color:#22c55e;"><i class='bx bx-check-circle me-1'></i>Sudah diset</span>
                  <?php else: ?>
                    <span style="font-size:.7rem;font-weight:500;color:#f59e0b;"><i class='bx bx-info-circle me-1'></i>Belum ada password</span>
                  <?php endif; ?>
                </label>
                <div class="input-group">
                  <input type="password" id="portal_password_input" name="portal_password"
                    class="form-control <?php $__errorArgs = ['portal_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="Kosongkan jika tidak ingin mengubah"
                    autocomplete="new-password">
                  <button type="button" class="input-group-text" onclick="toggleAppPassword()" title="Lihat/sembunyikan password" style="cursor:pointer;border-left:0;">
                    <i class='bx bx-hide' id="app-pass-eye" style="font-size:1rem;"></i>
                  </button>
                  <button type="button" class="input-group-text" onclick="generateAppPassword()" title="Generate password acak" style="cursor:pointer;background:#f0f5ff;color:#2563eb;font-size:.75rem;font-weight:600;white-space:nowrap;">
                    <i class='bx bx-refresh me-1'></i>Generate
                  </button>
                  <?php $__errorArgs = ['portal_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="form-text">Min. 6 karakter. <span id="generated-pass-info" style="display:none;color:#2563eb;font-weight:600;"></span></div>
              </div>
              <div class="col-12">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="2"><?php echo e(old('address', $customer->address)); ?></textarea>
                <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
            </div>
          </div>
        </div>

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-wifi me-2' style="color:#2563eb;"></i>PPPoE & Jaringan</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">PPPoE Username</label>
                <input type="text" class="form-control" value="<?php echo e($customer->pppoe_user); ?>" disabled>
                <div class="form-text">Tidak dapat diubah setelah dibuat.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Area</label>
                <select name="area_id" class="form-select <?php $__errorArgs = ['area_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                  <option value="">Pilih Area</option>
                  <?php $__currentLoopData = $areas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($area->id); ?>" <?php echo e($customer->area_id == $area->id ? 'selected' : ''); ?>><?php echo e($area->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['area_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Paket</label>
                <select name="package_id" class="form-select <?php $__errorArgs = ['package_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                  <option value="">Pilih Paket</option>
                  <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($package->id); ?>" <?php echo e($customer->package_id == $package->id ? 'selected' : ''); ?>>
                    <?php echo e($package->name); ?> (<?php echo e($package->formatted_price); ?>)
                  </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['package_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Harga Khusus</label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" id="package-price" name="package_price" class="form-control <?php $__errorArgs = ['package_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('package_price', $customer->package_price)); ?>" placeholder="Kosongkan untuk harga default">
                </div>
                <?php $__errorArgs = ['package_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tanggal Mulai Tagihan <span class="text-danger">*</span></label>
                <input type="date" id="billing-start-date" name="billing_start_date" class="form-control <?php $__errorArgs = ['billing_start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('billing_start_date', optional($customer->billing_start_date)->toDateString() ?? optional($customer->created_at)->toDateString())); ?>" required>
                <div class="form-text">Dipakai untuk hitung prorata invoice bulan pertama.</div>
                <?php $__errorArgs = ['billing_start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-12">
                <div id="proration-preview" class="alert alert-info py-2 mb-0" style="font-size:.82rem;display:none;">
                  <strong>Preview Prorata:</strong> <span id="proration-preview-text">-</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">ODP</label>
                <select name="odp_id" class="form-select <?php $__errorArgs = ['odp_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                  <option value="">Tanpa ODP</option>
                  <?php $__currentLoopData = $odps ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $odp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($odp->id); ?>" <?php echo e(old('odp_id', $customer->odp_id) == $odp->id ? 'selected' : ''); ?>><?php echo e($odp->name); ?> (<?php echo e($odp->code); ?>) — <?php echo e($odp->available_slots); ?> slot</option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['odp_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="col-md-6">
                <label class="form-label">Port ODP</label>
                <input type="number" name="odp_port" class="form-control <?php $__errorArgs = ['odp_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('odp_port', $customer->odp_port)); ?>" min="1" max="128" placeholder="Nomor port">
                <?php $__errorArgs = ['odp_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="<?php echo e(route('admin.customers.show', $customer)); ?>" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Perubahan</button>
          </div>
        </div>
      </form>
    </div>

    <div class="col-lg-4">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-info-circle me-2' style="color:#2563eb;"></i>Info Cepat</h5>
        </div>
        <div class="ms-panel-body">
          <div class="mb-3 pb-3" style="border-bottom:1px solid #eef2f7;">
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Status</div>
            <div class="mt-1">
              <?php if($customer->status === 'active'): ?>
              <span class="badge-status badge-active">Aktif</span>
              <?php elseif($customer->status === 'pending'): ?>
              <span class="badge-status badge-pending">Pending</span>
              <?php else: ?>
              <span class="badge-status badge-inactive">Tidak Aktif</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="mb-3 pb-3" style="border-bottom:1px solid #eef2f7;">
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Mitra</div>
            <div class="mt-1"><?php echo e($customer->partner->name ?? 'Langsung'); ?></div>
          </div>
          <div class="mb-3 pb-3" style="border-bottom:1px solid #eef2f7;">
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Remote IP</div>
            <div class="mt-1"><code><?php echo e($customer->remote_ip ?? 'Dinamis'); ?></code></div>
          </div>
          <div>
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Dibuat</div>
            <div class="mt-1"><?php echo e($customer->created_at->format('d M Y, H:i')); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const DUE_DAY = <?php echo e((int) config('billing.invoice_due_day', 20)); ?>;
const BASE_DAYS = <?php echo e((int) config('billing.proration_base_days', 30)); ?>;

function toggleAppPassword() {
  const input = document.getElementById('portal_password_input');
  const eye   = document.getElementById('app-pass-eye');
  if (input.type === 'password') {
    input.type = 'text';
    eye.className = 'bx bx-show';
  } else {
    input.type = 'password';
    eye.className = 'bx bx-hide';
  }
}

function generateAppPassword() {
  const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789@#!';
  let pass = '';
  for (let i = 0; i < 10; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  const input = document.getElementById('portal_password_input');
  input.value = pass;
  input.type  = 'text';
  document.getElementById('app-pass-eye').className = 'bx bx-show';

  const info = document.getElementById('generated-pass-info');
  info.style.display = 'inline';
  info.textContent   = 'Password: ' + pass + ' (catat sebelum simpan!)';
}

function parseYmd(value) {
  if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
  const [y, m, d] = value.split('-').map(Number);
  const dt = new Date(y, m - 1, d, 0, 0, 0, 0);
  return isNaN(dt.getTime()) ? null : dt;
}

function monthDays(year, month) {
  return new Date(year, month, 0).getDate();
}

function resolveDueDate(year, month, day) {
  const safe = Math.max(1, Math.min(day, monthDays(year, month)));
  return new Date(year, month - 1, safe, 0, 0, 0, 0);
}

function formatIdr(num) {
  return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function diffDays(fromDate, toDate) {
  const msPerDay = 24 * 60 * 60 * 1000;
  return Math.max(0, Math.floor((toDate - fromDate) / msPerDay));
}

function refreshProrationPreview() {
  const priceEl = document.getElementById('package-price');
  const startEl = document.getElementById('billing-start-date');
  const previewEl = document.getElementById('proration-preview');
  const previewTextEl = document.getElementById('proration-preview-text');

  if (!priceEl || !startEl || !previewEl || !previewTextEl) return;

  const price = Number(priceEl.value || 0);
  const startDate = parseYmd(startEl.value);
  if (!price || !startDate) {
    previewEl.style.display = 'none';
    return;
  }

  const now = new Date();
  const periodYear = now.getFullYear();
  const periodMonth = now.getMonth() + 1;
  const dueDate = resolveDueDate(periodYear, periodMonth, DUE_DAY);

  const prevMonth = periodMonth === 1 ? 12 : periodMonth - 1;
  const prevYear = periodMonth === 1 ? periodYear - 1 : periodYear;
  const windowStart = resolveDueDate(prevYear, prevMonth, DUE_DAY);

  if (startDate >= dueDate) {
    previewTextEl.textContent = 'Tanggal mulai tagihan berada pada/di atas tanggal jatuh tempo periode ini. Tagihan akan masuk periode berikutnya.';
    previewEl.style.display = 'block';
    return;
  }

  const billableStart = startDate > windowStart ? startDate : windowStart;
  const billedDays = Math.max(0, Math.min(diffDays(billableStart, dueDate), BASE_DAYS));
  const dailyRate = price / BASE_DAYS;
  const amount = dailyRate * billedDays;

  if (billedDays >= BASE_DAYS) {
    previewTextEl.textContent = 'Full cycle ' + BASE_DAYS + ' hari (tanpa prorata): ' + formatIdr(amount) + '.';
  } else {
    previewTextEl.textContent = 'Prorata ' + billedDays + ' hari x ' + formatIdr(dailyRate) + '/hari = ' + formatIdr(amount) + ' (jatuh tempo tgl ' + DUE_DAY + ').';
  }
  previewEl.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
  const priceEl = document.getElementById('package-price');
  const startEl = document.getElementById('billing-start-date');
  if (priceEl) {
    priceEl.addEventListener('input', refreshProrationPreview);
    priceEl.addEventListener('change', refreshProrationPreview);
  }
  if (startEl) {
    startEl.addEventListener('input', refreshProrationPreview);
    startEl.addEventListener('change', refreshProrationPreview);
  }
  refreshProrationPreview();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/netking.id/resources/views/admin/customers/edit.blade.php ENDPATH**/ ?>