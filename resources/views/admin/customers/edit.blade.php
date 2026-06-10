@extends('layouts.app')
@section('title', 'Ubah Pelanggan')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-user'></i> Manajemen Pelanggan</div>
      <h1 class="ms-page-title">Ubah Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.customers.show', $customer) }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:#2563eb;"></i>Informasi Pribadi</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone) }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Username App</label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                  value="{{ old('username', $customer->username) }}"
                  placeholder="Username untuk login ke aplikasi pelanggan"
                  autocomplete="off">
                <div class="form-text">Dipakai customer untuk login ke aplikasi. Boleh dikosongkan.</div>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label d-flex justify-content-between align-items-center">
                  <span>Password App</span>
                  @if($customer->portal_password)
                    <span style="font-size:.7rem;font-weight:500;color:#22c55e;"><i class='bx bx-check-circle me-1'></i>Sudah diset</span>
                  @else
                    <span style="font-size:.7rem;font-weight:500;color:#f59e0b;"><i class='bx bx-info-circle me-1'></i>Belum ada password</span>
                  @endif
                </label>
                <div class="input-group">
                  <input type="password" id="portal_password_input" name="portal_password"
                    class="form-control @error('portal_password') is-invalid @enderror"
                    placeholder="Kosongkan jika tidak ingin mengubah"
                    autocomplete="new-password">
                  <button type="button" class="input-group-text" onclick="toggleAppPassword()" title="Lihat/sembunyikan password" style="cursor:pointer;border-left:0;">
                    <i class='bx bx-hide' id="app-pass-eye" style="font-size:1rem;"></i>
                  </button>
                  <button type="button" class="input-group-text" onclick="generateAppPassword()" title="Generate password acak" style="cursor:pointer;background:#f0f5ff;color:#2563eb;font-size:.75rem;font-weight:600;white-space:nowrap;">
                    <i class='bx bx-refresh me-1'></i>Generate
                  </button>
                  @error('portal_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-text">Min. 6 karakter. <span id="generated-pass-info" style="display:none;color:#2563eb;font-weight:600;"></span></div>
              </div>
              <div class="col-12">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $customer->address) }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                <input type="text" class="form-control" value="{{ $customer->pppoe_user }}" disabled>
                <div class="form-text">Tidak dapat diubah setelah dibuat.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Area</label>
                <select name="area_id" class="form-select @error('area_id') is-invalid @enderror">
                  <option value="">Pilih Area</option>
                  @foreach($areas as $area)
                  <option value="{{ $area->id }}" {{ $customer->area_id == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                  @endforeach
                </select>
                @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Paket</label>
                <select name="package_id" class="form-select @error('package_id') is-invalid @enderror">
                  <option value="">Pilih Paket</option>
                  @foreach($packages as $package)
                  <option value="{{ $package->id }}" {{ $customer->package_id == $package->id ? 'selected' : '' }}>
                    {{ $package->name }} ({{ $package->formatted_price }})
                  </option>
                  @endforeach
                </select>
                @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Harga Khusus</label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" id="package-price" name="package_price" class="form-control @error('package_price') is-invalid @enderror" value="{{ old('package_price', $customer->package_price) }}" placeholder="Kosongkan untuk harga default">
                </div>
                @error('package_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Tanggal Mulai Tagihan <span class="text-danger">*</span></label>
                <input type="date" id="billing-start-date" name="billing_start_date" class="form-control @error('billing_start_date') is-invalid @enderror" value="{{ old('billing_start_date', optional($customer->billing_start_date)->toDateString() ?? optional($customer->created_at)->toDateString()) }}" required>
                <div class="form-text">Dipakai untuk hitung prorata invoice bulan pertama.</div>
                @error('billing_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Jatuh Tempo (Tanggal)</label>
                <input type="number" name="billing_due_day" class="form-control @error('billing_due_day') is-invalid @enderror" value="{{ old('billing_due_day', $customer->billing_due_day) }}" min="1" max="28" placeholder="Default: {{ config('billing.invoice_due_day', 20) }}">
                <div class="form-text">Kosongkan untuk pakai default (tgl {{ config('billing.invoice_due_day', 20) }}). Isi 25 kalau mau jatuh tempo tgl 25.</div>
                @error('billing_due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <div id="proration-preview" class="alert alert-info py-2 mb-0" style="font-size:.82rem;display:none;">
                  <strong>Preview Prorata:</strong> <span id="proration-preview-text">-</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">ODP</label>
                <select name="odp_id" class="form-select @error('odp_id') is-invalid @enderror">
                  <option value="">Tanpa ODP</option>
                  @foreach($odps ?? [] as $odp)
                  <option value="{{ $odp->id }}" {{ old('odp_id', $customer->odp_id) == $odp->id ? 'selected' : '' }}>{{ $odp->name }} ({{ $odp->code }}) — {{ $odp->available_slots }} slot</option>
                  @endforeach
                </select>
                @error('odp_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Port ODP</label>
                <input type="number" name="odp_port" class="form-control @error('odp_port') is-invalid @enderror" value="{{ old('odp_port', $customer->odp_port) }}" min="1" max="128" placeholder="Nomor port">
                @error('odp_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.customers.show', $customer) }}" class="ms-btn-ghost">Batal</a>
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
              @if($customer->is_free)
              <span class="badge-status badge-pending">Gratis</span>
              @elseif($customer->status === 'active')
              <span class="badge-status badge-active">Aktif</span>
              @elseif($customer->status === 'pending')
              <span class="badge-status badge-pending">Pending</span>
              @else
              <span class="badge-status badge-inactive">Tidak Aktif</span>
              @endif
            </div>
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="is_free" value="1" id="is-free-check" {{ old('is_free', $customer->is_free) ? 'checked' : '' }}>
              <label class="form-check-label" for="is-free-check" style="font-size:.82rem;">
                <strong>Pelanggan Gratis</strong> — tidak ditagih, tidak kena auto-isolir
              </label>
            </div>
          </div>
          <div class="mb-3 pb-3" style="border-bottom:1px solid #eef2f7;">
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Remote IP</div>
            <div class="mt-1"><code>{{ $customer->remote_ip ?? 'Dinamis' }}</code></div>
          </div>
          <div>
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Dibuat</div>
            <div class="mt-1">{{ $customer->created_at->format('d M Y, H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const DUE_DAY = {{ (int) config('billing.invoice_due_day', 20) }};
const BASE_DAYS = {{ (int) config('billing.proration_base_days', 30) }};

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
@endsection
