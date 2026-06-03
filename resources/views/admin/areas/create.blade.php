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
              <div class="col-md-6">
                <label class="form-label">IP Address Router <span class="text-danger">*</span></label>
                <input type="text" name="router_ip" class="form-control @error('router_ip') is-invalid @enderror" value="{{ old('router_ip') }}" placeholder="cth. 192.168.88.1" required>
                @error('router_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
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
            <button type="button" id="btn-add-pool" class="ms-btn-secondary">
              <i class='bx bx-plus'></i> Tambah Pool
            </button>
          </div>
          <div class="ms-panel-body pb-1">
            <div class="mb-2" style="font-size:.8rem;color:#64748b;">
              Pool pertama tidak bisa dihapus. Tambah pool jika router area ini memakai lebih dari satu range.
            </div>

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
                    <label class="form-label" style="font-size:.8rem;">IP Pool Awal <span class="text-danger">*</span></label>
                    <input type="text" name="pools[0][ip_pool_start]" class="form-control form-control-sm @error('pools.0.ip_pool_start') is-invalid @enderror" value="{{ old('pools.0.ip_pool_start') }}" placeholder="e.g. 10.10.1.10" required>
                    @error('pools.0.ip_pool_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">IP Pool Akhir <span class="text-danger">*</span></label>
                    <input type="text" name="pools[0][ip_pool_end]" class="form-control form-control-sm @error('pools.0.ip_pool_end') is-invalid @enderror" value="{{ old('pools.0.ip_pool_end') }}" placeholder="e.g. 10.10.1.254" required>
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
