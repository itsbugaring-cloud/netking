{{-- sidebar.blade.php — nav links only, no outer wrapper (layout provides it) --}}
@php $isAdmin = auth()->user()->role === 'admin'; @endphp

{{-- MAIN --}}
<div class="sb-section">
  <div class="sb-label">Main</div>
  <a href="{{ route('admin.dashboard') }}" class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-title="Dashboard">
    <i class='bx bx-tachometer sb-icon'></i><span>Dashboard</span>
  </a>
</div>

{{-- NETWORK --}}
<div class="sb-section">
  <div class="sb-label">Network</div>
  @if($isAdmin)
  <a href="{{ route('admin.areas.index') }}" class="sb-link {{ request()->routeIs('admin.areas*') ? 'active' : '' }}" data-title="Areas">
    <i class='bx bx-map-pin sb-icon'></i><span>Areas</span>
  </a>
  <a href="{{ route('admin.packages.index') }}" class="sb-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}" data-title="Packages">
    <i class='bx bx-package sb-icon'></i><span>Packages</span>
  </a>
  @endif
  <a href="{{ route('admin.pppoe.index') }}" class="sb-link {{ request()->routeIs('admin.pppoe*') ? 'active' : '' }}" data-title="PPPoE">
    <i class='bx bx-wifi sb-icon'></i><span>PPPoE</span>
  </a>
  @if($isAdmin)
  <a href="{{ route('admin.odps.index') }}" class="sb-link {{ request()->routeIs('admin.odps*') ? 'active' : '' }}" data-title="ODP Mapping">
    <i class='bx bx-map sb-icon'></i><span>ODP Mapping</span>
  </a>
  <a href="{{ route('admin.coverage-map') }}" class="sb-link {{ request()->routeIs('admin.coverage-map') ? 'active' : '' }}" data-title="Coverage Map">
    <i class='bx bx-map-alt sb-icon'></i><span>Coverage Map</span>
  </a>

  <a href="{{ route('admin.olts.index') }}" class="sb-link {{ request()->routeIs('admin.olts*') ? 'active' : '' }}" data-title="OLT / ONT">
    <i class='bx bx-server sb-icon'></i><span>OLT / ONT</span>
  </a>
  @endif
</div>

{{-- CUSTOMERS --}}
<div class="sb-section">
  <div class="sb-label">Customers</div>
  <a href="{{ route('admin.customers.index') }}" class="sb-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}" data-title="Customers">
    <i class='bx bx-user sb-icon'></i><span>Customers</span>
  </a>
</div>

@if($isAdmin)
{{-- BILLING --}}
<div class="sb-section">
  <div class="sb-label">Billing</div>
  <a href="{{ route('admin.invoices.index') }}" class="sb-link {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}" data-title="Invoices">
    <i class='bx bx-receipt sb-icon'></i><span>Invoices</span>
  </a>
  <a href="{{ route('admin.vouchers.index') }}" class="sb-link {{ request()->routeIs('admin.vouchers*') ? 'active' : '' }}" data-title="Vouchers">
    <i class='bx bx-purchase-tag sb-icon'></i><span>Vouchers</span>
  </a>
  <a href="{{ route('admin.reports.revenue') }}" class="sb-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" data-title="Reports">
    <i class='bx bx-bar-chart-alt-2 sb-icon'></i><span>Reports</span>
  </a>
</div>

{{-- MONITORING (NMS) --}}
<div class="sb-section">
  <div class="sb-label">Monitoring</div>
  <a href="{{ route('admin.nms.dashboard') }}" class="sb-link {{ request()->routeIs('admin.nms.dashboard') ? 'active' : '' }}" data-title="NMS Dashboard">
    <i class='bx bx-pulse sb-icon'></i><span>NMS Dashboard</span>
  </a>
  <a href="{{ route('admin.nms.devices') }}" class="sb-link {{ request()->routeIs('admin.nms.devices') ? 'active' : '' }}" data-title="NMS Devices">
    <i class='bx bx-chip sb-icon'></i><span>NMS Devices</span>
  </a>
  <a href="{{ route('admin.nms.ports') }}" class="sb-link {{ request()->routeIs('admin.nms.ports') ? 'active' : '' }}" data-title="Port Traffic">
    <i class='bx bx-transfer sb-icon'></i><span>Port Traffic</span>
  </a>
  <a href="{{ route('admin.nms.alerts') }}" class="sb-link {{ request()->routeIs('admin.nms.alerts') ? 'active' : '' }}" data-title="Alert Rules">
    <i class='bx bx-bell sb-icon'></i><span>Alert Rules</span>
  </a>
  <a href="{{ route('admin.nms.syslog') }}" class="sb-link {{ request()->routeIs('admin.nms.syslog') ? 'active' : '' }}" data-title="Syslog">
    <i class='bx bx-file sb-icon'></i><span>Syslog</span>
  </a>
  <a href="{{ route('admin.nms.topology') }}" class="sb-link {{ request()->routeIs('admin.nms.topology') ? 'active' : '' }}" data-title="Topology">
    <i class='bx bx-git-repo-forked sb-icon'></i><span>Topology</span>
  </a>
</div>
@endif

{{-- SUPPORT --}}
<div class="sb-section">
  <div class="sb-label">Support</div>
  <a href="{{ route('admin.tickets.index') }}" class="sb-link {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}" data-title="Tickets">
    <i class='bx bx-support sb-icon'></i><span>Tickets</span>
  </a>
</div>

{{-- ACCOUNT --}}
<div class="sb-section">
  <div class="sb-label">Account</div>
  @if($isAdmin)
  <a href="{{ route('admin.users.index') }}" class="sb-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" data-title="Users">
    <i class='bx bx-group sb-icon'></i><span>Users</span>
  </a>
  @endif
  <a href="{{ route('admin.profile') }}" class="sb-link {{ request()->routeIs('admin.profile*') ? 'active' : '' }}" data-title="Profile">
    <i class='bx bx-user-circle sb-icon'></i><span>Profile</span>
  </a>
  @if($isAdmin)
  <a href="{{ route('admin.settings') }}" class="sb-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" data-title="Settings">
    <i class='bx bx-cog sb-icon'></i><span>Settings</span>
  </a>
  @endif
</div>