@extends('layouts.app')
@section('title', 'Batch Voucher — ' . $batch->name)

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-barcode'></i> Batch Voucher</div>
      <h1 class="ms-page-title">{{ $batch->name }}</h1>
    </div>
    <div class="ms-page-actions">
      @if($batch->type === 'hotspot')
      <span class="badge-status badge-active">HOTSPOT</span>
      @else
      <span class="badge-status" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">PPPOE</span>
      @endif
    </div>
  </div>

  <div class="stat-grid">
    @foreach([['Total', $batch->total, '#1e293b'], ['Digunakan', $batch->used, '#ef4444'], ['Tersedia', $batch->available_count, '#16a34a']] as [$label, $value, $color])
    <div class="stat-card">
      <div>
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value" style="color:{{ $color }};">{{ $value }}</div>
      </div>
      <div class="stat-icon si-blue"><i class='bx bx-bar-chart-alt-2'></i></div>
    </div>
    @endforeach
  </div>

  <div class="ms-panel">
    <div class="ms-panel-head d-flex justify-content-between align-items-center">
      <h5 class="ms-panel-title"><i class='bx bx-list-ul me-2' style="color:#2563eb;"></i>Kode Voucher</h5>
      <div style="font-size:.78rem;color:#64748b;">
        {{ $batch->duration_days }} hari · Rp {{ number_format($batch->price, 0, ',', '.') }} · {{ $batch->profile }}
      </div>
    </div>
    <div class="ms-table-shell">
      <div class="table-responsive">
        <table class="table ms-table-wide mb-0" style="min-width:920px;">
          <thead>
            <tr>
              <th style="min-width:80px;">#</th>
              <th style="min-width:280px;">Kode</th>
              <th style="min-width:120px;">Status</th>
              <th style="min-width:220px;">Ditukar Oleh</th>
              <th style="min-width:160px;">Ditukar Pada</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vouchers as $v)
            <tr>
              <td>{{ $v->id }}</td>
              <td>
                <code style="font-size:.84rem;font-weight:700;letter-spacing:.4px;">{{ $v->code }}</code>
                <button class="btn btn-sm btn-clipboard p-0 ms-1" data-clipboard-text="{{ $v->code }}" title="Salin" style="border:none;background:none;color:#94a3b8;cursor:pointer;">
                  <i class='bx bx-copy'></i>
                </button>
                @if($v->status !== 'used')
                <button class="btn btn-sm p-0 ms-1 btn-qr-toggle" data-code="{{ $v->code }}" title="Tampilkan QR" style="border:none;background:none;color:#2563eb;cursor:pointer;">
                  <i class='bx bx-qr'></i>
                </button>
                @endif
                <div class="qr-container mt-2" id="qr-{{ $v->id }}" style="display:none;"></div>
              </td>
              <td>
                @if($v->status === 'used')
                <span class="badge-status badge-active">Digunakan</span>
                @elseif($v->status === 'expired')
                <span class="badge-status badge-danger">Kedaluwarsa</span>
                @else
                <span class="badge-status badge-inactive">Belum Digunakan</span>
                @endif
              </td>
              <td>{{ $v->customer?->name ?? '—' }}</td>
              <td>{{ $v->redeemed_at?->format('d M Y H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-state-icon"><i class='bx bx-barcode'></i></div>
                  <div class="empty-state-title">Tidak ada voucher dalam batch ini</div>
                  <div class="empty-state-desc">Kode yang dibuat untuk batch ini akan muncul di sini.</div>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($vouchers->hasPages())
    <div class="ms-panel-body pt-2">
      {{ $vouchers->links() }}
    </div>
    @endif
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.querySelectorAll('.btn-qr-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var code = btn.getAttribute('data-code');
      var container = btn.parentElement.querySelector('.qr-container');
      if (container.style.display === 'none') {
        container.style.display = 'block';
        if (!container.hasChildNodes()) {
          new QRCode(container, {
            text: code,
            width: 96,
            height: 96,
            colorDark: '#1e293b',
            colorLight: '#ffffff'
          });
        }
      } else {
        container.style.display = 'none';
      }
    });
  });
</script>
@endsection
