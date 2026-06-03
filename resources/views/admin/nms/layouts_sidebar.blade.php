{{-- sidebar.blade.php — nav links only, no outer wrapper (layout provides it) --}}
@php $isAdmin = auth()->user()->role === 'admin'; @endphp

{{-- MAIN --}}
<div class="sb-section">
  <div class="sb-label">Main</div>
  <a href="{{ route('admin.dashboard') }}" class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class='bx bx-tachometer sb-icon'></i><span>Dashboard</span>
  </a>
</div>

{{-- NETWORK --}}
<div class="sb-section">
  <div class="sb-label">Network</div>
  @if($isAdmin)
  <a href="{{ route('admin.areas.index') }}" class="sb-link {{ request()->routeIs('admin.areas*') ? 'active' : '' }}">
    <i class='bx bx-map-pin sb-icon'></i><span>Areas</span>
  </a>
  <a href="{{ route('admin.packages.index') }}" class="sb-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
    <i class='bx bx-package sb-icon'></i><span>Packages</span>
  </a>
  @endif
  <a href="{{ route('admin.pppoe.index') }}" class="sb-link {{ request()->routeIs('admin.pppoe*') ? 'active' : '' }}">
    <i class='bx bx-wifi sb-icon'></i><span>PPPoE</span>
  </a>
  @if($isAdmin)
  <a href="{{ route('admin.odps.index') }}" class="sb-link {{ request()->routeIs('admin.odps*') ? 'active' : '' }}">
    <i class='bx bx-map sb-icon'></i><span>ODP Mapping</span>
  </a>
  <a href="{{ route('admin.coverage-map') }}" class="sb-link {{ request()->routeIs('admin.coverage-map') ? 'active' : '' }}">
    <i class='bx bx-globe sb-icon'></i><span>Coverage Map</span>
  </a>
  <a href="{{ route('admin.acs.index') }}" class="sb-link {{ request()->routeIs('admin.acs*') ? 'active' : '' }}">
    <i class='bx bx-router sb-icon'></i><span>ACS Devices</span>
  </a>
  <a href="{{ route('admin.olts.index') }}" class="sb-link {{ request()->routeIs('admin.olts*') ? 'active' : '' }}">
    <i class='bx bx-server sb-icon'></i><span>OLT / ONT</span>
  </a>
  @endif
</div>

{{-- CUSTOMERS --}}
<div class="sb-section">
  <div class="sb-label">Customers</div>
  @if($isAdmin)
  <a href="{{ route('admin.partners.index') }}" class="sb-link {{ request()->routeIs('admin.partners*') ? 'active' : '' }}">
    <i class='bx bx-buildings sb-icon'></i><span>Partners</span>
  </a>
  @endif
  <a href="{{ route('admin.customers.index') }}" class="sb-link {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
    <i class='bx bx-user sb-icon'></i><span>Customers</span>
  </a>
</div>

@if($isAdmin)
{{-- BILLING --}}
<div class="sb-section">
  <div class="sb-label">Billing</div>
  <a href="{{ route('admin.invoices.index') }}" class="sb-link {{ request()->routeIs('admin.invoices*') ? 'active' : '' }}">
    <i class='bx bx-receipt sb-icon'></i><span>Invoices</span>
  </a>
  <a href="{{ route('admin.commissions.index') }}" class="sb-link {{ request()->routeIs('admin.commissions*') ? 'active' : '' }}">
    <i class='bx bx-dollar-circle sb-icon'></i><span>Commissions</span>
  </a>
  <a href="{{ route('admin.vouchers.index') }}" class="sb-link {{ request()->routeIs('admin.vouchers*') ? 'active' : '' }}">
    <i class='bx bx-purchase-tag sb-icon'></i><span>Vouchers</span>
  </a>
</div>
@endif

{{-- SUPPORT --}}
<div class="sb-section">
  <div class="sb-label">Support</div>
  <a href="{{ route('admin.tickets.index') }}" class="sb-link {{ request()->routeIs('admin.tickets*') ? 'active' : '' }}">
    <i class='bx bx-support sb-icon'></i><span>Tickets</span>
  </a>
  @if($isAdmin)
  <a href="{{ route('admin.whatsapp.index') }}" class="sb-link {{ request()->routeIs('admin.whatsapp*') ? 'active' : '' }}">
    <i class='bx bxl-whatsapp sb-icon'></i><span>WhatsApp</span>
  </a>
  @endif
</div>

{{-- ACCOUNT --}}
<div class="sb-section">
  <div class="sb-label">Account</div>
  @if($isAdmin)
  <a href="{{ route('admin.users.index') }}" class="sb-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
    <i class='bx bx-group sb-icon'></i><span>Users</span>
  </a>
  @endif
  <a href="{{ route('admin.profile') }}" class="sb-link {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
    <i class='bx bx-user-circle sb-icon'></i><span>Profile</span>
  </a>
  @if($isAdmin)
  <a href="{{ route('admin.settings') }}" class="sb-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
    <i class='bx bx-cog sb-icon'></i><span>Settings</span>
  </a>
  @endif
</div>