@extends('layouts.app')
@section('title', 'IPAM Routers')

@section('content')
<div class="ms-page nk-list-page ipam-routers-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-server'></i> IPAM</div>
      <h1 class="ms-page-title">IPAM Routers</h1>
    </div>
    <div class="ms-page-actions">
      <form action="{{ route('admin.ipam.routers.scanAll') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="ms-btn">
          <i class='bx bx-refresh'></i> Scan All
        </button>
      </form>
      <a href="{{ route('admin.ipam.routers.export') }}" class="ms-btn-secondary">
        <i class='bx bx-download'></i> Export CSV
      </a>
      <form action="{{ route('admin.ipam.routers.autoMap') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="ms-btn-secondary">
          <i class='bx bx-link'></i> Auto-Map
        </button>
      </form>
    </div>
  </div>

  {{-- Add Router Form --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-plus-circle me-2'></i>Tambah Router</h5>
    </div>
    <div class="p-3">
      <form action="{{ route('admin.ipam.routers.store') }}" method="POST">
        @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem;">Nama Device</label>
            <input type="text" name="device_name" class="form-control form-control-sm" placeholder="MK-Cangkring-01" required value="{{ old('device_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem;">WireGuard IP</label>
            <input type="text" name="wireguard_ip" class="form-control form-control-sm" placeholder="10.10.50.1" required value="{{ old('wireguard_ip') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label" style="font-size:.8rem;">Username</label>
            <input type="text" name="auth_username" class="form-control form-control-sm" placeholder="admin" value="{{ old('auth_username') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label" style="font-size:.8rem;">Password</label>
            <input type="password" name="auth_password" class="form-control form-control-sm" placeholder="password">
          </div>
          <div class="col-md-2">
            <button type="submit" class="ms-btn w-100">
              <i class='bx bx-plus'></i> Tambah
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Search --}}
  <div class="ms-panel mb-3">
    <div class="p-3">
      <form action="{{ route('admin.ipam.routers.index') }}" method="GET">
        <div class="d-flex gap-2">
          <div class="flex-grow-1">
            <div class="nk-search-wrap">
              <i class='bx bx-search'></i>
              <input type="text" name="search" class="nk-search-input" placeholder="Cari router (nama, IP)..." value="{{ request('search') }}">
            </div>
          </div>
          <button type="submit" class="ms-btn-secondary">
            <i class='bx bx-search'></i> Cari
          </button>
          @if(request('search'))
          <a href="{{ route('admin.ipam.routers.index') }}" class="ms-btn-secondary">
            <i class='bx bx-x'></i> Reset
          </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- Router Table --}}
  <div class="ms-panel">
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Device Name</th>
              <th>WireGuard IP</th>
              <th>Status</th>
              <th>Mapped OLT</th>
              <th>Last Scanned</th>
              <th style="width:100px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($routers as $router)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  @if($router->is_online)
                  <span class="d-inline-block rounded-circle" style="width:8px;height:8px;background:var(--green);flex-shrink:0;" title="Online"></span>
                  @else
                  <span class="d-inline-block rounded-circle" style="width:8px;height:8px;background:var(--txt-3);opacity:.4;flex-shrink:0;" title="Offline"></span>
                  @endif
                  <span style="font-weight:500;">{{ $router->device_name }}</span>
                </div>
              </td>
              <td><code>{{ $router->wireguard_ip }}</code></td>
              <td>
                @if($router->connection_status === 'connected')
                <span class="badge" style="background:color-mix(in srgb,var(--green) 15%,var(--surface));color:var(--green);font-weight:500;">Connected</span>
                @elseif($router->connection_status === 'error')
                <span class="badge" style="background:color-mix(in srgb,var(--red) 15%,var(--surface));color:var(--red);font-weight:500;">Error</span>
                @else
                <span class="badge" style="background:color-mix(in srgb,var(--txt-3) 15%,var(--surface));color:var(--txt-3);font-weight:500;">Unknown</span>
                @endif
              </td>
              <td>{{ $router->mappedOlt?->name ?? '—' }}</td>
              <td style="color:var(--txt-3);font-size:.8125rem;">
                {{ $router->last_scanned_at?->diffForHumans() ?? 'Never' }}
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="{{ route('admin.ipam.routers.show', $router) }}" class="nk-action-btn" title="Detail">
                    <i class='bx bx-show'></i>
                  </a>
                  <form action="{{ route('admin.ipam.routers.scan', $router) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="nk-action-btn" title="Scan">
                      <i class='bx bx-refresh'></i>
                    </button>
                  </form>
                  <form action="{{ route('admin.ipam.routers.destroy', $router) }}" method="POST" class="m-0" data-confirm="Hapus router {{ $router->device_name }}?">
                    @csrf @method('DELETE')
                    <button type="submit" class="nk-action-btn delete" title="Hapus">
                      <i class='bx bx-trash'></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6">
                <div class="text-center py-5" style="color:var(--txt-3);">
                  <i class='bx bx-server fs-1 d-block mb-2'></i>
                  <div style="font-size:.9375rem;font-weight:500;">Belum ada router</div>
                  <div style="font-size:.8rem;">Router akan muncul setelah data di-import atau di-scan.</div>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Pagination --}}
  @if($routers->hasPages())
  <div class="mt-3">
    {{ $routers->links() }}
  </div>
  @endif
</div>
@endsection
