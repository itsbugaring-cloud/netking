@extends('layouts.app')
@section('title', 'Tambah Area')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <h1 class="ms-page-title">Tambah Area</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.areas.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-xl-9">
      <form action="{{ route('admin.areas.store') }}" method="POST">
        @csrf

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-map-pin me-2' style="color:#2563eb;"></i>Informasi Area</h5>
          </div>
          <div class="ms-panel-body">
            <div class="mb-0">
              <label class="form-label">Nama Area <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="cth. Cianjur Kota" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-chip me-2' style="color:#2563eb;"></i>Konfigurasi Router MikroTik</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label">IP Address Router <span class="text-danger">*</span></label>
                <input type="text" name="router_ip" class="form-control @error('router_ip') is-invalid @enderror" value="{{ old('router_ip') }}" placeholder="cth. 192.168.88.1" required>
                @error('router_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-1">
                <label class="form-label">VLAN PPPoE</label>
                <input type="text" name="vlan_pppoe" class="form-control @error('vlan_pppoe') is-invalid @enderror" value="{{ old('vlan_pppoe') }}" placeholder="cth. 100">
                @error('vlan_pppoe')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-1">
                <label class="form-label">VLAN MGMT</label>
                <input type="text" name="vlan_mgmt" class="form-control @error('vlan_mgmt') is-invalid @enderror" value="{{ old('vlan_mgmt') }}" placeholder="cth. 200">
                @error('vlan_mgmt')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-2">
                <label class="form-label">Username Router <span class="text-danger">*</span></label>
                <input type="text" name="router_user" class="form-control @error('router_user') is-invalid @enderror" value="{{ old('router_user') }}" placeholder="admin" required>
                @error('router_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Password Router <span class="text-danger">*</span></label>
                <input type="password" name="router_pass" class="form-control @error('router_pass') is-invalid @enderror" value="{{ old('router_pass') }}" required>
                @error('router_pass')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
        </div>

        <div class="ms-panel mb-3">
          <div class="ms-panel-head d-flex justify-content-between align-items-center">
            <h5 class="ms-panel-title"><i class='bx bx-network-chart me-2' style="color:#2563eb;"></i>IP Pool MikroTik</h5>
            <div>
              <button type="button" id="btn-fetch-pools" class="ms-btn-secondary me-1">
                <i class='bx bx-download'></i> Ambil dari Router
              </button>
              <button type="button" id="btn-add-pool" class="ms-btn-secondary">
                <i class='bx bx-plus'></i> Tambah Pool
              </button>
            </div>
          </div>
          <div class="ms-panel-body pb-1">
            <div class="mb-2" style="font-size:.8rem;color:#64748b;">
              Kosongkan jika ingin otomatis diambil dari router saat simpan. Atau klik "Ambil dari Router" untuk preview.
            </div>

            <div id="fetch-status" class="mb-2" style="display:none;"></div>

            @error('pools') <div class="alert alert-danger py-2 mb-3">{{ $message }}</div> @enderror

            <div id="pool-container">
              <div class="pool-row border rounded p-3 mb-3" data-index="0" style="background:rgba(37,99,235,.03);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-semibold" style="font-size:.85rem;color:#2563eb;"><i class='bx bx-server me-1'></i>Pool #1</span>
                </div>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">Nama Pool</label>
                    <input type="text" name="pools[0][pool_name]" class="form-control form-control-sm" value="{{ old('pools.0.pool_name') }}" placeholder="cth. pool-internet-1">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">IP Pool Awal</label>
                    <input type="text" name="pools[0][ip_pool_start]" class="form-control form-control-sm @error('pools.0.ip_pool_start') is-invalid @enderror" value="{{ old('pools.0.ip_pool_start') }}" placeholder="otomatis dari router">
                    @error('pools.0.ip_pool_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">IP Pool Akhir</label>
                    <input type="text" name="pools[0][ip_pool_end]" class="form-control form-control-sm @error('pools.0.ip_pool_end') is-invalid @enderror" value="{{ old('pools.0.ip_pool_end') }}" placeholder="otomatis dari router">
                    @error('pools.0.ip_pool_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>

              @if(old('pools'))
                @foreach(old('pools') as $i => $pool)
                  @if($i === 0) @continue @endif
                  <div class="pool-row border rounded p-3 mb-3 pool-removable" data-index="{{ $i }}" style="background:rgba(37,99,235,.03);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-semibold" style="font-size:.85rem;color:#2563eb;"><i class='bx bx-server me-1'></i>Pool #{{ $i + 1 }}</span>
                      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pool"><i class='bx bx-trash'></i></button>
                    </div>
                    <div class="row g-2">
                      <div class="col-md-4">
                        <label class="form-label" style="font-size:.8rem;">Nama Pool</label>
                        <input type="text" name="pools[{{ $i }}][pool_name]" class="form-control form-control-sm" value="{{ $pool['pool_name'] ?? '' }}" placeholder="cth. pool-internet-2">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label" style="font-size:.8rem;">IP Pool Awal <span class="text-danger">*</span></label>
                        <input type="text" name="pools[{{ $i }}][ip_pool_start]" class="form-control form-control-sm" value="{{ $pool['ip_pool_start'] ?? '' }}" placeholder="e.g. 10.10.2.10" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label" style="font-size:.8rem;">IP Pool Akhir <span class="text-danger">*</span></label>
                        <input type="text" name="pools[{{ $i }}][ip_pool_end]" class="form-control form-control-sm" value="{{ $pool['ip_pool_end'] ?? '' }}" placeholder="e.g. 10.10.2.254" required>
                      </div>
                    </div>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.areas.index') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Area</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
  let poolCount = {{ old('pools') ? count(old('pools')) : 1 }};

  // Auto-fetch pools from router
  document.getElementById('btn-fetch-pools').addEventListener('click', function() {
    var btn = this;
    var ip = document.querySelector('[name="router_ip"]').value;
    var user = document.querySelector('[name="router_user"]').value;
    var pass = document.querySelector('[name="router_pass"]').value;
    var status = document.getElementById('fetch-status');

    if (!ip || !user || !pass) {
      status.style.display = 'block';
      status.innerHTML = '<span class="text-danger" style="font-size:.8rem;">Isi IP, Username, dan Password router dulu.</span>';
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Mengambil...';
    status.style.display = 'block';
    status.innerHTML = '<span style="font-size:.8rem;color:#64748b;">Menghubungi router...</span>';

    fetch('{{ route("admin.areas.test-router") }}', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({router_ip: ip, router_user: user, router_pass: pass})
    })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bx bx-download"></i> Ambil dari Router';

      if (!data.success) {
        status.innerHTML = '<span class="text-danger" style="font-size:.8rem;">Gagal: ' + (data.error || 'Unknown') + '</span>';
        return;
      }

      status.innerHTML = '<span class="text-success" style="font-size:.8rem;">✓ Terhubung! Identity: <strong>' + data.identity + '</strong></span>';

      if (data.pools && data.pools.length > 0) {
        // Clear existing pools and fill with fetched data
        document.getElementById('pool-container').innerHTML = '';
        poolCount = 0;

        data.pools.forEach(function(pool, idx) {
          var html = '<div class="pool-row border rounded p-3 mb-3' + (idx > 0 ? ' pool-removable' : '') + '" data-index="' + idx + '" style="background:rgba(37,99,235,.03);">' +
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
              '<span class="fw-semibold" style="font-size:.85rem;color:#2563eb;"><i class="bx bx-server me-1"></i>Pool #' + (idx+1) + '</span>' +
              (idx > 0 ? '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-pool"><i class="bx bx-trash"></i></button>' : '') +
            '</div>' +
            '<div class="row g-2">' +
              '<div class="col-md-4"><label class="form-label" style="font-size:.8rem;">Nama Pool</label><input type="text" name="pools[' + idx + '][pool_name]" class="form-control form-control-sm" value="' + (pool.pool_name||'') + '"></div>' +
              '<div class="col-md-4"><label class="form-label" style="font-size:.8rem;">IP Pool Awal</label><input type="text" name="pools[' + idx + '][ip_pool_start]" class="form-control form-control-sm" value="' + pool.ip_pool_start + '"></div>' +
              '<div class="col-md-4"><label class="form-label" style="font-size:.8rem;">IP Pool Akhir</label><input type="text" name="pools[' + idx + '][ip_pool_end]" class="form-control form-control-sm" value="' + pool.ip_pool_end + '"></div>' +
            '</div></div>';
          document.getElementById('pool-container').insertAdjacentHTML('beforeend', html);
          poolCount++;
        });

        bindRemoveButtons();
        status.innerHTML += ' — <strong>' + data.pools.length + ' pool</strong> ditemukan.';
      } else {
        status.innerHTML += ' — Tidak ada IP pool di router.';
      }
    })
    .catch(function(err) {
      btn.disabled = false;
      btn.innerHTML = '<i class="bx bx-download"></i> Ambil dari Router';
      status.innerHTML = '<span class="text-danger" style="font-size:.8rem;">Error: ' + err.message + '</span>';
    });
  });

  document.getElementById('btn-add-pool').addEventListener('click', function() {
    const i = poolCount;
    const html = `
      <div class="pool-row border rounded p-3 mb-3 pool-removable" data-index="${i}" style="background:rgba(37,99,235,.03);">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-semibold" style="font-size:.85rem;color:#2563eb;"><i class='bx bx-server me-1'></i>Pool #${i + 1}</span>
          <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pool"><i class='bx bx-trash'></i></button>
        </div>
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">Nama Pool</label>
            <input type="text" name="pools[${i}][pool_name]" class="form-control form-control-sm" placeholder="cth. pool-internet-${i + 1}">
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">IP Pool Awal <span class="text-danger">*</span></label>
            <input type="text" name="pools[${i}][ip_pool_start]" class="form-control form-control-sm" placeholder="cth. 10.10.${i}.10" required>
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">IP Pool Akhir <span class="text-danger">*</span></label>
            <input type="text" name="pools[${i}][ip_pool_end]" class="form-control form-control-sm" placeholder="cth. 10.10.${i}.254" required>
          </div>
        </div>
      </div>`;
    document.getElementById('pool-container').insertAdjacentHTML('beforeend', html);
    poolCount++;
    bindRemoveButtons();
    renumberPools();
  });

  function bindRemoveButtons() {
    document.querySelectorAll('.btn-remove-pool').forEach(btn => {
      btn.onclick = function() {
        this.closest('.pool-row').remove();
        renumberPools();
      };
    });
  }

  function renumberPools() {
    document.querySelectorAll('.pool-row').forEach((row, idx) => {
      row.querySelector('span.fw-semibold').innerHTML = `<i class='bx bx-server me-1'></i>Pool #${idx + 1}`;
    });
  }

  bindRemoveButtons();
})();
</script>
@endsection
