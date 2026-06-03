@extends('layouts.app')
@section('title', 'Commissions')

@section('content')
<div class="page-title-box d-flex align-items-center justify-content-between">
  <div>
    <h4>Commissions</h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Commissions</li>
      </ol>
    </nav>
  </div>
</div>

{{-- Stats --}}
<div class="stat-grid" style="margin-bottom:1.5rem;">
  <div class="stat-card">
    <div>
      <div class="stat-label">Pending</div>
      <div class="stat-value">Rp {{ number_format($stats['total_pending'] ?? 0, 0, ',', '.') }}</div>
      <div class="stat-change" style="color:#f59e0b;">Menunggu customer bayar</div>
    </div>
    <div class="stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class='bx bx-time-five'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">Confirmed (Belum Dicairkan)</div>
      <div class="stat-value">Rp {{ number_format($stats['total_unpaid'] ?? 0, 0, ',', '.') }}</div>
      <div class="stat-change" style="color:#2563eb;">Siap dicairkan ke partner</div>
    </div>
    <div class="stat-icon si-blue"><i class='bx bx-wallet'></i></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">Sudah Dicairkan</div>
      <div class="stat-value">Rp {{ number_format($stats['total_paid'] ?? 0, 0, ',', '.') }}</div>
      <div class="stat-change up">Total dicairkan ke semua partner</div>
    </div>
    <div class="stat-icon si-green"><i class='bx bx-check-circle'></i></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="card-title"><i class='bx bx-dollar-circle me-2' style="color:#2563eb;"></i>Commission History</h5>
  </div>
  <div class="table-responsive">
    <table class="table" id="commissions-table">
      <thead>
        <tr>
          <th>Partner</th>
          <th>Customer</th>
          <th>Periode</th>
          <th>Paket</th>
          <th>Komisi (1/3)</th>
          <th>Status</th>
          <th>Keterangan</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($commissions as $commission)
        <tr>
          <td>
            <div style="font-weight:500; color:#1e293b; font-size:0.875rem;">{{ $commission->user->name ?? '-' }}</div>
          </td>
          <td style="font-size:0.875rem; color:#64748b;">{{ $commission->customer->name ?? '-' }}</td>
          <td>
            <div style="font-size:0.8125rem; font-weight:600; color:#1e293b;">
              @php
              $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
              @endphp
              {{ $monthNames[$commission->month] ?? '-' }} {{ $commission->year }}
            </div>
            @if($commission->invoice)
            <div style="font-size:0.6875rem; color:#64748b;">{{ $commission->invoice->invoice_number }}</div>
            @endif
          </td>
          <td style="font-size:0.875rem; color:#64748b;">
            Rp {{ number_format($commission->customer->package_price ?? 0, 0, ',', '.') }}
          </td>
          <td style="font-weight:700; color:#1aae6f;">Rp {{ number_format($commission->amount, 0, ',', '.') }}</td>
          <td>
            @if($commission->status === 'paid')
            <span class="badge-status badge-paid">Dicairkan</span>
            @elseif($commission->status === 'unpaid')
            <span class="badge-status badge-active">Confirmed</span>
            @else
            <span class="badge-status badge-pending">Pending</span>
            @endif
          </td>
          <td style="font-size:0.75rem; color:#64748b;">
            @if($commission->status === 'pending')
            <i class='bx bx-time-five' style="color:#f59e0b;"></i> Customer belum bayar invoice
            @elseif($commission->status === 'unpaid')
            <i class='bx bx-check' style="color:#2563eb;"></i> Customer sudah bayar, komisi menunggu pencairan
            @elseif($commission->status === 'paid')
            <i class='bx bx-check-double' style="color:#1aae6f;"></i> Dicairkan {{ $commission->paid_at?->format('d M Y H:i') }}
            @endif
          </td>
          <td>
            @if($commission->status === 'unpaid')
            <form method="POST" action="{{ route('admin.commissions.pay', $commission) }}" style="display:inline;">
              @csrf
              <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Cairkan komisi Rp {{ number_format($commission->amount, 0, \',\', \'.\') }} ke {{ $commission->user->name ?? \'-\' }}?')">
                <i class='bx bx-money'></i> Cairkan
              </button>
            </form>
            @elseif($commission->status === 'paid')
            <span style="font-size:0.6875rem; color:#1aae6f; font-weight:500;">✓ Done</span>
            @else
            <span style="font-size:0.6875rem; color:#94a3b8;">—</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8">
            <div class="text-center py-5" style="color:#64748b;">
              <i class='bx bx-dollar-circle fs-1 d-block mb-2'></i>
              <div style="font-size:0.9375rem; font-weight:500; color:#1e293b;">Belum ada komisi</div>
              <div style="font-size:0.8125rem;">Komisi otomatis dibuat saat invoice bulanan di-generate</div>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
@section('scripts')
<script>
  $(function() {
    $('#commissions-table').DataTable({
      dom: '<"d-flex justify-content-between align-items-center mb-3"f<"text-muted small"l>><rt<"d-flex justify-content-between align-items-center mt-3"ip>',
      pageLength: 25,
      order: [
        [2, 'desc']
      ],
      language: {
        search: '',
        searchPlaceholder: 'Cari komisi...',
        lengthMenu: 'Show _MENU_',
        info: '_START_-_END_ of _TOTAL_',
        paginate: {
          previous: '&lsaquo;',
          next: '&rsaquo;'
        }
      }
    });
  });
</script>
@endsection