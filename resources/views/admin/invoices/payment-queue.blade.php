@extends('layouts.app')
@section('title', 'Antrian Bukti Bayar')

@section('styles')
<style>
.proof-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow .15s;
}
.proof-card:hover { box-shadow: var(--shadow-md); }
.proof-card-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.proof-card-body {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 0;
}
.proof-img-wrap {
    border-right: 1px solid var(--border);
    background: var(--hover-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    cursor: pointer;
    position: relative;
}
.proof-img-wrap img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}
.proof-img-wrap .zoom-hint {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,.5);
    color: #fff;
    font-size: .7rem;
    padding: 2px 7px;
    border-radius: 6px;
}
.proof-detail {
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.proof-meta-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: .8125rem;
}
.proof-meta-row .label {
    color: var(--txt-3);
    min-width: 100px;
    flex-shrink: 0;
}
.proof-meta-row .val {
    color: var(--txt);
    font-weight: 500;
}
.proof-actions {
    padding: 14px 18px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.reject-form-wrap {
    display: none;
    padding: 14px 18px;
    border-top: 1px solid var(--border);
    background: #fff5f5;
}
.reject-form-wrap.show { display: block; }
.empty-queue {
    text-align: center;
    padding: 60px 20px;
    color: var(--txt-3);
}
/* Lightbox */
.proof-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.proof-lightbox.active { display: flex; }
.proof-lightbox img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.proof-lightbox-close {
    position: absolute;
    top: 20px;
    right: 24px;
    color: #fff;
    font-size: 2rem;
    cursor: pointer;
    line-height: 1;
}
@media(max-width:640px) {
    .proof-card-body { grid-template-columns: 1fr; }
    .proof-img-wrap { border-right: none; border-bottom: 1px solid var(--border); }
}
</style>
@endsection

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <h1 class="ms-page-title">Antrian Bukti Bayar</h1>
        </div>
        <div class="ms-page-actions">
            @if($pendingCount > 0)
                <span style="background:var(--red);color:#fff;font-size:.75rem;font-weight:700;padding:4px 10px;border-radius:20px;">
                    {{ $pendingCount }} menunggu
                </span>
            @endif
            <a href="{{ route('admin.invoices.index') }}" class="ms-btn-secondary">
                <i class='bx bx-list-ul'></i> Semua Invoice
            </a>
        </div>
    </div>



    @if($invoices->isEmpty())
        <div class="ms-panel">
            <div class="empty-queue">
                <i class='bx bx-check-shield' style="font-size:3rem;color:var(--green);display:block;margin-bottom:12px;"></i>
                <div style="font-size:1rem;font-weight:600;color:var(--txt);margin-bottom:6px;">Tidak ada bukti bayar yang perlu direview</div>
                <div style="font-size:.875rem;">Semua pembayaran sudah diproses.</div>
            </div>
        </div>
    @else
        <div class="ms-panel mb-3">
            <div class="ms-panel-head">
                <h5 class="ms-panel-title"><i class='bx bx-credit-card me-2'></i>Referensi Pembayaran Resmi</h5>
            </div>
            <div class="ms-panel-body">
                <div class="row g-3 align-items-start">
                    @if(!empty($paymentSettings['qris']))
                    <div class="col-lg-4">
                        <div style="border:1px solid var(--border);border-radius:14px;padding:14px;background:var(--surface-2);">
                            <div style="font-size:.75rem;color:var(--txt-3);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">QRIS</div>
                            <div style="font-weight:700;color:var(--txt);margin-bottom:10px;">{{ $paymentSettings['qris']['label'] ?? 'QRIS NETKING' }}</div>
                            <a href="{{ $paymentSettings['qris']['image_url'] }}" target="_blank" rel="noopener">
                                <img src="{{ $paymentSettings['qris']['image_url'] }}" alt="QRIS NETKING" style="width:100%;border-radius:12px;border:1px solid var(--border);">
                            </a>
                            @if(!empty($paymentSettings['qris']['notes']))
                            <div style="font-size:.75rem;color:var(--txt-3);margin-top:10px;line-height:1.5;">{{ $paymentSettings['qris']['notes'] }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="col-lg-8">
                        <div class="row g-3">
                            @foreach($paymentSettings['accounts'] ?? [] as $account)
                            <div class="col-md-6">
                                <div style="border:1px solid var(--border);border-radius:14px;padding:14px;background:var(--surface-2);height:100%;">
                                    <div style="font-size:.75rem;color:var(--txt-3);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Rekening {{ $account['bank_name'] }}</div>
                                    <div style="font-size:1.05rem;font-weight:800;color:var(--txt);letter-spacing:.6px;">{{ $account['account_number'] }}</div>
                                    <div style="font-size:.8rem;color:var(--txt-3);margin-top:4px;">a/n {{ $account['account_holder'] }}</div>
                                </div>
                            </div>
                            @endforeach
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class='bx bx-info-circle me-1'></i>
                                    {{ $paymentSettings['notes'] ?? 'Transfer atau bayar via QRIS sesuai nominal invoice, lalu upload bukti pembayaran agar admin bisa memverifikasi pembayaran Anda.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px;">
            @foreach($invoices as $invoice)
            <div class="proof-card" id="card-{{ $invoice->id }}">
                {{-- Header --}}
                <div class="proof-card-header">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div>
                            <div style="font-weight:700;font-size:.9375rem;color:var(--txt);">
                                {{ $invoice->customer->name }}
                            </div>
                            <div style="font-size:.8rem;color:var(--txt-3);">
                                {{ $invoice->invoice_number }}
                                &bull; {{ $invoice->customer->area->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:1rem;font-weight:700;color:var(--txt);">
                            Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                        </span>
                        <span style="background:var(--blue-lt);color:var(--blue);font-size:.72rem;font-weight:600;padding:3px 9px;border-radius:20px;">
                            Menunggu Review
                        </span>
                    </div>
                </div>

                {{-- Body --}}
                <div class="proof-card-body">
                    {{-- Foto bukti --}}
                    <div class="proof-img-wrap" onclick="openLightbox('{{ $invoice->payment_proof_url }}')">
                        @if($invoice->payment_proof_url)
                            <img src="{{ $invoice->payment_proof_url }}" alt="Bukti Bayar">
                            <span class="zoom-hint"><i class='bx bx-zoom-in'></i> Perbesar</span>
                        @else
                            <div style="text-align:center;padding:20px;color:var(--txt-3);">
                                <i class='bx bx-image' style="font-size:2rem;"></i>
                                <div style="font-size:.75rem;margin-top:4px;">Tidak ada foto</div>
                            </div>
                        @endif
                    </div>

                    {{-- Detail --}}
                    <div class="proof-detail">
                        <div class="proof-meta-row">
                            <span class="label">Pelanggan</span>
                            <span class="val">{{ $invoice->customer->name }}</span>
                        </div>
                        <div class="proof-meta-row">
                            <span class="label">No. HP</span>
                            <span class="val">{{ $invoice->customer->phone ?? '-' }}</span>
                        </div>
                        <div class="proof-meta-row">
                            <span class="label">Metode Bayar</span>
                            <span class="val">{{ $invoice->payment_method ?? '-' }}</span>
                        </div>
                        <div class="proof-meta-row">
                            <span class="label">Nama File</span>
                            <span class="val" style="word-break:break-all;font-size:.75rem;">
                                {{ $invoice->payment_proof_original_name ?? '-' }}
                            </span>
                        </div>
                        <div class="proof-meta-row">
                            <span class="label">Dikirim</span>
                            <span class="val">
                                {{ $invoice->payment_proof_submitted_at?->format('d M Y, H:i') ?? '-' }}
                            </span>
                        </div>
                        @if($invoice->payment_proof_notes)
                        <div class="proof-meta-row">
                            <span class="label">Catatan</span>
                            <span class="val" style="font-style:italic;">{{ $invoice->payment_proof_notes }}</span>
                        </div>
                        @endif
                        <div class="proof-meta-row">
                            <span class="label">Jatuh Tempo</span>
                            <span class="val" style="color:{{ $invoice->isOverdue() ? 'var(--red)' : 'var(--txt)' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                                @if($invoice->isOverdue()) <span style="font-size:.72rem;">(Lewat jatuh tempo)</span> @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="proof-actions">
                    {{-- Approve --}}
                    <form action="{{ route('admin.invoices.approveProof', $invoice) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="payment_method" value="{{ $invoice->payment_method ?? 'Transfer Manual' }}">
                        <button type="submit" class="btn btn-sm btn-success"
                            onclick="return confirm('Konfirmasi pembayaran {{ $invoice->invoice_number }}?')"
                            style="border-radius:8px;font-weight:600;padding:.4rem .9rem;">
                            <i class='bx bx-check me-1'></i> Konfirmasi Lunas
                        </button>
                    </form>

                    {{-- Reject toggle --}}
                    <button type="button" class="btn btn-sm btn-danger"
                        onclick="toggleRejectForm({{ $invoice->id }})"
                        style="border-radius:8px;font-weight:600;padding:.4rem .9rem;background:transparent;border:1px solid var(--red);color:var(--red);">
                        <i class='bx bx-x me-1'></i> Tolak
                    </button>

                    {{-- View invoice --}}
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="ms-btn-secondary" style="font-size:.8125rem;">
                        <i class='bx bx-show'></i> Detail
                    </a>
                </div>

                {{-- Reject form (hidden) --}}
                <div class="reject-form-wrap" id="reject-form-{{ $invoice->id }}">
                    <form action="{{ route('admin.invoices.rejectProof', $invoice) }}" method="POST">
                        @csrf
                        <div style="margin-bottom:10px;">
                            <label style="font-size:.8125rem;font-weight:600;color:var(--red);display:block;margin-bottom:6px;">
                                <i class='bx bx-error-circle me-1'></i> Alasan Penolakan
                            </label>
                            <select name="reject_reason_preset" class="form-select form-select-sm" style="border-radius:8px;margin-bottom:8px;">
                                <option value="">-- Pilih alasan --</option>
                                <option value="Bukti transfer tidak jelas / buram">Bukti transfer tidak jelas / buram</option>
                                <option value="Jumlah transfer tidak sesuai">Jumlah transfer tidak sesuai</option>
                                <option value="Tanggal transfer sudah kadaluarsa">Tanggal transfer sudah kadaluarsa</option>
                                <option value="Bukan bukti transfer yang valid">Bukan bukti transfer yang valid</option>
                                <option value="Rekening tujuan tidak sesuai">Rekening tujuan tidak sesuai</option>
                                <option value="Lainnya">Lainnya (tuliskan di bawah)</option>
                            </select>
                            <input type="text" name="reject_reason_custom" class="form-control form-control-sm"
                                placeholder="Atau tulis alasan lain..."
                                style="border-radius:8px;" oninput="this.form.querySelector('select').value=''">
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-sm btn-danger" style="border-radius:8px;font-weight:600;">
                                <i class='bx bx-send me-1'></i> Kirim Penolakan + Notif WA
                            </button>
                            <button type="button" class="btn btn-sm ms-btn-secondary"
                                onclick="toggleRejectForm({{ $invoice->id }})" style="border-radius:8px;">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $invoices->links() }}</div>
    @endif
</div>

{{-- Lightbox --}}
<div class="proof-lightbox" id="proof-lightbox" onclick="closeLightbox()">
    <span class="proof-lightbox-close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" src="" alt="Bukti Bayar">
</div>
@endsection

@section('scripts')
<script>
function openLightbox(url) {
    if (!url) return;
    document.getElementById('lightbox-img').src = url;
    document.getElementById('proof-lightbox').classList.add('active');
}
function closeLightbox() {
    document.getElementById('proof-lightbox').classList.remove('active');
    document.getElementById('lightbox-img').src = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

function toggleRejectForm(id) {
    const el = document.getElementById('reject-form-' + id);
    el.classList.toggle('show');
}

// Fix: select + input mutually exclusive
document.querySelectorAll('.reject-form-wrap select').forEach(sel => {
    sel.addEventListener('change', function() {
        const input = this.closest('form').querySelector('input[name="reject_reason_custom"]');
        if (this.value) input.value = '';
    });
});
</script>
@endsection
