@extends('layouts.app')
@section('title', 'Laporan Pendapatan')

@section('content')
<div class="ms-page nk-list-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-bar-chart-alt-2'></i> Analitik Pendapatan</div>
      <h1 class="ms-page-title">Laporan Pendapatan</h1>
    </div>
    <div class="ms-page-actions">
      <select id="year-select" class="form-select" style="width:110px;" onchange="window.location='?year='+this.value">
        @foreach($years as $y)
        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
      </select>
      <div class="dropdown">
        <button class="ms-btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
          <i class='bx bx-download'></i> Ekspor
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="{{ route('admin.reports.export-payments', ['year' => $year]) }}">CSV Pembayaran ({{ $year }})</a></li>
          <li><a class="dropdown-item" href="{{ route('admin.reports.export-payments', ['year' => $year, 'status' => 'approved']) }}">CSV Disetujui Saja</a></li>
          <li><a class="dropdown-item" href="{{ route('admin.reports.export-payments', ['year' => $year, 'status' => 'pending']) }}">CSV Pending Saja</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="{{ route('admin.reports.export-revenue', ['year' => $year]) }}">CSV Ringkasan Pendapatan</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <div>
        <div class="stat-label">Total Pendapatan {{ $year }}</div>
        <div class="stat-value">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
      </div>
      <div class="stat-icon si-blue"><i class='bx bx-wallet'></i></div>
    </div>
    <div class="stat-card">
      <div>
        <div class="stat-label">Belum Lunas</div>
        <div class="stat-value" style="color:#f59e0b;">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
      </div>
      <div class="stat-icon si-orange"><i class='bx bx-time'></i></div>
    </div>
    <div class="stat-card">
      <div>
        <div class="stat-label">Jatuh Tempo</div>
        <div class="stat-value" style="color:#dc2626;">Rp {{ number_format($totalOverdue, 0, ',', '.') }}</div>
      </div>
      <div class="stat-icon si-red"><i class='bx bx-error-circle'></i></div>
    </div>
    <div class="stat-card">
      <div>
        <div class="stat-label">Total Pembayaran</div>
        <div class="stat-value">{{ number_format($totalInvoices) }}</div>
      </div>
      <div class="stat-icon si-green"><i class='bx bx-receipt'></i></div>
    </div>
  </div>

  <div class="ms-panel mb-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-line-chart me-2' style="color:#2563eb;"></i>Pendapatan Bulanan {{ $year }}</h5>
    </div>
    <div class="ms-panel-body">
      <canvas id="revenueChart" height="100"></canvas>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-xl-6">
      <div class="ms-panel h-100">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-map me-2' style="color:#2563eb;"></i>Pendapatan per Area</h5>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>Area</th>
                  <th class="text-end">Jumlah</th>
                  <th class="text-end">Pendapatan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($revenueByArea as $row)
                <tr>
                  <td><strong>{{ $row->area_name }}</strong></td>
                  <td class="text-end">{{ $row->count }}</td>
                  <td class="text-end" style="font-weight:700;color:var(--green);">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data untuk {{ $year }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-6">
      <div class="ms-panel h-100">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-group me-2' style="color:#2563eb;"></i>Pendapatan per PIC</h5>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>PIC</th>
                  <th class="text-end">Jumlah</th>
                  <th class="text-end">Pendapatan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($revenueByPartner as $row)
                <tr>
                  <td><strong>{{ $row->partner_name }}</strong></td>
                  <td class="text-end">{{ $row->count }}</td>
                  <td class="text-end" style="font-weight:700;color:var(--green);">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data PIC untuk {{ $year }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="ms-panel mt-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-calendar me-2' style="color:#2563eb;"></i>Rincian Bulanan</h5>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table mb-0" style="min-width:760px;">
          <thead>
            <tr>
              <th>Bulan</th>
              <th class="text-end">Pembayaran Disetujui</th>
              <th class="text-end">Pendapatan</th>
              <th style="width:40%;">Progres</th>
            </tr>
          </thead>
          <tbody>
            @php $maxMonthly = max(array_column($monthlyData, 'total')) ?: 1; @endphp
            @foreach($monthlyData as $m)
            <tr>
              <td><strong>{{ $m['label'] }} {{ $year }}</strong></td>
              <td class="text-end">{{ $m['count'] }}</td>
              <td class="text-end" style="font-weight:700;color:{{ $m['total'] > 0 ? '#16a34a' : '#94a3b8' }};">
                Rp {{ number_format($m['total'], 0, ',', '.') }}
              </td>
              <td>
                <div style="height:10px;background:var(--border);border-radius:999px;overflow:hidden;">
                  <div style="width:{{ round(($m['total'] / $maxMonthly) * 100) }}%;height:100%;background:var(--blue);border-radius:999px;"></div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="font-weight:700;">
              <td>TOTAL {{ $year }}</td>
              <td class="text-end">{{ array_sum(array_column($monthlyData, 'count')) }}</td>
              <td class="text-end">Rp {{ number_format(array_sum(array_column($monthlyData, 'total')), 0, ',', '.') }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  {{-- Revenue per PIC per Bulan --}}
  @if(!empty($partnerMonthly))
  <div class="ms-panel mt-3">
    <div class="ms-panel-head">
      <h5 class="ms-panel-title"><i class='bx bx-group me-2'></i>Pendapatan per PIC per Bulan — {{ $year }}</h5>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        @php $mnth = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; @endphp
        <table class="table table-sm mb-0" style="font-size:.78rem;min-width:1100px;">
          <thead>
            <tr>
              <th style="min-width:130px;">PIC</th>
              @foreach($mnth as $mn)<th class="text-end">{{ $mn }}</th>@endforeach
              <th class="text-end" style="font-weight:700;">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($partnerMonthly as $pname => $months)
            @php $rowTotal = array_sum($months); @endphp
            <tr>
              <td style="font-weight:600;">{{ $pname }}</td>
              @for($m=1;$m<=12;$m++)
              <td class="text-end" style="color:{{ isset($months[$m]) ? 'var(--green)' : 'var(--txt-3)' }};">
                {{ isset($months[$m]) ? 'Rp '.number_format($months[$m],0,',','.') : '—' }}
              </td>
              @endfor
              <td class="text-end" style="font-weight:700;">Rp {{ number_format($rowTotal,0,',','.') }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="font-weight:700;background:var(--hover-bg);">
              <td>TOTAL</td>
              @for($m=1;$m<=12;$m++)
              @php $colTotal = array_sum(array_column($partnerMonthly, $m)); @endphp
              <td class="text-end">{{ $colTotal > 0 ? 'Rp '.number_format($colTotal,0,',','.') : '—' }}</td>
              @endfor
              <td class="text-end">Rp {{ number_format(array_sum(array_merge(...array_values(array_map('array_values',$partnerMonthly)))),0,',','.') }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('revenueChart').getContext('2d');
    var data = @json($monthlyData);

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.map(d => d.label),
        datasets: [{
          label: 'Pendapatan (Rp)',
          data: data.map(d => d.total),
          backgroundColor: 'rgba(37, 99, 235, 0.72)',
          borderColor: 'rgba(37, 99, 235, 1)',
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                return 'Rp ' + ctx.raw.toLocaleString('id-ID');
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(val) {
                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
                if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'K';
                return 'Rp ' + val;
              }
            }
          }
        }
      }
    });
  });
</script>
@endpush
