@extends('layouts.app')
@section('title', 'Review Pembayaran')

@section('styles')
<style>
  .payment-review-page .ms-panel {
    border: none !important; box-shadow: none !important;
    background: transparent !important; border-radius: 0 !important;
  }
  .payment-review-page .ms-panel-head {
    border-bottom: 1px solid var(--border) !important;
    border-radius: 0 !important; background: transparent !important;
  }
  .payment-review-page .ms-panel-body { background: transparent !important; }

  .payment-card {
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: box-shadow .15s;
  }
  .payment-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
  }
  .payment-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: .75rem;
  }
  .payment-customer-info h4 {
    font-size: .95rem;
    font-weight: 700;
    margin: 0;
    color: var(--txt);
  }
  .payment-customer-info .meta {
    font-size: .78rem;
    color: var(--txt-3);
    margin-top: 2px;
  }
  .payment-period-badge {
    display: inline-flex;
    align-items: center;
    padding: .2rem .6rem;
    background: color-mix(in srgb, var(--blue) 10%, var(--surface));
    color: var(--blue);
    border: 1px solid color-mix(in srgb, var(--blue) 20%, var(--border));
    border-radius: 6px;
    font-size: .76rem;
    font-weight: 600;
  }
  .payment-details {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: .5rem;
    margin-bottom: .75rem;
  }
  .payment-detail-item label {
    display: block;
    font-size: .7rem;
    font-weight: 600;
    color: var(--txt-3);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 2px;
  }
  .payment-detail-item span {
    font-size: .85rem;
    font-weight: 600;
    color: var(--txt);
  }
  .payment-proof-thumb {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: opacity .12s;
  }
  .payment-proof-thumb:hover { opacity: .8; }
  .payment-actions {
    display: flex;
    gap: .5rem;
    align-items: flex-start;
    flex-wrap: wrap;
    padding-top: .75rem;
    border-top: 1px solid var(--border);
  }
  .payment-actions form {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
  }
  .period-override {
    display: inline-flex;
    gap: .3rem;
    align-items: center;
  }
  .period-override select {
    font-size: .78rem;
    padding: .2rem .4rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--txt);
  }
  .reject-input {
    font-size: .8rem;
    padding: .3rem .6rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--txt);
    min-width: 180px;
  }

  /* Proof modal */
  .proof-modal-img {
    max-width: 100%;
    max-height: 80vh;
    border-radius: 12px;
  }
</style>
@endsection

@section('content')
<div class="ms-page payment-review-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-check-shield'></i> Review Pembayaran</div>
      <h1 class="ms-page-title">Pembayaran Menunggu Review</h1>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success" style="border-radius:10px;border:1px solid #b7e4c7;background:#d8f3dc;color:#1b4332;margin-bottom:1rem;">
    <i class='bx bx-check-circle me-1'></i> {{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger" style="border-radius:10px;border:1px solid #f5c6cb;background:#f8d7da;color:#721c24;margin-bottom:1rem;">
    <i class='bx bx-error-circle me-1'></i> {{ session('error') }}
  </div>
  @endif

  <div class="ms-panel">
    <div class="ms-panel-head d-flex justify-content-between align-items-center">
      <span class="ms-panel-title">
        <i class='bx bx-time-five me-2' style="color:var(--orange, #f97316);"></i>Antrian Review
        <span class="ms-2 ms-kpi-chip"><strong>{{ $payments->count() }}</strong> menunggu</span>
      </span>
    </div>

    <div class="ms-panel-body">
      @forelse($payments as $payment)
      @php
        $customer = $payment->customer;
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      @endphp
      <div class="payment-card">
        <div class="payment-card-header">
          <div class="payment-customer-info">
            <h4>{{ $customer->name ?? '—' }}</h4>
            <div class="meta">
              {{ $customer->area->name ?? '—' }}
              · PIC: {{ $customer->partner->name ?? '—' }}
              · {{ $customer->package->name ?? '—' }}
            </div>
          </div>
          <div class="payment-period-badge">
            {{ $months[$payment->periode_bulan] ?? $payment->periode_bulan }} {{ $payment->periode_tahun }}
          </div>
        </div>

        <div class="payment-details">
          <div class="payment-detail-item">
            <label>Jumlah</label>
            <span>Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
          </div>
          <div class="payment-detail-item">
            <label>Metode</label>
            <span>{{ ucfirst($payment->metode) }}</span>
          </div>
          <div class="payment-detail-item">
            <label>Rekening</label>
            <span>{{ $payment->rekening_tujuan }}</span>
          </div>
          <div class="payment-detail-item">
            <label>Tanggal Upload</label>
            <span>{{ $payment->created_at->format('d M Y H:i') }}</span>
          </div>
          @if($payment->bukti_url)
          <div class="payment-detail-item">
            <label>Bukti</label>
            <img src="{{ $payment->bukti_url }}" alt="Bukti pembayaran"
                 class="payment-proof-thumb"
                 onclick="showProof('{{ $payment->bukti_url }}')">
          </div>
          @endif
        </div>

        @if($payment->catatan)
        <div style="font-size:.8rem;color:var(--txt-3);margin-bottom:.75rem;">
          <strong>Catatan:</strong> {{ $payment->catatan }}
        </div>
        @endif

        <div class="payment-actions">
          {{-- Approve form --}}
          <form action="{{ route('admin.payments.approve', $payment) }}" method="POST">
            @csrf
            <div class="period-override">
              <select name="periode_bulan" title="Override bulan">
                <option value="">Bulan</option>
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $payment->periode_bulan == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                @endfor
              </select>
              <select name="periode_tahun" title="Override tahun">
                <option value="">Tahun</option>
                @for($y = 2024; $y <= 2027; $y++)
                <option value="{{ $y }}" {{ $payment->periode_tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            <button type="submit" class="ms-btn" style="font-size:.8rem;padding:.35rem .75rem;">
              <i class='bx bx-check'></i> Setujui
            </button>
          </form>

          {{-- Reject form --}}
          <form action="{{ route('admin.payments.reject', $payment) }}" method="POST">
            @csrf
            <input type="text" name="reject_reason" class="reject-input" placeholder="Alasan penolakan..." required>
            <button type="submit" class="ms-btn-ghost" style="font-size:.8rem;padding:.35rem .75rem;color:var(--red);">
              <i class='bx bx-x'></i> Tolak
            </button>
          </form>
        </div>
      </div>
      @empty
      <div class="empty-state">
        <div class="empty-state-icon"><i class='bx bx-check-shield'></i></div>
        <div class="empty-state-title">Tidak ada pembayaran menunggu</div>
        <div class="empty-state-desc">Semua pembayaran sudah diproses</div>
      </div>
      @endforelse
    </div>
  </div>
</div>

{{-- Proof Image Modal --}}
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:16px;border:1px solid var(--border);overflow:hidden;background:var(--surface);">
      <div class="modal-header" style="border-bottom:1px solid var(--border);padding:.75rem 1rem;">
        <h6 class="modal-title" style="font-weight:700;">Bukti Pembayaran</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center" style="padding:1rem;">
        <img id="proofModalImg" src="" alt="Bukti pembayaran" class="proof-modal-img">
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function showProof(url) {
  document.getElementById('proofModalImg').src = url;
  var modal = new bootstrap.Modal(document.getElementById('proofModal'));
  modal.show();
}
</script>
@endsection
