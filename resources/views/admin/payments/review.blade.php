@extends('layouts.app')
@section('title', 'Review Pembayaran')

@section('styles')
<style>
  /* ── Page ── */
  .review-page { max-width: 100%; }

  /* ── Toolbar ── */
  .review-toolbar {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    padding: .75rem 1rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    margin-bottom: 1rem;
  }
  .review-toolbar .bulk-counter {
    font-size: .82rem;
    font-weight: 600;
    color: var(--txt-2);
    margin-left: auto;
  }
  .review-toolbar .bulk-counter strong { color: var(--blue); }

  /* ── Card ── */
  .pcard {
    border: 1.5px solid var(--border);
    border-radius: 14px;
    background: var(--surface);
    margin-bottom: .875rem;
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
  }
  .pcard:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }
  .pcard.selected {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--blue) 12%, transparent);
  }

  .pcard-inner {
    display: grid;
    grid-template-columns: 2.5rem 1fr 220px;
    min-height: 168px;
  }

  /* Checkbox column */
  .pcard-check {
    display: flex;
    align-items: flex-start;
    padding: 1.1rem .5rem 1rem 1rem;
  }
  .pcard-check input[type=checkbox] {
    width: 18px; height: 18px;
    cursor: pointer;
    accent-color: var(--blue);
    flex-shrink: 0;
    margin-top: 2px;
  }

  /* Info column */
  .pcard-info {
    padding: 1rem 1rem 1rem .25rem;
    border-right: 1px solid var(--border);
  }
  .pcard-name {
    font-size: .95rem;
    font-weight: 700;
    color: var(--txt);
    margin: 0 0 2px;
    line-height: 1.2;
  }
  .pcard-meta {
    font-size: .76rem;
    color: var(--txt-3);
    margin-bottom: .6rem;
  }

  .pcard-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    margin-bottom: .75rem;
  }
  .chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .72rem;
    font-weight: 600;
    padding: .18rem .55rem;
    border-radius: 6px;
    border: 1px solid transparent;
  }
  .chip-blue   { background: color-mix(in srgb,var(--blue) 12%,var(--surface));  color:var(--blue);  border-color:color-mix(in srgb,var(--blue) 20%,var(--border)); }
  .chip-green  { background: color-mix(in srgb,#22c55e 12%,var(--surface));      color:#16a34a;      border-color:color-mix(in srgb,#22c55e 20%,var(--border)); }
  .chip-red    { background: color-mix(in srgb,#ef4444 12%,var(--surface));      color:#dc2626;      border-color:color-mix(in srgb,#ef4444 20%,var(--border)); }
  .chip-gray   { background: color-mix(in srgb,#94a3b8 10%,var(--surface));      color:var(--txt-2); border-color:var(--border); }
  .chip-orange { background: color-mix(in srgb,#f97316 12%,var(--surface));      color:#ea580c;      border-color:color-mix(in srgb,#f97316 20%,var(--border)); }

  .pcard-datarow {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: .4rem .75rem;
    margin-bottom: .75rem;
  }
  .pcard-field label {
    display: block;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--txt-3);
    margin-bottom: 1px;
  }
  .pcard-field span {
    font-size: .82rem;
    font-weight: 600;
    color: var(--txt);
  }
  .pcard-catatan {
    font-size: .76rem;
    color: var(--txt-2);
    background: color-mix(in srgb,#f59e0b 8%,var(--surface));
    border: 1px solid color-mix(in srgb,#f59e0b 20%,var(--border));
    border-radius: 7px;
    padding: .3rem .6rem;
    margin-bottom: .6rem;
  }

  /* Actions row */
  .pcard-actions {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
    padding-top: .6rem;
    border-top: 1px solid var(--border);
  }
  .pcard-actions form { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
  .period-sel {
    font-size: .76rem;
    padding: .22rem .4rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--txt);
  }
  .reject-inp {
    font-size: .78rem;
    padding: .28rem .55rem;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--txt);
    min-width: 170px;
  }

  /* Proof column */
  .pcard-proof {
    padding: .75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    background: color-mix(in srgb,var(--blue) 3%,var(--surface));
  }
  .proof-img {
    width: 100%;
    max-height: 180px;
    object-fit: cover;
    border-radius: 9px;
    border: 1px solid var(--border);
    cursor: zoom-in;
    transition: opacity .1s;
  }
  .proof-img:hover { opacity: .88; }
  .no-proof {
    text-align: center;
    color: var(--txt-3);
    font-size: .75rem;
  }

  /* Bulk action sticky bar */
  #bulk-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 2000;
    background: var(--blue);
    color: #fff;
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: .85rem 1.5rem;
    box-shadow: 0 -4px 24px rgba(37,99,235,.25);
    font-weight: 600;
    font-size: .9rem;
  }
  #bulk-bar.visible { display: flex; }
  #bulk-bar .bulk-actions { display: flex; gap: .5rem; }
  .btn-bulk-approve {
    background: #fff;
    color: var(--blue);
    border: none;
    border-radius: 8px;
    padding: .45rem 1.2rem;
    font-weight: 700;
    font-size: .84rem;
    cursor: pointer;
    display: flex; align-items: center; gap: .4rem;
    transition: background .15s;
  }
  .btn-bulk-approve:hover { background: #eff6ff; }
  .btn-bulk-cancel {
    background: rgba(255,255,255,.15);
    color: #fff;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 8px;
    padding: .45rem .9rem;
    font-weight: 600;
    font-size: .82rem;
    cursor: pointer;
  }
  .btn-bulk-cancel:hover { background: rgba(255,255,255,.25); }

  /* Proof modal */
  .proof-modal-img { max-width: 100%; max-height: 80vh; border-radius: 12px; }

  /* Responsive */
  @media (max-width: 640px) {
    .pcard-inner { grid-template-columns: 2.2rem 1fr; }
    .pcard-proof { display: none; }
  }
</style>
@endsection

@section('content')
@php
  $months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

<div class="ms-page review-page">
  <div class="ms-page-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-check-shield'></i> Review Pembayaran</div>
      <h1 class="ms-page-title">Antrian Review
        @if($payments->count())
          <span class="ms-kpi-chip ms-2"><strong>{{ $payments->count() }}</strong> menunggu</span>
        @endif
      </h1>
    </div>
    <div>
      <button onclick="document.getElementById('importModal').style.display='flex'" class="ms-btn-primary" style="display:flex; align-items:center; gap:0.5rem; border-radius:8px;">
        <i class='bx bx-import'></i> Import Excel
      </button>
    </div>
  </div>

  {{-- Flash messages --}}
  @foreach(['success','warning','error','info'] as $type)
    @if(session($type))
      @php
        $colors = ['success'=>'#d1fae5:#065f46','warning'=>'#fef3c7:#92400e','error'=>'#fee2e2:#991b1b','info'=>'#dbeafe:#1e40af'];
        [$bg, $clr] = explode(':', $colors[$type]);
      @endphp
      <div style="background:{{$bg}};color:{{$clr}};border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:500;">
        <i class='bx bx-{{ $type === "success" ? "check-circle" : ($type === "warning" ? "error" : ($type === "error" ? "x-circle" : "info-circle")) }} me-1'></i>
        {!! session($type) !!}
      </div>
    @endif
  @endforeach

  @if($payments->isEmpty())
    <div class="empty-state" style="margin-top:3rem;">
      <div class="empty-state-icon"><i class='bx bx-check-shield'></i></div>
      <div class="empty-state-title">Tidak ada pembayaran menunggu</div>
      <div class="empty-state-desc">Semua pembayaran sudah diproses. Cek kembali nanti.</div>
    </div>
  @else
    {{-- Toolbar --}}
    <div class="review-toolbar">
      <input type="checkbox" id="select-all" style="width:18px;height:18px;cursor:pointer;accent-color:var(--blue);" title="Pilih semua">
      <label for="select-all" style="font-size:.84rem;font-weight:600;cursor:pointer;color:var(--txt-2);margin:0;">Pilih Semua</label>
      <div class="bulk-counter">
        <span id="selected-label">0 dipilih</span>
      </div>
    </div>

    {{-- Cards --}}
    <form id="bulk-form" action="{{ route('admin.payments.bulk-approve') }}" method="POST">
      @csrf
      @foreach($payments as $payment)
        @php
          $customer = $payment->customer;
          $ageMinutes = (int) $payment->created_at->diffInMinutes(now());
          $ageHours   = (int) $payment->created_at->diffInHours(now());
          $ageDays    = (int) $payment->created_at->diffInDays(now());
          if ($ageDays > 0)        $ageLabel = $ageDays . ' hari lalu';
          elseif ($ageHours > 0)   $ageLabel = $ageHours . ' jam lalu';
          else                     $ageLabel = max(1, $ageMinutes) . ' menit lalu';
        @endphp
        <div class="pcard" id="pcard-{{ $payment->id }}">
          <div class="pcard-inner">

            {{-- Checkbox --}}
            <div class="pcard-check">
              <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}"
                     class="payment-cb" id="cb-{{ $payment->id }}"
                     onchange="onCbChange()">
            </div>

            {{-- Info --}}
            <div class="pcard-info">
              <div class="pcard-name">{{ $customer->name ?? '—' }}</div>
              <div class="pcard-meta">
                {{ $customer->customer_code ?? '' }}
                @if($customer?->area) · {{ $customer->area->name }} @endif
                @if($customer?->partner) · {{ $customer->partner->name }} @endif
                @if($customer?->package) · {{ $customer->package->name }} @endif
              </div>

              <div class="pcard-chips">
                <span class="chip chip-blue">
                  <i class='bx bx-calendar-alt'></i>
                  {{ $months[$payment->periode_bulan] ?? $payment->periode_bulan }} {{ $payment->periode_tahun }}
                </span>
                @if($customer?->is_isolated)
                  <span class="chip chip-red">
                    <i class='bx bx-shield-x'></i> Terisolir — approve akan de-isolir otomatis
                  </span>
                @endif
                <span class="chip chip-gray"><i class='bx bx-time'></i> {{ $ageLabel }}</span>
              </div>

              <div class="pcard-datarow">
                <div class="pcard-field">
                  <label>Jumlah</label>
                  <span style="color:#16a34a;">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</span>
                </div>
                <div class="pcard-field">
                  <label>Metode</label>
                  <span>{{ ucfirst($payment->metode) }}</span>
                </div>
                <div class="pcard-field">
                  <label>Rekening Tujuan</label>
                  <span>{{ $payment->rekening_tujuan }}</span>
                </div>
                <div class="pcard-field">
                  <label>Dikirim</label>
                  <span>{{ $payment->created_at->format('d M Y, H:i') }}</span>
                </div>
              </div>

              @if($payment->catatan)
                <div class="pcard-catatan"><i class='bx bx-note me-1'></i>{{ $payment->catatan }}</div>
              @endif

              {{-- Single-card actions --}}
              <div class="pcard-actions">
                <form action="{{ route('admin.payments.approve', $payment) }}" method="POST">
                  @csrf
                  <div class="d-flex align-items-center gap-1">
                    <select name="periode_bulan" class="period-sel" title="Override bulan">
                      @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $payment->periode_bulan == $m ? 'selected' : '' }}>{{ $months[$m] }}</option>
                      @endfor
                    </select>
                    <select name="periode_tahun" class="period-sel" title="Override tahun">
                      @for($y = 2024; $y <= 2027; $y++)
                        <option value="{{ $y }}" {{ $payment->periode_tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                      @endfor
                    </select>
                  </div>
                  <button type="submit" class="ms-btn" style="font-size:.78rem;padding:.3rem .75rem;">
                    <i class='bx bx-check'></i> Setujui
                  </button>
                </form>

                <form action="{{ route('admin.payments.reject', $payment) }}" method="POST">
                  @csrf
                  <input type="text" name="reject_reason" class="reject-inp" placeholder="Alasan penolakan..." required>
                  <button type="submit" class="ms-btn-ghost" style="font-size:.78rem;padding:.3rem .75rem;color:var(--red);">
                    <i class='bx bx-x'></i> Tolak
                  </button>
                </form>
              </div>
            </div>

            {{-- Proof image --}}
            <div class="pcard-proof">
              @if($payment->bukti_url)
                <img src="{{ $payment->bukti_url }}"
                     alt="Bukti bayar {{ $customer->name ?? '' }}"
                     class="proof-img"
                     onclick="showProof('{{ $payment->bukti_url }}', '{{ addslashes($customer->name ?? '') }}')"
                     loading="lazy">
                <div style="font-size:.7rem;color:var(--txt-3);text-align:center;">Klik untuk perbesar</div>
              @else
                <div class="no-proof">
                  <i class='bx bx-image-alt' style="font-size:2rem;opacity:.35;"></i>
                  <div>Tanpa bukti</div>
                </div>
              @endif
            </div>

          </div>
        </div>
      @endforeach
    </form>
  @endif
</div>

{{-- Bulk action sticky bar --}}
<div id="bulk-bar">
  <div><i class='bx bx-check-square me-2'></i><span id="bulk-label">0 pembayaran dipilih</span></div>
  <div class="bulk-actions">
    <button type="button" class="btn-bulk-approve" onclick="submitBulkApprove()">
      <i class='bx bx-check-circle'></i> Setujui Semua Pilihan
    </button>
    <button type="button" class="btn-bulk-cancel" onclick="clearAll()">Batalkan</button>
  </div>
</div>

{{-- Import Modal --}}
<div id="importModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
  <div style="background:var(--surface); width:100%; max-width:480px; border-radius:16px; padding:1.5rem; box-shadow:0 10px 40px rgba(0,0,0,0.1);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
      <h3 style="margin:0; font-size:1.1rem; color:var(--txt);"><i class='bx bx-import' style="color:var(--blue); margin-right:5px;"></i> Import Pembayaran Excel</h3>
      <button type="button" onclick="document.getElementById('importModal').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--txt-3);">&times;</button>
    </div>
    <form action="{{ route('admin.payments.import') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:.85rem; color:var(--txt-2); margin-bottom:.3rem;">Periode Bulan Tagihan</label>
        <select name="periode_bulan" class="ms-input" required style="width:100%; padding:.6rem; border-radius:8px; border:1px solid var(--border);">
          @foreach($months as $k => $v)
            @if($k > 0)
              <option value="{{ $k }}" {{ now()->month == $k ? 'selected' : '' }}>{{ $v }}</option>
            @endif
          @endforeach
        </select>
      </div>
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:.85rem; color:var(--txt-2); margin-bottom:.3rem;">Tahun Tagihan</label>
        <input type="number" name="periode_tahun" class="ms-input" required value="{{ now()->year }}" min="2020" max="2030" style="width:100%; padding:.6rem; border-radius:8px; border:1px solid var(--border);">
      </div>
      <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:.85rem; color:var(--txt-2); margin-bottom:.3rem;">File Excel (Dari Laporan/Daftar Pelanggan)</label>
        <input type="file" name="file" accept=".xlsx, .xls, .csv" required class="ms-input" style="width:100%; padding:.5rem; border-radius:8px; border:1px solid var(--border);">
      </div>
      <div style="background:#f8fafc; border:1px dashed #cbd5e1; padding:1rem; border-radius:8px; font-size:.8rem; color:#475569; margin-bottom:1.5rem; line-height:1.5;">
        <strong>Cara Pakai:</strong><br>
        1. Download Excel dari halaman Pelanggan.<br>
        2. Buka Excel, isi tanggal di kolom <strong>"Tgl Bayar"</strong> atau ketik "Lunas" di kolom <strong>"Pembayaran"</strong>.<br>
        3. Simpan dan Upload ke sini. Sistem otomatis merekam pembayaran & buka isolir.
      </div>
      <div style="display:flex; justify-content:flex-end; gap:.5rem;">
        <button type="button" onclick="document.getElementById('importModal').style.display='none'" class="ms-btn-secondary" style="padding:.6rem 1.2rem; border-radius:8px;">Batal</button>
        <button type="submit" class="ms-btn-primary" style="padding:.6rem 1.2rem; border-radius:8px;" onclick="this.innerHTML='<i class=\'bx bx-loader-alt bx-spin\'></i> Memproses...'; this.style.pointerEvents='none'; this.form.submit();">Import Data</button>
      </div>
    </form>
  </div>
</div>

@endsection

{{-- Proof Image Modal: OUTSIDE @section('content') so workspace-shell overflow:hidden doesn't block clicks --}}
@push('modals')
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content" style="border-radius:16px;border:1px solid var(--border);overflow:hidden;background:var(--surface);">
      <div class="modal-header" style="border-bottom:1px solid var(--border);padding:.75rem 1rem;">
        <h6 class="modal-title fw-bold" id="proofModalTitle">Bukti Pembayaran</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center" style="padding:1rem;">
        <img id="proofModalImg" src="" alt="Bukti pembayaran" class="proof-modal-img">
      </div>
    </div>
  </div>
</div>
@endpush

@section('scripts')
<script>
function onCbChange() {
  const cbs     = document.querySelectorAll('.payment-cb');
  const checked = document.querySelectorAll('.payment-cb:checked');
  const bar     = document.getElementById('bulk-bar');
  const label   = document.getElementById('bulk-label');
  const selLabel= document.getElementById('selected-label');
  const n       = checked.length;

  // Update card highlight
  cbs.forEach(cb => {
    const card = document.getElementById('pcard-' + cb.value);
    if (card) card.classList.toggle('selected', cb.checked);
  });

  // Update counter
  selLabel.innerHTML = '<strong>' + n + '</strong> dipilih';
  label.textContent  = n + ' pembayaran dipilih';

  // Show/hide sticky bar
  bar.classList.toggle('visible', n > 0);

  // Sync select-all state
  const selectAll = document.getElementById('select-all');
  if (selectAll) {
    selectAll.indeterminate = n > 0 && n < cbs.length;
    selectAll.checked       = n > 0 && n === cbs.length;
  }
}

document.getElementById('select-all')?.addEventListener('change', function() {
  document.querySelectorAll('.payment-cb').forEach(cb => {
    cb.checked = this.checked;
  });
  onCbChange();
});

function clearAll() {
  document.querySelectorAll('.payment-cb').forEach(cb => cb.checked = false);
  const sa = document.getElementById('select-all');
  if (sa) sa.checked = false;
  onCbChange();
}

function submitBulkApprove() {
  const checked = document.querySelectorAll('.payment-cb:checked');
  if (!checked.length) return;
  if (!confirm('Setujui ' + checked.length + ' pembayaran? Pelanggan yang sedang terisolir akan otomatis di-de-isolir.')) return;
  document.getElementById('bulk-form').submit();
}

function showProof(url, name) {
  document.getElementById('proofModalImg').src = url;
  document.getElementById('proofModalTitle').textContent = 'Bukti — ' + (name || 'Pelanggan');
  var modalEl = document.getElementById('proofModal');
  bootstrap.Modal.getOrCreateInstance(modalEl).show();
}
</script>
@endsection
