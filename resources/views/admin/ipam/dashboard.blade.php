@extends('layouts.app')
@section('title', 'IPAM Dashboard')

@section('content')
<div class="ms-page nk-list-page ipam-dashboard-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-network-chart'></i> Network Inventory</div>
      <h1 class="ms-page-title">IPAM Dashboard</h1>
    </div>
  </div>

  @if (session('success'))
  <div class="alert alert-success mb-3" style="border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-check-circle me-2'></i>{{ session('success') }}
  </div>
  @endif
  @if (session('error'))
  <div class="alert mb-3" style="background:color-mix(in srgb,var(--red) 10%,var(--surface));border:1px solid color-mix(in srgb,var(--red) 25%,var(--border));color:var(--red);border-radius:.5rem;font-size:.875rem;">
    <i class='bx bx-error-circle me-2'></i>{{ session('error') }}
  </div>
  @endif

  <div class="ms-stat-grid mb-4">
    <div class="ms-stat-card" style="--stat-accent:var(--blue);--stat-bg:color-mix(in srgb,var(--blue) 8%,var(--surface));">
      <div class="ms-stat-icon"><i class='bx bx-server'></i></div>
      <div><div class="ms-stat-label">Total Routers</div><div class="ms-stat-value">{{ $totalRouters }}</div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:var(--green);--stat-bg:color-mix(in srgb,var(--green) 8%,var(--surface));">
      <div class="ms-stat-icon"><i class='bx bx-check-circle'></i></div>
      <div><div class="ms-stat-label">Connected</div><div class="ms-stat-value" style="color:var(--green);">{{ $connectedCount }}</div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:var(--red);--stat-bg:color-mix(in srgb,var(--red) 8%,var(--surface));">
      <div class="ms-stat-icon"><i class='bx bx-error-circle'></i></div>
      <div><div class="ms-stat-label">Errors</div><div class="ms-stat-value" style="color:var(--red);">{{ $errorCount }}</div></div>
    </div>
    <div class="ms-stat-card" style="--stat-accent:#a855f7;--stat-bg:color-mix(in srgb,#a855f7 8%,var(--surface));">
      <div class="ms-stat-icon"><i class='bx bx-sitemap'></i></div>
      <div><div class="ms-stat-label">Total Subnets</div><div class="ms-stat-value">{{ $totalSubnets }}</div></div>
    </div>
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-link-external me-2'></i>Quick Links</h5>
    </div>
    <div class="p-3">
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.ipam.routers.index') }}" class="ms-btn-secondary">
          <i class='bx bx-server'></i> Routers
        </a>
        <a href="{{ route('admin.ipam.subnets.index') }}" class="ms-btn-secondary">
          <i class='bx bx-sitemap'></i> Subnets
        </a>
        <a href="{{ route('admin.ipam.auditLog') }}" class="ms-btn-secondary">
          <i class='bx bx-history'></i> Audit Log
        </a>
        <a href="{{ route('admin.ipam.olts.index') }}" class="ms-btn-secondary">
          <i class='bx bx-broadcast'></i> OLT Management
        </a>
        <a href="{{ route('admin.ipam.settings') }}" class="ms-btn-secondary">
          <i class='bx bx-cog'></i> Settings
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
