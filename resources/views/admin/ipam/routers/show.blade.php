@extends('layouts.app')
@section('title', 'Router: ' . $router->device_name)

@section('content')
<div class="ms-page nk-list-page ipam-router-detail-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-server'></i> IPAM</div>
      <h1 class="ms-page-title">{{ $router->device_name }}</h1>
      <div class="d-flex align-items-center gap-3 mt-1" style="font-size:.8125rem;color:var(--txt-3);">
        <span><code>{{ $router->wireguard_ip }}</code></span>
        <span>
          @if($router->connection_status === 'connected')
          <span class="badge" style="background:color-mix(in srgb,var(--green) 15%,var(--surface));color:var(--green);font-weight:500;">Connected</span>
          @elseif($router->connection_status === 'error')
          <span class="badge" style="background:color-mix(in srgb,var(--red) 15%,var(--surface));color:var(--red);font-weight:500;">Error</span>
          @else
          <span class="badge" style="background:color-mix(in srgb,var(--txt-3) 15%,var(--surface));color:var(--txt-3);font-weight:500;">Unknown</span>
          @endif
        </span>
        <span>Last scanned: {{ $router->last_scanned_at?->format('d M Y H:i') ?? 'Never' }}</span>
      </div>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.ipam.routers.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
      <form action="{{ route('admin.ipam.routers.scan', $router) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="ms-btn">
          <i class='bx bx-refresh'></i> Scan
        </button>
      </form>
    </div>
  </div>

  {{-- OLT Mapping --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-link me-2'></i>OLT Mapping</h5>
    </div>
    <div class="p-3">
      <form action="{{ route('admin.ipam.routers.mapOlt', $router) }}" method="POST">
        @csrf
        <div class="d-flex align-items-end gap-3">
          <div class="flex-grow-1">
            <label class="form-label" style="font-size:.8rem;">Mapped OLT</label>
            <select name="mapped_olt_id" class="form-select form-select-sm">
              <option value="">— Tidak ada mapping —</option>
              @foreach($olts as $olt)
              <option value="{{ $olt->id }}" {{ $router->mapped_olt_id == $olt->id ? 'selected' : '' }}>
                {{ $olt->name }} ({{ $olt->ip_address }})
              </option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="ms-btn-secondary">
            <i class='bx bx-save'></i> Simpan Mapping
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- 1. IP Pools --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-collection me-2'></i>IP Pools</h5>
      <span class="badge" style="background:var(--surface-2);color:var(--txt-2);font-size:.75rem;">{{ $router->ipPools->count() }}</span>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Pool Name</th>
              <th>Ranges</th>
            </tr>
          </thead>
          <tbody>
            @forelse($router->ipPools as $pool)
            <tr>
              <td style="font-weight:500;">{{ $pool->pool_name }}</td>
              <td><code>{{ $pool->ranges }}</code></td>
            </tr>
            @empty
            <tr><td colspan="2" class="text-center py-3" style="color:var(--txt-3);">Tidak ada IP pool</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 2. Addresses --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-globe me-2'></i>Addresses</h5>
      <span class="badge" style="background:var(--surface-2);color:var(--txt-2);font-size:.75rem;">{{ $router->addresses->count() }}</span>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Address</th>
              <th>Network</th>
              <th>Interface</th>
              <th>Disabled</th>
              <th>Comment</th>
            </tr>
          </thead>
          <tbody>
            @forelse($router->addresses as $address)
            <tr>
              <td><code>{{ $address->address }}</code></td>
              <td><code>{{ $address->network }}</code></td>
              <td>{{ $address->interface }}</td>
              <td>
                @if($address->disabled)
                <span style="color:var(--red);font-size:.75rem;">Yes</span>
                @else
                <span style="color:var(--green);font-size:.75rem;">No</span>
                @endif
              </td>
              <td style="color:var(--txt-3);font-size:.8125rem;">{{ $address->comment ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-3" style="color:var(--txt-3);">Tidak ada address</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 3. Routes --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-transfer me-2'></i>Routes</h5>
      <span class="badge" style="background:var(--surface-2);color:var(--txt-2);font-size:.75rem;">{{ $router->routes->count() }}</span>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Dst Address</th>
              <th>Gateway</th>
              <th>Distance</th>
              <th>Disabled</th>
              <th>Comment</th>
            </tr>
          </thead>
          <tbody>
            @forelse($router->routes as $route)
            <tr>
              <td><code>{{ $route->dst_address }}</code></td>
              <td><code>{{ $route->gateway }}</code></td>
              <td>{{ $route->distance ?? '—' }}</td>
              <td>
                @if($route->disabled)
                <span style="color:var(--red);font-size:.75rem;">Yes</span>
                @else
                <span style="color:var(--green);font-size:.75rem;">No</span>
                @endif
              </td>
              <td style="color:var(--txt-3);font-size:.8125rem;">{{ $route->comment ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-3" style="color:var(--txt-3);">Tidak ada route</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 4. WireGuard Interfaces --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-shield me-2'></i>WireGuard Interfaces</h5>
      <span class="badge" style="background:var(--surface-2);color:var(--txt-2);font-size:.75rem;">{{ $router->wireguardInterfaces->count() }}</span>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Name</th>
              <th>Listen Port</th>
              <th>Public Key</th>
              <th>Disabled</th>
              <th>Comment</th>
            </tr>
          </thead>
          <tbody>
            @forelse($router->wireguardInterfaces as $wgInterface)
            <tr>
              <td style="font-weight:500;">{{ $wgInterface->name }}</td>
              <td>{{ $wgInterface->listen_port ?? '—' }}</td>
              <td><code style="font-size:.75rem;">{{ Str::limit($wgInterface->public_key, 20) ?? '—' }}</code></td>
              <td>
                @if($wgInterface->disabled)
                <span style="color:var(--red);font-size:.75rem;">Yes</span>
                @else
                <span style="color:var(--green);font-size:.75rem;">No</span>
                @endif
              </td>
              <td style="color:var(--txt-3);font-size:.8125rem;">{{ $wgInterface->comment ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-3" style="color:var(--txt-3);">Tidak ada WireGuard interface</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 5. WireGuard Peers --}}
  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-group me-2'></i>WireGuard Peers</h5>
      <span class="badge" style="background:var(--surface-2);color:var(--txt-2);font-size:.75rem;">{{ $router->wireguardPeers->count() }}</span>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table table-flat mb-0">
          <thead>
            <tr>
              <th>Interface</th>
              <th>Public Key</th>
              <th>Allowed Address</th>
              <th>Endpoint</th>
              <th>Disabled</th>
              <th>Comment</th>
            </tr>
          </thead>
          <tbody>
            @forelse($router->wireguardPeers as $peer)
            <tr>
              <td>{{ $peer->interface_name }}</td>
              <td><code style="font-size:.75rem;">{{ Str::limit($peer->public_key, 20) }}</code></td>
              <td><code>{{ $peer->allowed_address }}</code></td>
              <td>
                @if($peer->endpoint_address)
                <code>{{ $peer->endpoint_address }}{{ $peer->endpoint_port ? ':' . $peer->endpoint_port : '' }}</code>
                @else
                <span style="color:var(--txt-3);">—</span>
                @endif
              </td>
              <td>
                @if($peer->disabled)
                <span style="color:var(--red);font-size:.75rem;">Yes</span>
                @else
                <span style="color:var(--green);font-size:.75rem;">No</span>
                @endif
              </td>
              <td style="color:var(--txt-3);font-size:.8125rem;">{{ $peer->comment ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-3" style="color:var(--txt-3);">Tidak ada WireGuard peer</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
