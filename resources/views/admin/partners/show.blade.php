@extends('layouts.app')
@section('title', 'Mitra: ' . $partner->name)

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-buildings'></i> Detail Mitra</div>
      <h1 class="ms-page-title">{{ $partner->name }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.partners.edit', $partner) }}" class="ms-btn"><i class='bx bx-edit'></i> Ubah</a>
      <a href="{{ route('admin.partners.index') }}" class="ms-btn-secondary"><i class='bx bx-arrow-back'></i> Kembali</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-xl-4">
      <div class="ms-panel h-100">
        <div class="ms-panel-body text-center">
          <span class="avatar avatar-xl mb-3" style="background-image:url(https://ui-avatars.com/api/?name={{ urlencode($partner->name) }}&background=206bc4&color=fff&size=128)"></span>
          <h3 class="mb-1">{{ $partner->name }}</h3>
          <div class="text-muted mb-3">{{ $partner->email }}</div>
          <span class="badge-status badge-active">Partner</span>
        </div>
        <div class="ms-table-shell">
          <table class="table table-sm mb-0">
            <tbody>
              <tr><td class="text-muted">Telepon</td><td class="text-end">{{ $partner->phone ?? '-' }}</td></tr>
              <tr><td class="text-muted">Area</td><td class="text-end">{{ $partner->area->name ?? '-' }}</td></tr>
              <tr><td class="text-muted">Saldo Dompet</td><td class="text-end" style="font-weight:700;color:#16a34a;">Rp {{ number_format($partner->wallet_balance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td class="text-muted">Bergabung</td><td class="text-end">{{ $partner->created_at->format('d M Y') }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-xl-8">
      <div class="stat-grid">
        <div class="stat-card">
          <div>
            <div class="stat-label">Pelanggan</div>
            <div class="stat-value">{{ $partner->customers_count ?? 0 }}</div>
          </div>
          <div class="stat-icon si-blue"><i class='bx bx-user'></i></div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Komisi</div>
            <div class="stat-value">{{ $partner->commissions_count ?? 0 }}</div>
          </div>
          <div class="stat-icon si-green"><i class='bx bx-money'></i></div>
        </div>
        <div class="stat-card">
          <div>
            <div class="stat-label">Pelanggan Aktif</div>
            <div class="stat-value">{{ $partner->customers->count() > 0 ? $partner->customers->where('status', 'active')->count() : 0 }}</div>
          </div>
          <div class="stat-icon si-orange"><i class='bx bx-check-circle'></i></div>
        </div>
      </div>

      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-user me-2' style="color:#2563eb;"></i>Pelanggan</h5>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table ms-table-wide mb-0" style="min-width:760px;">
              <thead>
                <tr>
                  <th>Pelanggan</th>
                  <th>Paket</th>
                  <th>Status</th>
                  <th>Dibuat</th>
                </tr>
              </thead>
              <tbody>
                @forelse($partner->customers as $customer)
                <tr>
                  <td>
                    <a href="{{ route('admin.customers.show', $customer) }}" style="font-weight:600;color:inherit;text-decoration:none;">{{ $customer->name }}</a>
                    <div style="font-size:.74rem;color:#64748b;">{{ $customer->pppoe_user }}</div>
                  </td>
                  <td>{{ $customer->package->name ?? '-' }}</td>
                  <td>
                    @if($customer->status === 'active')
                    <span class="badge-status badge-active">Aktif</span>
                    @elseif($customer->status === 'suspended')
                    <span class="badge-status badge-danger">Ditangguhkan</span>
                    @else
                    <span class="badge-status badge-inactive">{{ ucfirst($customer->status) }}</span>
                    @endif
                  </td>
                  <td>{{ $customer->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pelanggan untuk mitra ini</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
