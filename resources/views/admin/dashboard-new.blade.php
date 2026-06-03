@extends('layouts.app')
@section('title', 'Dasbor')

@section('content')
<div class="ms-page">
  <!-- Page Header -->
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-tachometer'></i> Ringkasan</div>
      <h1 class="ms-page-title">Dasbor</h1>
      <div class="ms-page-subtitle">Ringkasan jaringan dan bisnis secara real-time</div>
    </div>
    <div class="ms-page-actions">
      <button class="ms-btn-secondary" onclick="location.reload()">
        <i class='bx bx-refresh'></i> Perbarui
      </button>
    </div>
  </div>

  <!-- KPI Cards Row -->
  <div class="ms-stat-grid">
    <div class="ms-stat-card">
      <div class="ms-stat-icon si-primary">
            <i class='bx bx-user'></i>
      </div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Total Pelanggan</div>
        <div class="ms-stat-value">{{ $stats['total_customers'] ?? 0 }}</div>
        <div class="ms-stat-meta">
          <span class="text-success"><i class='bx bx-up-arrow-alt'></i> {{ $stats['active_customers'] ?? 0 }} aktif</span>
        </div>
      </div>
    </div>

    <div class="ms-stat-card">
      <div class="ms-stat-icon si-success">
        <i class='bx bx-check-circle'></i>
      </div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Layanan Aktif</div>
        <div class="ms-stat-value">{{ $stats['active_customers'] ?? 0 }}</div>
        <div class="ms-stat-meta">
          @php $activeRate = ($stats['total_customers'] ?? 0) > 0 ? round(($stats['active_customers'] / $stats['total_customers']) * 100) : 0; @endphp
          <span class="text-success">{{ $activeRate }}% tingkat aktif</span>
        </div>
      </div>
    </div>

    <div class="ms-stat-card">
      <div class="ms-stat-icon si-warning">
        <i class='bx bx-receipt'></i>
      </div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Tagihan Belum Dibayar</div>
        <div class="ms-stat-value">{{ $stats['unpaid_invoices_count'] ?? 0 }}</div>
        <div class="ms-stat-meta">
          <span class="text-danger">{{ $stats['overdue_invoices_count'] ?? 0 }} jatuh tempo</span>
        </div>
      </div>
    </div>

    <div class="ms-stat-card">
      <div class="ms-stat-icon si-info">
        <i class='bx bx-dollar'></i>
      </div>
      <div class="ms-stat-content">
        <div class="ms-stat-label">Revenue ({{ now()->format('M') }})</div>
        <div class="ms-stat-value" style="font-size: 1.375rem;">Rp {{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', '.') }}</div>
        <div class="ms-stat-meta">
          MRR: Rp {{ number_format(($stats['mrr'] ?? 0) / 1000000, 1, ',', '.') }}jt
        </div>
      </div>
    </div>
  </div>

  <!-- Network Status Row -->
  <div class="ms-panel">
    <div class="ms-panel-head">
      <div>
        <h5 class="ms-panel-title"><i class='bx bx-wifi'></i> Status Jaringan</h5>
        <div class="ms-panel-subtitle">Pemantauan langsung</div>
      </div>
      <div class="ms-toolbar-right">
        <span class="ms-chip"><i class='bx bx-time'></i> Perbarui otomatis 30d</span>
      </div>
    </div>
    <div class="ms-panel-body">
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <!-- ACS ONTs -->
        <div class="ms-detail-card is-soft">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(22, 163, 74, 0.1); display: flex; align-items: center; justify-content: center;">
              <i class='bx bx-wifi' style="font-size: 1.25rem; color: var(--nk-success);"></i>
            </div>
            <div>
              <div class="ms-detail-label">ACS ONTs</div>
              <div class="ms-detail-value">
                <span id="live-acs-online" style="color: var(--nk-success);">—</span>
                <span class="text-muted">/ <span id="live-acs-total">—</span></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 8px;">
            <span class="live-dot pulse-green" style="width: 8px; height: 8px; border-radius: 50%; background: var(--nk-success); display: inline-block;"></span>
            <span class="text-muted" style="font-size: 0.6875rem;">ONLINE</span>
          </div>
        </div>

        <!-- ONT OLT -->
        <div class="ms-detail-card is-soft">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(37, 99, 235, 0.1); display: flex; align-items: center; justify-content: center;">
              <i class='bx bx-server' style="font-size: 1.25rem; color: var(--nk-primary);"></i>
            </div>
            <div>
              <div class="ms-detail-label">OLT ONTs</div>
              <div class="ms-detail-value">
                <span id="live-ont-online" style="color: var(--nk-success);">—</span>
                <span class="text-muted">/ <span id="live-ont-total">—</span></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 8px;">
            <span class="live-dot pulse-green" style="width: 8px; height: 8px; border-radius: 50%; background: var(--nk-success); display: inline-block;"></span>
            <span class="text-muted" style="font-size: 0.6875rem;">ONLINE</span>
          </div>
        </div>

        <!-- Unpaid -->
        <div class="ms-detail-card is-soft">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(217, 119, 6, 0.1); display: flex; align-items: center; justify-content: center;">
                <i class='bx bx-calendar-exclamation' style="font-size: 1.25rem; color: var(--nk-warning);"></i>
            </div>
            <div>
              <div class="ms-detail-label">Belum Dibayar</div>
              <div class="ms-detail-value" style="color: var(--nk-warning);">
                <span id="live-unpaid">—</span>
              </div>
            </div>
          </div>
          <div style="margin-top: 8px;">
            <span id="live-overdue-wrap" style="display: none;">
              <span class="text-danger" style="font-size: 0.6875rem;">
                (<span id="live-overdue">0</span> jatuh tempo)
              </span>
            </span>
          </div>
        </div>

        <!-- Active -->
        <div class="ms-detail-card is-soft">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(22, 163, 74, 0.1); display: flex; align-items: center; justify-content: center;">
              <i class='bx bx-user-check' style="font-size: 1.25rem; color: var(--nk-success);"></i>
            </div>
            <div>
              <div class="ms-detail-label">Aktif</div>
              <div class="ms-detail-value">
                <span id="live-active" style="color: var(--nk-success);">—</span>
                <span class="text-muted">/ <span id="live-total-cust">—</span></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 8px;">
            <span class="live-dot pulse-green" style="width: 8px; height: 8px; border-radius: 50%; background: var(--nk-success); display: inline-block;"></span>
            <span class="text-muted" style="font-size: 0.6875rem;">LANGSUNG</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Revenue Chart Placeholder -->
    <div class="ms-panel">
      <div class="ms-panel-head">
        <div>
          <h5 class="ms-panel-title"><i class='bx bx-line-chart'></i> Tren Pendapatan</h5>
        </div>
      </div>
      <div class="ms-panel-body" style="height: 280px; display: flex; align-items: center; justify-content: center;">
        <div class="text-center">
          <i class='bx bx-chart-line' style="font-size: 3rem; color: var(--nk-text-muted);"></i>
          <div class="text-muted mt-2">Visualisasi grafik</div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="ms-panel">
      <div class="ms-panel-head">
        <div>
          <h5 class="ms-panel-title"><i class='bx bx-bolt'></i> Statistik Cepat</h5>
        </div>
      </div>
      <div class="ms-panel-body">
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--nk-bg); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <i class='bx bx-map-pin' style="font-size: 1.25rem; color: var(--nk-primary);"></i>
              <span>Area</span>
            </div>
            <span class="fw-semibold">{{ $stats['total_areas'] ?? 0 }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--nk-bg); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <i class='bx bx-package' style="font-size: 1.25rem; color: var(--nk-success);"></i>
              <span>Paket</span>
            </div>
            <span class="fw-semibold">{{ $stats['total_packages'] ?? 0 }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--nk-bg); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 12px;">
              <i class='bx bx-wifi' style="font-size: 1.25rem; color: var(--nk-info);"></i>
              <span>Sesi PPPoE</span>
            </div>
            <span class="fw-semibold">{{ $stats['pppoe_sessions'] ?? 0 }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity / Quick Links -->
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Quick Links -->
    <div class="ms-panel">
      <div class="ms-panel-head">
        <div>
          <h5 class="ms-panel-title"><i class='bx bx-link'></i> Tautan Cepat</h5>
        </div>
      </div>
      <div class="ms-panel-body" style="padding: 8px;">
        <a href="{{ route('admin.customers.index') }}" class="ms-quick-link">
          <i class='bx bx-user-plus'></i>
          <span>Tambah Pelanggan</span>
        </a>
        <a href="{{ route('admin.pppoe.index') }}" class="ms-quick-link">
          <i class='bx bx-sync'></i>
          <span>Sinkronisasi PPPoE</span>
        </a>
        <a href="{{ route('admin.olts.index') }}" class="ms-quick-link">
          <i class='bx bx-server'></i>
          <span>Manajemen OLT</span>
        </a>
        <a href="{{ route('admin.invoices.index') }}" class="ms-quick-link">
          <i class='bx bx-receipt'></i>
          <span>Lihat Tagihan</span>
        </a>
      </div>
    </div>

    <!-- System Info -->
    <div class="ms-panel">
      <div class="ms-panel-head">
        <div>
          <h5 class="ms-panel-title"><i class='bx bx-info-circle'></i> Info Sistem</h5>
        </div>
      </div>
      <div class="ms-panel-body">
        <div class="ms-detail-grid" style="grid-template-columns: 1fr;">
          <div class="ms-detail-card is-soft">
            <div class="ms-detail-label">Versi</div>
            <div class="ms-detail-value">NETKING ISP v2.0</div>
          </div>
          <div class="ms-detail-card is-soft">
            <div class="ms-detail-label">Waktu Server</div>
            <div class="ms-detail-value">{{ now()->format('d M Y H:i:s') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes pulse-green {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }
  .pulse-green {
    animation: pulse-green 2s infinite;
  }
  .ms-quick-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 8px;
    color: var(--nk-text);
    text-decoration: none;
    transition: background 0.15s;
  }
  .ms-quick-link:hover {
    background: var(--nk-bg);
    color: var(--nk-primary);
  }
  .ms-quick-link i {
    font-size: 1.25rem;
  }
</style>

<script>
// Live refresh for network status
document.addEventListener('DOMContentLoaded', function() {
  function fetchLiveStatus() {
    fetch('/api/dashboard/live')
      .then(r => r.json())
      .then(data => {
        if(data.acs_online !== undefined) {
          document.getElementById('live-acs-online').textContent = data.acs_online;
          document.getElementById('live-acs-total').textContent = data.acs_total;
        }
        if(data.ont_online !== undefined) {
          document.getElementById('live-ont-online').textContent = data.ont_online;
          document.getElementById('live-ont-total').textContent = data.ont_total;
        }
        if(data.unpaid !== undefined) {
          document.getElementById('live-unpaid').textContent = data.unpaid;
        }
        if(data.active !== undefined) {
          document.getElementById('live-active').textContent = data.active;
          document.getElementById('live-total-cust').textContent = data.total;
        }
      })
      .catch(() => {});
  }
  // Initial load
  fetchLiveStatus();
  // Auto-refresh every 30s
  setInterval(fetchLiveStatus, 30000);
});
</script>
@endsection
