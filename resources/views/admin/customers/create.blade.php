@extends('layouts.app')
@section('title', 'Tambah Pelanggan')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-user-plus'></i> Provisioning Pelanggan</div>
      <h1 class="ms-page-title">Tambah Pelanggan</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.customers.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-xl-9">
      <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:var(--blue);"></i>Informasi Pelanggan</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="cth. 081234567890">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">Alamat</label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}" placeholder="Alamat lengkap">
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Latitude</label>
                <input type="number" step="0.00000001" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" placeholder="-6.12345678">
                <div class="form-text">Bisa diisi dari hasil share location bot.</div>
                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Longitude</label>
                <input type="number" step="0.00000001" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" placeholder="107.12345678">
                <div class="form-text">Format angka desimal koordinat rumah pelanggan.</div>
                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">ONT Serial Number</label>
                <input type="text" name="ont_sn" class="form-control @error('ont_sn') is-invalid @enderror" value="{{ old('ont_sn') }}" placeholder="cth. HWTC12345678">
                @error('ont_sn')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Area <span class="text-danger">*</span></label>
                <select name="area_id" id="area-select" class="form-select @error('area_id') is-invalid @enderror" required>
                  <option value="">Pilih Area</option>
                  @foreach($areas as $area)
                  <option value="{{ $area->id }}" {{ old('area_id', auth()->user()->role === 'partner' ? auth()->user()->area_id : '') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                  @endforeach
                </select>
                @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-wifi me-2' style="color:var(--blue);"></i>PPPoE & Tagihan</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">PPPoE Username <span class="text-danger">*</span></label>
                <input type="text" name="pppoe_user" class="form-control @error('pppoe_user') is-invalid @enderror" value="{{ old('pppoe_user') }}" required>
                @error('pppoe_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">PPPoE Password <span class="text-danger">*</span></label>
                <input type="password" name="pppoe_pass" class="form-control @error('pppoe_pass') is-invalid @enderror" autocomplete="new-password" required>
                @error('pppoe_pass')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Paket / Profil <span class="text-danger">*</span></label>
                <select name="package_id" id="package-select" class="form-select @error('package_id') is-invalid @enderror" required>
                  <option value="">Pilih Paket</option>
                  @foreach($packages ?? [] as $pkg)
                  <option value="{{ $pkg->id }}"
                    data-price="{{ $pkg->price }}"
                    data-profile="{{ $pkg->mikrotik_profile }}"
                    data-speed="{{ $pkg->speed_down }}/{{ $pkg->speed_up }}"
                    {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                    {{ $pkg->name }} — {{ $pkg->speed_down }}M/{{ $pkg->speed_up }}M · Rp {{ number_format($pkg->price, 0, ',', '.') }}
                  </option>
                  @endforeach
                </select>
                @error('package_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Local Address</label>
                <select id="local-address-select" class="form-select mb-2" style="display:none;">
                  <option value="">Pilih dari existing</option>
                </select>
                <input type="text" name="local_address" id="local-address" class="form-control @error('local_address') is-invalid @enderror" value="{{ old('local_address') }}" placeholder="Pilih area dulu...">
                @error('local_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Harga Paket Bulanan (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="package_price" id="package-price" class="form-control @error('package_price') is-invalid @enderror" value="{{ old('package_price', 100000) }}" min="0" step="1000" required>
                @error('package_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Tanggal Mulai Tagihan <span class="text-danger">*</span></label>
                <input type="text" name="billing_start_date" id="billing-start-date" class="form-control js-flatpickr @error('billing_start_date') is-invalid @enderror" style="background:var(--surface);cursor:pointer;" value="{{ old('billing_start_date', now()->toDateString()) }}" placeholder="YYYY-MM-DD" required>
                <div class="form-text">Dipakai untuk hitung prorata invoice bulan pertama.</div>
                @error('billing_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <div id="proration-preview" class="alert alert-info py-2 mb-0" style="font-size:.82rem;display:none;">
                  <strong>Preview Prorata:</strong> <span id="proration-preview-text">-</span>
                </div>
              </div>
            </div>

            <div class="alert alert-info py-2 mb-0 mt-3" style="font-size:.85rem;background:var(--blue-lt);border-color:var(--blue-md);color:var(--txt);">
              Pelanggan akan dibuat dengan status <strong>Provisioning</strong>. Secret PPPoE akan dikirim secara otomatis melalui antrian worker.
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.customers.index') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn"><i class='bx bx-user-plus'></i> Buat Pelanggan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(function() {
  var dueDay = {{ (int) config('billing.invoice_due_day', 20) }};
  var baseDays = {{ (int) config('billing.proration_base_days', 30) }};
  var packageUrl = '{{ route("admin.api.packages-by-area") }}';
  var $area = $('#area-select');
  var $pkg  = $('#package-select');
  var $price = $('#package-price');
  var $billingStart = $('#billing-start-date');
  var $preview = $('#proration-preview');
  var $previewText = $('#proration-preview-text');
  var oldPkg = '{{ old("package_id", "") }}';
  var $localAddr = $('#local-address');
  var $localSelect = $('#local-address-select');

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
    const price = Number($price.val() || 0);
    const startDate = parseYmd($billingStart.val());
    if (!price || !startDate) {
      $preview.hide();
      return;
    }

    const now = new Date();
    const periodYear = now.getFullYear();
    const periodMonth = now.getMonth() + 1;
    const dueDate = resolveDueDate(periodYear, periodMonth, dueDay);

    const prevMonth = periodMonth === 1 ? 12 : periodMonth - 1;
    const prevYear = periodMonth === 1 ? periodYear - 1 : periodYear;
    const windowStart = resolveDueDate(prevYear, prevMonth, dueDay);

    if (startDate >= dueDate) {
      $previewText.text('Tanggal mulai tagihan berada pada/di atas tanggal jatuh tempo periode ini. Tagihan akan masuk periode berikutnya.');
      $preview.show();
      return;
    }

    const billableStart = startDate > windowStart ? startDate : windowStart;
    const billedDays = Math.max(0, Math.min(diffDays(billableStart, dueDate), baseDays));
    const dailyRate = price / baseDays;
    const amount = dailyRate * billedDays;

    if (billedDays >= baseDays) {
      $previewText.text('Full cycle ' + baseDays + ' hari (tanpa prorata): ' + formatIdr(amount) + '.');
    } else {
      $previewText.text('Prorata ' + billedDays + ' hari x ' + formatIdr(dailyRate) + '/hari = ' + formatIdr(amount) + ' (jatuh tempo tgl ' + dueDay + ').');
    }
    $preview.show();
  }

  function loadPackages(areaId) {
    if (!areaId) {
      $pkg.html('<option value="">Pilih area dulu</option>').prop('disabled', true);
      $localAddr.attr('placeholder', 'Pilih area dulu...');
      $localSelect.hide().html('<option value="">Pilih dari existing</option>');
      return;
    }
    $pkg.html('<option value="">Menghubungkan ke MikroTik...</option>').prop('disabled', true);
    $localAddr.attr('placeholder', 'Menghubungkan...');
    $localSelect.hide();
    $.getJSON(packageUrl, { area_id: areaId }, function(data) {
      if (data.error) {
        $pkg.html('<option value="">' + data.error + '</option>').prop('disabled', true);
        return;
      }

      var profiles = data.profiles || data;
      var html = '<option value="">Pilih Profil</option>';
      if (profiles.length === 0) html = '<option value="">Tidak ada profile di router ini</option>';
      $.each(profiles, function(i, p) {
        var label = p.name;
        if (p.rate_limit) {
          label += ' [' + p.rate_limit + ']';
        } else if (p.speed_down > 0 || p.speed_up > 0) {
          label += ' [' + p.speed_down + 'M/' + p.speed_up + 'M]';
        }
        if (p.price > 0) {
          label += ' · Rp ' + Number(p.price).toLocaleString('id-ID');
        }
        var sel = (oldPkg == (p.id || p.mikrotik_profile)) ? 'selected' : '';
        html += '<option value="' + (p.id || '') + '" data-price="' + p.price + '" data-profile="' + p.mikrotik_profile + '" ' + sel + '>' + label + '</option>';
      });
      $pkg.html(html).prop('disabled', false);
      if (oldPkg) {
        var opt = $pkg.find(':selected');
        var price = opt.data('price');
        if (price) $price.val(price);
      }
      refreshProrationPreview();

      var localAddresses = data.local_addresses || [];
      if (localAddresses.length > 0) {
        var laHtml = '<option value="">Pilih dari existing</option>';
        laHtml += '<option value="__manual__">Ketik manual...</option>';
        $.each(localAddresses, function(i, addr) {
          laHtml += '<option value="' + addr + '">' + addr + '</option>';
        });
        $localSelect.html(laHtml).show();
        $localAddr.attr('placeholder', 'Atau ketik manual di sini...');
      } else {
        $localSelect.hide();
        $localAddr.attr('placeholder', 'cth. 10.10.10.2');
      }
    }).fail(function() {
      $pkg.html('<option value="">Gagal memuat profil</option>').prop('disabled', true);
    });
  }

  $localSelect.on('change', function() {
    var val = $(this).val();
    if (val && val !== '__manual__') {
      $localAddr.val(val);
    } else {
      $localAddr.val('').focus();
    }
  });

  $area.on('change', function() {
    oldPkg = '';
    $localAddr.val('');
    $localSelect.val('');
    var areaId = $(this).val();
    loadPackages(areaId);
  });

  $pkg.on('change', function() {
    var price = $(this).find(':selected').data('price');
    if (price !== undefined && price !== '') $price.val(price);
    refreshProrationPreview();
  });

  $price.on('input change', refreshProrationPreview);
  $billingStart.on('change input', refreshProrationPreview);

  if (typeof flatpickr !== 'undefined' && $billingStart.length > 0) {
    flatpickr($billingStart[0], {
      dateFormat: 'Y-m-d',
      onChange: refreshProrationPreview
    });
  }

  loadPackages($area.val());
  refreshProrationPreview();


});
</script>
@endsection
