@extends('layouts.app')
@section('title', 'Ubah Area')

@section('content')
@php
  $existingPools = old('pools') ?? $area->ipPools->map(fn($p) => [
      'pool_name'     => $p->pool_name,
      'ip_pool_start' => $p->ip_pool_start,
      'ip_pool_end'   => $p->ip_pool_end,
  ])->toArray();

  if (empty($existingPools)) {
      $existingPools = [[
          'pool_name'     => '',
          'ip_pool_start' => $area->ip_pool_start,
          'ip_pool_end'   => $area->ip_pool_end,
      ]];
  }
@endphp

<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <h1 class="ms-page-title">Ubah Area</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.areas.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-xl-9">
      <form action="{{ route('admin.areas.update', $area) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="ms-panel mb-3">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-map-pin me-2' style="color:#2563eb;"></i>Informasi Area</h5>
          </div>
          <div class="ms-panel-body">
            <div class="mb-0">
              <label class="form-label">Nama Area <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $area->name) }}" required>
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
                <input type="text" name="router_ip" class="form-control @error('router_ip') is-invalid @enderror" value="{{ old('router_ip', $area->router_ip) }}" required>
                @error('router_ip')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-1">
                <label class="form-label">VLAN PPPoE</label>
                <input type="text" name="vlan_pppoe" class="form-control @error('vlan_pppoe') is-invalid @enderror" value="{{ old('vlan_pppoe', $area->vlan_pppoe) }}" placeholder="cth. 100">
                @error('vlan_pppoe')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-1">
                <label class="form-label">VLAN MGMT</label>
                <input type="text" name="vlan_mgmt" class="form-control @error('vlan_mgmt') is-invalid @enderror" value="{{ old('vlan_mgmt', $area->vlan_mgmt) }}" placeholder="cth. 200">
                @error('vlan_mgmt')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-2">
                <label class="form-label">Username Router <span class="text-danger">*</span></label>
                <input type="text" name="router_user" class="form-control @error('router_user') is-invalid @enderror" value="{{ old('router_user', $area->router_user) }}" required>
                @error('router_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label">Password Router</label>
                <input type="password" name="router_pass" class="form-control @error('router_pass') is-invalid @enderror" placeholder="Kosongkan untuk tidak mengubah">
                @error('router_pass')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-12 mt-2">
                <label class="form-label">Lokasi Router (Google Maps Link)</label>
                <input type="text" id="google_maps_link" name="google_maps_link" class="form-control @error('google_maps_link') is-invalid @enderror" value="{{ old('google_maps_link') }}" placeholder="Paste link Google Maps...">
                @error('google_maps_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Paste URL dari Google Maps atau ketik langsung koordinat (lat,long). Koordinat otomatis diambil.</div>
              </div>
              <div class="col-md-6 mt-2">
                <label class="form-label">Latitude</label>
                <input type="text" name="latitude" id="input-latitude" class="form-control" value="{{ old('latitude', $area->latitude) }}">
              </div>
              <div class="col-md-6 mt-2">
                <label class="form-label">Longitude</label>
                <input type="text" name="longitude" id="input-longitude" class="form-control" value="{{ old('longitude', $area->longitude) }}">
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
              Klik "Ambil dari Router" untuk update IP pool dari router. Atau edit manual.
            </div>

            <div id="fetch-status" class="mb-2" style="display:none;"></div>

            @error('pools') <div class="alert alert-danger py-2 mb-3">{{ $message }}</div> @enderror

            <div id="pool-container">
              @foreach($existingPools as $i => $pool)
              <div class="pool-row border rounded p-3 mb-3 {{ $i > 0 ? 'pool-removable' : '' }}" data-index="{{ $i }}" style="background:rgba(37,99,235,.03);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-semibold" style="font-size:.85rem;color:#2563eb;"><i class='bx bx-server me-1'></i>Pool #{{ $i + 1 }}</span>
                  @if($i > 0)
                  <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pool"><i class='bx bx-trash'></i></button>
                  @endif
                </div>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">Nama Pool</label>
                    <input type="text" name="pools[{{ $i }}][pool_name]" class="form-control form-control-sm" value="{{ $pool['pool_name'] ?? '' }}" placeholder="e.g. pool-internet-{{ $i + 1 }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">IP Pool Awal <span class="text-danger">*</span></label>
                    <input type="text" name="pools[{{ $i }}][ip_pool_start]" class="form-control form-control-sm @error('pools.'.$i.'.ip_pool_start') is-invalid @enderror" value="{{ $pool['ip_pool_start'] ?? '' }}" required>
                    @error('pools.'.$i.'.ip_pool_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" style="font-size:.8rem;">IP Pool Akhir <span class="text-danger">*</span></label>
                    <input type="text" name="pools[{{ $i }}][ip_pool_end]" class="form-control form-control-sm @error('pools.'.$i.'.ip_pool_end') is-invalid @enderror" value="{{ $pool['ip_pool_end'] ?? '' }}" required>
                    @error('pools.'.$i.'.ip_pool_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.areas.index') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan Perubahan</button>
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
  let poolCount = {{ count($existingPools) }};

  // Auto-fetch pools from router
  document.getElementById('btn-fetch-pools').addEventListener('click', function() {
    var btn = this;
    var ip = document.querySelector('[name="router_ip"]').value;
    var user = document.querySelector('[name="router_user"]').value;
    var pass = document.querySelector('[name="router_pass"]').value || '{{ $area->router_pass }}';
    var status = document.getElementById('fetch-status');

    if (!ip || !user) {
      status.style.display = 'block';
      status.innerHTML = '<span class="text-danger" style="font-size:.8rem;">Isi IP dan Username router.</span>';
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
              '<div class="col-md-4"><label class="form-label" style="font-size:.8rem;">IP Pool Awal</label><input type="text" name="pools[' + idx + '][ip_pool_start]" class="form-control form-control-sm" value="' + pool.ip_pool_start + '" required></div>' +
              '<div class="col-md-4"><label class="form-label" style="font-size:.8rem;">IP Pool Akhir</label><input type="text" name="pools[' + idx + '][ip_pool_end]" class="form-control form-control-sm" value="' + pool.ip_pool_end + '" required></div>' +
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
            <input type="text" name="pools[${i}][ip_pool_start]" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem;">IP Pool Akhir <span class="text-danger">*</span></label>
            <input type="text" name="pools[${i}][ip_pool_end]" class="form-control form-control-sm" required>
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

  // === Google Maps Link Coordinate Extraction ===
  var gmapsInput = document.getElementById('google_maps_link');
  var latInput = document.getElementById('input-latitude');
  var lngInput = document.getElementById('input-longitude');

  function extractCoords(text) {
    if (!text) return null;
    text = text.trim();

    // Direct format: -7.194529,107.573512
    var directMatch = text.match(/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/);
    if (directMatch) return { lat: directMatch[1], lng: directMatch[2] };

    // Pattern: @-6.9502,107.6614
    var atMatch = text.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (atMatch) return { lat: atMatch[1], lng: atMatch[2] };

    // Pattern: ?q=-6.9502,107.6614 or &q=
    var qMatch = text.match(/[\?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (qMatch) return { lat: qMatch[1], lng: qMatch[2] };

    // Pattern: /place/-6.9502,107.6614
    var placeMatch = text.match(/\/place\/(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (placeMatch) return { lat: placeMatch[1], lng: placeMatch[2] };

    // Pattern: !3d-6.9502!4d107.6614
    var dMatch = text.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
    if (dMatch) return { lat: dMatch[1], lng: dMatch[2] };

    return null;
  }

  if (gmapsInput) {
    gmapsInput.addEventListener('input', function() {
      var coords = extractCoords(this.value);
      if (coords) {
        latInput.value = coords.lat;
        lngInput.value = coords.lng;
      }
    });
    gmapsInput.addEventListener('paste', function() {
      var self = this;
      setTimeout(function() {
        var coords = extractCoords(self.value);
        if (coords) {
          latInput.value = coords.lat;
          lngInput.value = coords.lng;
        }
      }, 50);
    });
  }
})();
</script>
@endsection
