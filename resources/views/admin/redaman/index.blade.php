@extends('layouts.app')
@section('title', 'Kalkulator Redaman')

@section('content')
<div class="page-header mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class='bx bx-broadcast me-2' style="color:var(--orange);"></i>Kalkulator Redaman
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Kalkulator Redaman</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-3">

    <!-- ── LEFT: Calculator Panel ─────────────────────────────────────── -->
    <div class="col-xl-7 col-lg-12">
        <div class="card">
            <div class="card-body p-0">

                <!-- Tab Switcher -->
                <div style="border-bottom:1px solid var(--border-color,#e5e7eb);padding:0 1rem;">
                    <div class="d-flex" style="gap:.25rem;">
                        <button id="tabFiber" class="rd-tab active" onclick="switchTab('fiber')">
                            <i class='bx bx-cable me-1'></i>Fiber Optik
                        </button>
                        <button id="tabWireless" class="rd-tab" onclick="switchTab('wireless')">
                            <i class='bx bx-signal-5 me-1'></i>Wireless / RF
                        </button>
                    </div>
                </div>

                <!-- ── FIBER FORM ──────────────────────────────────────────── -->
                <div id="fiberPanel" class="rd-panel p-3">
                    <div class="row g-3">

                        <!-- Nama/Label Titik -->
                        <div class="col-12">
                            <label class="form-label-sm">Nama / Label Titik</label>
                            <input type="text" id="f_name" class="form-control form-control-sm"
                                   placeholder="cth: ONT Pak Budi - Komp. Permata Blok A3">
                        </div>

                        <!-- Kabel Fiber -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-cable me-1' style="color:#3b82f6;"></i>Kabel Fiber
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">Panjang Kabel (km)</label>
                            <input type="number" id="f_length" class="form-control form-control-sm rd-input"
                                   value="1" min="0" step="0.01" placeholder="km">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">
                                Redaman Kabel (dB/km)
                                <span class="rd-hint">OS2 ≈ 0.35 | OM3 ≈ 2.5</span>
                            </label>
                            <input type="number" id="f_cable_loss" class="form-control form-control-sm rd-input"
                                   value="0.35" min="0" step="0.01">
                        </div>

                        <!-- Splitter -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-git-branch me-1' style="color:var(--orange);"></i>Splitter
                            </div>
                        </div>

                        <div class="col-12" id="splitterRows">
                            <!-- rows added dynamically -->
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm" onclick="addSplitter()"
                                    style="border:1px dashed var(--orange);color:var(--orange);background:transparent;font-size:.78rem;padding:.2rem .65rem;">
                                <i class='bx bx-plus me-1'></i>Tambah Splitter
                            </button>
                        </div>

                        <!-- Konektor & Splice -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-link me-1' style="color:#22c55e;"></i>Konektor & Splice
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">
                                Jumlah Konektor
                                <span class="rd-hint">≈ 0.5 dB/buah</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="f_conn_count" class="form-control rd-input"
                                       value="2" min="0" step="1">
                                <span class="input-group-text" style="font-size:.72rem;">×</span>
                                <input type="number" id="f_conn_loss" class="form-control rd-input"
                                       value="0.5" min="0" step="0.01" style="max-width:70px;">
                                <span class="input-group-text" style="font-size:.72rem;">dB</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">
                                Jumlah Splice
                                <span class="rd-hint">≈ 0.1 dB/buah</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="f_splice_count" class="form-control rd-input"
                                       value="2" min="0" step="1">
                                <span class="input-group-text" style="font-size:.72rem;">×</span>
                                <input type="number" id="f_splice_loss" class="form-control rd-input"
                                       value="0.1" min="0" step="0.01" style="max-width:70px;">
                                <span class="input-group-text" style="font-size:.72rem;">dB</span>
                            </div>
                        </div>

                        <!-- Link Budget -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-bar-chart-alt-2 me-1' style="color:#8b5cf6;"></i>Link Budget OLT
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">
                                Tx Power OLT (dBm)
                                <span class="rd-hint">biasanya +5</span>
                            </label>
                            <input type="number" id="f_tx" class="form-control form-control-sm rd-input"
                                   value="5" step="0.1">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">
                                Rx Sensitivity ONT (dBm)
                                <span class="rd-hint">biasanya -27</span>
                            </label>
                            <input type="number" id="f_rx_sens" class="form-control form-control-sm rd-input"
                                   value="-27" step="0.1">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">Safety Margin (dB)</label>
                            <input type="number" id="f_safety" class="form-control form-control-sm rd-input"
                                   value="3" step="0.5" min="0">
                        </div>

                        <!-- Catatan -->
                        <div class="col-12">
                            <label class="form-label-sm">Catatan (opsional)</label>
                            <input type="text" id="f_notes" class="form-control form-control-sm"
                                   placeholder="Lokasi, pelanggan, keterangan lain...">
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-sm rd-calc-btn" onclick="calcFiber()">
                                <i class='bx bx-calculator me-1'></i>Hitung
                            </button>
                            <button class="btn btn-sm" onclick="resetFiber()"
                                    style="border:1px solid #e5e7eb;color:var(--text-muted);font-size:.8rem;padding:.3rem .8rem;border-radius:6px;">
                                <i class='bx bx-reset me-1'></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── WIRELESS FORM ────────────────────────────────────────── -->
                <div id="wirelessPanel" class="rd-panel p-3" style="display:none;">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label-sm">Nama / Label Link</label>
                            <input type="text" id="w_name" class="form-control form-control-sm"
                                   placeholder="cth: PTP Gudang ke Tower A">
                        </div>

                        <!-- Link Parameter -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-signal-5 me-1' style="color:#3b82f6;"></i>Parameter Link
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">Jarak (km)</label>
                            <input type="number" id="w_dist" class="form-control form-control-sm rd-input"
                                   value="1" min="0.01" step="0.01">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">Frekuensi (GHz)</label>
                            <select id="w_freq_sel" class="form-select form-select-sm rd-input" onchange="freqSelectChange()">
                                <option value="2.4">2.4 GHz (WiFi 2.4G)</option>
                                <option value="5.0">5.0 GHz (WiFi 5G)</option>
                                <option value="5.8" selected>5.8 GHz (WiFi/PTP)</option>
                                <option value="10">10 GHz (Microwave)</option>
                                <option value="11">11 GHz (Microwave)</option>
                                <option value="18">18 GHz (Microwave)</option>
                                <option value="24">24 GHz (Microwave)</option>
                                <option value="custom">Custom...</option>
                            </select>
                        </div>
                        <div class="col-sm-6" id="w_freq_custom_wrap" style="display:none;">
                            <label class="form-label-sm">Frekuensi Custom (GHz)</label>
                            <input type="number" id="w_freq_custom" class="form-control form-control-sm rd-input"
                                   value="5.8" min="0.1" step="0.1">
                        </div>

                        <!-- TX Side -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-broadcast me-1' style="color:var(--orange);"></i>Sisi Pengirim (TX)
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">TX Power (dBm)</label>
                            <input type="number" id="w_tx" class="form-control form-control-sm rd-input"
                                   value="23" step="0.5">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">Antena TX Gain (dBi)</label>
                            <input type="number" id="w_ant_tx" class="form-control form-control-sm rd-input"
                                   value="24" step="0.5" min="0">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">
                                Kabel/Konektor TX (dB)
                                <span class="rd-hint">loss</span>
                            </label>
                            <input type="number" id="w_cable_tx" class="form-control form-control-sm rd-input"
                                   value="1" step="0.1" min="0">
                        </div>

                        <!-- RX Side -->
                        <div class="col-12">
                            <div class="rd-section-title">
                                <i class='bx bx-radio-circle me-1' style="color:#22c55e;"></i>Sisi Penerima (RX)
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">Antena RX Gain (dBi)</label>
                            <input type="number" id="w_ant_rx" class="form-control form-control-sm rd-input"
                                   value="24" step="0.5" min="0">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">
                                Kabel/Konektor RX (dB)
                                <span class="rd-hint">loss</span>
                            </label>
                            <input type="number" id="w_cable_rx" class="form-control form-control-sm rd-input"
                                   value="1" step="0.1" min="0">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label-sm">
                                RX Sensitivity (dBm)
                                <span class="rd-hint">threshold</span>
                            </label>
                            <input type="number" id="w_rx_sens" class="form-control form-control-sm rd-input"
                                   value="-75" step="0.5">
                        </div>

                        <!-- Obstacle (opsional) -->
                        <div class="col-sm-6">
                            <label class="form-label-sm">
                                Redaman Obstacle / Rain (dB)
                                <span class="rd-hint">opsional</span>
                            </label>
                            <input type="number" id="w_obstacle" class="form-control form-control-sm rd-input"
                                   value="0" step="0.5" min="0">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label-sm">Safety Margin (dB)</label>
                            <input type="number" id="w_safety" class="form-control form-control-sm rd-input"
                                   value="10" step="0.5" min="0">
                        </div>

                        <!-- Catatan -->
                        <div class="col-12">
                            <label class="form-label-sm">Catatan (opsional)</label>
                            <input type="text" id="w_notes" class="form-control form-control-sm"
                                   placeholder="Lokasi tower, kondisi LOS, vendor perangkat...">
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 d-flex gap-2">
                            <button class="btn btn-sm rd-calc-btn" onclick="calcWireless()">
                                <i class='bx bx-calculator me-1'></i>Hitung
                            </button>
                            <button class="btn btn-sm" onclick="resetWireless()"
                                    style="border:1px solid #e5e7eb;color:var(--text-muted);font-size:.8rem;padding:.3rem .8rem;border-radius:6px;">
                                <i class='bx bx-reset me-1'></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── RESULT PANEL ─────────────────────────────────────────────── -->
        <div id="resultPanel" style="display:none;margin-top:.75rem;"></div>

    </div>

    <!-- ── RIGHT: Reference + History ─────────────────────────────────── -->
    <div class="col-xl-5 col-lg-12">

        <!-- Splitter Loss Reference -->
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="fw-bold mb-2" style="font-size:.85rem;">
                    <i class='bx bx-git-branch me-2' style="color:var(--orange);"></i>Referensi Redaman Splitter
                </div>
                <table class="table table-sm mb-0" style="font-size:.8rem;">
                    <thead>
                        <tr style="background:var(--bg-card-alt,#f8f9fa);">
                            <th>Rasio</th>
                            <th>Redaman Insersi</th>
                            <th>Return Loss</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>1:2</strong></td><td>3.5 dB</td><td>&gt;55 dB</td><td style="color:var(--text-muted);">2 port</td></tr>
                        <tr><td><strong>1:4</strong></td><td>7.5 dB</td><td>&gt;55 dB</td><td style="color:var(--text-muted);">4 port</td></tr>
                        <tr><td><strong>1:8</strong></td><td>10.5 dB</td><td>&gt;50 dB</td><td style="color:var(--text-muted);">8 port</td></tr>
                        <tr><td><strong>1:16</strong></td><td>13.5 dB</td><td>&gt;45 dB</td><td style="color:var(--text-muted);">16 port</td></tr>
                        <tr><td><strong>1:32</strong></td><td>16.5 dB</td><td>&gt;45 dB</td><td style="color:var(--text-muted);">32 port (GPON std)</td></tr>
                        <tr><td><strong>1:64</strong></td><td>19.5 dB</td><td>&gt;40 dB</td><td style="color:var(--text-muted);">64 port</td></tr>
                        <tr><td><strong>1:128</strong></td><td>22.5 dB</td><td>&gt;35 dB</td><td style="color:var(--text-muted);">128 port</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kabel Fiber Reference -->
        <div class="card mb-3">
            <div class="card-body p-3">
                <div class="fw-bold mb-2" style="font-size:.85rem;">
                    <i class='bx bx-cable me-2' style="color:#3b82f6;"></i>Referensi Redaman Kabel Fiber
                </div>
                <table class="table table-sm mb-0" style="font-size:.8rem;">
                    <thead>
                        <tr style="background:var(--bg-card-alt,#f8f9fa);">
                            <th>Tipe</th>
                            <th>Redaman</th>
                            <th>Bandwidth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>OS1 (SM Indoor)</strong></td><td>1.0 dB/km</td><td>Tidak terbatas</td></tr>
                        <tr><td><strong>OS2 (SM Outdoor)</strong></td><td>0.35 dB/km</td><td>Tidak terbatas</td></tr>
                        <tr><td><strong>OM3 (MM 50µm)</strong></td><td>2.5 dB/km</td><td>10G/300m</td></tr>
                        <tr><td><strong>OM4 (MM 50µm)</strong></td><td>2.5 dB/km</td><td>10G/550m</td></tr>
                    </tbody>
                </table>
                <div style="margin-top:.5rem;padding:.4rem .6rem;background:rgba(59,130,246,.07);border-radius:4px;font-size:.72rem;color:#3b82f6;">
                    <i class='bx bx-info-circle me-1'></i>
                    FTTH GPON biasanya pakai <strong>OS2 (0.35 dB/km)</strong> — single mode, outdoor.
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold" style="font-size:.85rem;">
                        <i class='bx bx-history me-2' style="color:var(--orange);"></i>Riwayat Kalkulasi
                    </div>
                    <button onclick="loadHistory()" style="border:none;background:transparent;color:var(--text-muted);font-size:.8rem;cursor:pointer;">
                        <i class='bx bx-refresh'></i>
                    </button>
                </div>
                <div id="historyList">
                    @if($history->isEmpty())
                    <div class="text-center py-3 text-muted" style="font-size:.8rem;">
                        <i class='bx bx-folder-open' style="font-size:1.5rem;display:block;margin-bottom:.3rem;"></i>
                        Belum ada kalkulasi tersimpan
                    </div>
                    @else
                    @foreach($history as $h)
                    <div class="rd-hist-item" data-id="{{ $h->id }}">
                        <div class="d-flex align-items-start justify-content-between">
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $h->name }}
                                </div>
                                <div style="font-size:.68rem;color:var(--text-muted);">
                                    <span class="rd-type-badge {{ $h->type }}">{{ strtoupper($h->type) }}</span>
                                    {{ $h->created_at->format('d/m/Y H:i') }}
                                </div>
                                @if($h->results)
                                <div style="font-size:.7rem;margin-top:.1rem;">
                                    Total Loss: <strong>{{ number_format($h->results['total_loss'] ?? 0, 2) }} dB</strong>
                                    &nbsp;|&nbsp;Margin: <strong style="color:{{ ($h->results['margin'] ?? -99) >= 6 ? '#22c55e' : (($h->results['margin'] ?? -99) >= 0 ? '#f59e0b' : '#ef4444') }};">
                                        {{ number_format($h->results['margin'] ?? 0, 2) }} dB
                                    </strong>
                                </div>
                                @endif
                            </div>
                            <div class="d-flex gap-1 ms-1">
                                <button class="rd-hist-load" title="Load" onclick="loadCalc({{ $h->id }}, {{ json_encode($h->type) }}, {{ json_encode($h->inputs) }}, {{ json_encode($h->results) }})">
                                    <i class='bx bx-upload'></i>
                                </button>
                                <button class="rd-hist-del" title="Hapus" onclick="deleteCalc({{ $h->id }}, this)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* ── Tab ─────────────────────────────────────────────────── */
.rd-tab {
    border: none;
    background: transparent;
    padding: .65rem 1rem;
    font-size: .83rem;
    color: var(--text-muted);
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: color .2s, border-color .2s;
}
.rd-tab.active { color: var(--orange); border-bottom-color: var(--orange); font-weight: 600; }
.rd-tab:hover  { color: var(--orange); }

/* ── Section title ───────────────────────────────────────── */
.rd-section-title {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--text-muted);
    padding: .15rem 0;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    margin-bottom: .1rem;
}

/* ── Form labels ─────────────────────────────────────────── */
.form-label-sm {
    display: block;
    font-size: .75rem;
    font-weight: 600;
    color: var(--text-secondary, #374151);
    margin-bottom: .25rem;
}
.rd-hint {
    font-weight: 400;
    font-size: .68rem;
    color: var(--text-muted);
    margin-left: .3rem;
}

/* ── Calc button ─────────────────────────────────────────── */
.rd-calc-btn {
    background: var(--orange);
    color: #fff;
    font-size: .82rem;
    padding: .35rem 1rem;
    border-radius: 6px;
    border: none;
    font-weight: 600;
}
.rd-calc-btn:hover { opacity: .9; color: #fff; }

/* ── Splitter row ────────────────────────────────────────── */
.splitter-row {
    display: flex;
    gap: .5rem;
    align-items: center;
    margin-bottom: .4rem;
    padding: .35rem .5rem;
    background: var(--bg-card-alt, #f8f9fa);
    border-radius: 5px;
}
.splitter-row select, .splitter-row input { font-size: .78rem; }

/* ── Result Panel ────────────────────────────────────────── */
.rd-result { border-radius: 10px; overflow: hidden; }
.rd-result .result-header {
    padding: .75rem 1rem;
    font-weight: 700;
    font-size: .9rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.rd-result.ok      .result-header { background: rgba(34,197,94,.15);  color: #16a34a; }
.rd-result.warning .result-header { background: rgba(245,158,11,.15); color: #b45309; }
.rd-result.fail    .result-header { background: rgba(239,68,68,.15);  color: #dc2626; }
.rd-result-body { padding: .75rem 1rem; }
.rd-metric { display: flex; justify-content: space-between; padding: .3rem 0; border-bottom: 1px solid var(--border-color,#e5e7eb); font-size: .82rem; }
.rd-metric:last-child { border-bottom: none; }
.rd-metric .lbl { color: var(--text-muted); }
.rd-metric .val { font-weight: 700; }

/* ── History ─────────────────────────────────────────────── */
.rd-hist-item {
    padding: .45rem .5rem;
    border-bottom: 1px solid var(--border-color,#e5e7eb);
    cursor: default;
}
.rd-hist-item:last-child { border-bottom: none; }
.rd-hist-load, .rd-hist-del {
    border: none; background: transparent; cursor: pointer;
    font-size: 1rem; padding: .1rem .2rem; border-radius: 4px;
}
.rd-hist-load { color: #3b82f6; }
.rd-hist-load:hover { background: rgba(59,130,246,.1); }
.rd-hist-del  { color: #ef4444; }
.rd-hist-del:hover  { background: rgba(239,68,68,.1); }
.rd-type-badge { font-size: .6rem; padding: .1rem .3rem; border-radius: 3px; font-weight: 700; margin-right: .3rem; }
.rd-type-badge.fiber    { background: rgba(59,130,246,.15); color: #3b82f6; }
.rd-type-badge.wireless { background: rgba(245,158,11,.15);  color: #b45309; }
</style>

<script>
const SAVE_URL    = '{{ route("admin.redaman.store") }}';
const HISTORY_URL = '{{ route("admin.redaman.history") }}';
const DELETE_URL  = '/admin/redaman/';
const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content || '';

// ─── Splitter reference table ─────────────────────────────────────────────
const SPLITTER_LOSS = { '1:2':3.5, '1:4':7.5, '1:8':10.5, '1:16':13.5, '1:32':16.5, '1:64':19.5, '1:128':22.5 };
let splitterCount = 0;

// ─── Tab switch ───────────────────────────────────────────────────────────
function switchTab(tab) {
    document.getElementById('fiberPanel').style.display   = tab === 'fiber'    ? 'block' : 'none';
    document.getElementById('wirelessPanel').style.display = tab === 'wireless' ? 'block' : 'none';
    document.getElementById('tabFiber').classList.toggle('active',   tab === 'fiber');
    document.getElementById('tabWireless').classList.toggle('active', tab === 'wireless');
    document.getElementById('resultPanel').style.display = 'none';
}

// ─── Splitter row add/remove ──────────────────────────────────────────────
function addSplitter(ratio, qty) {
    splitterCount++;
    const id  = splitterCount;
    const row = document.createElement('div');
    row.className = 'splitter-row';
    row.id = 'splitter-' + id;
    row.innerHTML =
        '<select class="form-select form-select-sm rd-input splitter-ratio" style="flex:0 0 90px;" onchange="calcFiber()">'
        + Object.entries(SPLITTER_LOSS).map(([r, l]) =>
            '<option value="' + l + '"' + (r === (ratio || '1:8') ? ' selected' : '') + '>' + r + ' (' + l + ' dB)</option>'
        ).join('')
        + '</select>'
        + '<label style="font-size:.75rem;color:var(--text-muted);white-space:nowrap;">× Qty</label>'
        + '<input type="number" class="form-control form-control-sm rd-input splitter-qty" value="' + (qty || 1) + '" min="0" step="1" style="max-width:60px;" onchange="calcFiber()">'
        + '<span style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;">buah</span>'
        + '<button type="button" onclick="removeSplitter(' + id + ')" style="border:none;background:transparent;color:#ef4444;font-size:1rem;cursor:pointer;padding:0 .2rem;flex-shrink:0;">'
        + '<i class=\'bx bx-x-circle\'></i></button>';
    document.getElementById('splitterRows').appendChild(row);
}

function removeSplitter(id) {
    const el = document.getElementById('splitter-' + id);
    if (el) el.remove();
    calcFiber();
}

// ─── Frequency select ─────────────────────────────────────────────────────
function freqSelectChange() {
    const sel = document.getElementById('w_freq_sel').value;
    document.getElementById('w_freq_custom_wrap').style.display = sel === 'custom' ? 'block' : 'none';
}

function getFreqGHz() {
    const sel = document.getElementById('w_freq_sel').value;
    return sel === 'custom'
        ? parseFloat(document.getElementById('w_freq_custom').value) || 5.8
        : parseFloat(sel);
}

// ─── Helpers ──────────────────────────────────────────────────────────────
function v(id) { return parseFloat(document.getElementById(id).value) || 0; }
function s(id) { return document.getElementById(id).value.trim(); }

function showResult(html) {
    const panel = document.getElementById('resultPanel');
    panel.innerHTML = html;
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function statusInfo(margin) {
    if (margin >= 6)  return { cls: 'ok',      icon: 'bx-check-circle', label: 'LAYAK — Link OK',             color: '#16a34a' };
    if (margin >= 0)  return { cls: 'warning',  icon: 'bx-error',        label: 'PERINGATAN — Margin Tipis',   color: '#b45309' };
    return                  { cls: 'fail',     icon: 'bx-x-circle',    label: 'TIDAK LAYAK — Link Budget -',  color: '#dc2626' };
}

function buildResultHtml(title, metrics, margin, saveBtn) {
    const st = statusInfo(margin);
    let html = '<div class="card rd-result ' + st.cls + '">'
             + '<div class="result-header"><i class=\'bx ' + st.icon + '\'></i>' + title + ' — ' + st.label + '</div>'
             + '<div class="rd-result-body">';
    metrics.forEach(m => {
        html += '<div class="rd-metric"><span class="lbl">' + m[0] + '</span>'
              + '<span class="val" style="color:' + (m[2] || 'inherit') + ';">' + m[1] + '</span></div>';
    });
    html += '</div>'
          + '<div style="padding:.5rem 1rem;border-top:1px solid var(--border-color,#e5e7eb);">'
          + saveBtn
          + '</div></div>';
    return html;
}

// ─── FIBER CALCULATOR ─────────────────────────────────────────────────────
function calcFiber() {
    const length     = v('f_length');
    const cableLoss  = v('f_cable_loss');
    const connCount  = v('f_conn_count');
    const connLoss   = v('f_conn_loss');
    const spliceCount= v('f_splice_count');
    const spliceLoss = v('f_splice_loss');
    const txPower    = v('f_tx');
    const rxSens     = v('f_rx_sens');
    const safety     = v('f_safety');

    // Splitter loss
    let splitterTotal = 0;
    document.querySelectorAll('.splitter-row').forEach(row => {
        const loss = parseFloat(row.querySelector('.splitter-ratio').value) || 0;
        const qty  = parseFloat(row.querySelector('.splitter-qty').value)   || 0;
        splitterTotal += loss * qty;
    });

    const cableLossTot  = length * cableLoss;
    const connLossTot   = connCount * connLoss;
    const spliceLossTot = spliceCount * spliceLoss;
    const totalLoss     = cableLossTot + splitterTotal + connLossTot + spliceLossTot;
    const rxPower       = txPower - totalLoss;
    const budget        = txPower - rxSens;          // Max allowed loss
    const margin        = budget - totalLoss - safety; // Positive = ok

    // Cache for save
    window._lastCalc = {
        type: 'fiber',
        name: s('f_name') || 'Fiber Calc',
        notes: s('f_notes'),
        inputs: {
            length, cableLoss, connCount, connLoss, spliceCount, spliceLoss,
            txPower, rxSens, safety,
            splitters: [...document.querySelectorAll('.splitter-row')].map(r => ({
                loss: parseFloat(r.querySelector('.splitter-ratio').value),
                qty:  parseFloat(r.querySelector('.splitter-qty').value),
            })),
        },
        results: {
            cable_loss:    round2(cableLossTot),
            splitter_loss: round2(splitterTotal),
            conn_loss:     round2(connLossTot),
            splice_loss:   round2(spliceLossTot),
            total_loss:    round2(totalLoss),
            rx_power:      round2(rxPower),
            link_budget:   round2(budget),
            margin:        round2(margin),
        },
    };

    const html = buildResultHtml(
        '<i class=\'bx bx-cable me-1\'></i>Fiber Optik',
        [
            ['Redaman Kabel (' + length + ' km × ' + cableLoss + ' dB/km)',   fmtdB(cableLossTot), null],
            ['Redaman Splitter',                                                fmtdB(splitterTotal), null],
            ['Redaman Konektor (' + connCount + ' × ' + connLoss + ' dB)',     fmtdB(connLossTot),  null],
            ['Redaman Splice (' + spliceCount + ' × ' + spliceLoss + ' dB)',   fmtdB(spliceLossTot),null],
            ['Total Redaman',                                                   fmtdB(totalLoss),    totalLoss > budget ? '#ef4444' : null],
            ['Link Budget (Tx − Rx Sens)',                                      fmtdB(budget),        null],
            ['Daya Terima Estimasi (Rx Power)',                                 fmtdBm(rxPower),      rxPower < rxSens ? '#ef4444' : '#22c55e'],
            ['Link Margin (setelah safety ' + safety + ' dB)',                  fmtdB(margin),        margin >= 6 ? '#22c55e' : margin >= 0 ? '#f59e0b' : '#ef4444'],
        ],
        margin,
        saveBtnHtml()
    );
    showResult(html);
}

// ─── WIRELESS CALCULATOR ──────────────────────────────────────────────────
function calcWireless() {
    const dist     = v('w_dist');
    const freqGHz  = getFreqGHz();
    const txPow    = v('w_tx');
    const antTx    = v('w_ant_tx');
    const cableTx  = v('w_cable_tx');
    const antRx    = v('w_ant_rx');
    const cableRx  = v('w_cable_rx');
    const rxSens   = v('w_rx_sens');
    const obstacle = v('w_obstacle');
    const safety   = v('w_safety');

    // FSPL = 20·log10(d) + 20·log10(f) + 92.45  (d in km, f in GHz)
    const fspl = 20 * Math.log10(dist) + 20 * Math.log10(freqGHz) + 92.45;

    // Received Power = TxPow + AntTx - CableTx - FSPL + AntRx - CableRx - Obstacle
    const eirp       = txPow + antTx - cableTx;
    const rxPower    = eirp - fspl + antRx - cableRx - obstacle;
    const fadeMargin = rxPower - rxSens - safety;

    window._lastCalc = {
        type: 'wireless',
        name: s('w_name') || 'Wireless Calc',
        notes: s('w_notes'),
        inputs: { dist, freqGHz, txPow, antTx, cableTx, antRx, cableRx, rxSens, obstacle, safety },
        results: {
            fspl:        round2(fspl),
            eirp:        round2(eirp),
            rx_power:    round2(rxPower),
            total_loss:  round2(fspl + cableTx + cableRx + obstacle),
            margin:      round2(fadeMargin),
        },
    };

    const html = buildResultHtml(
        '<i class=\'bx bx-signal-5 me-1\'></i>Wireless / RF',
        [
            ['Frekuensi',                         freqGHz + ' GHz',       null],
            ['Jarak',                              dist + ' km',           null],
            ['Free Space Path Loss (FSPL)',         fmtdB(fspl),            null],
            ['EIRP (Tx + Ant Gain - Cable)',        fmtdBm(eirp),           null],
            ['Redaman Obstacle / Rain',             fmtdB(obstacle),        null],
            ['Daya Terima Estimasi (Rx Power)',     fmtdBm(rxPower),        rxPower < rxSens ? '#ef4444' : '#22c55e'],
            ['Rx Sensitivity Threshold',            fmtdBm(rxSens),         null],
            ['Fade Margin (setelah safety ' + safety + ' dB)', fmtdB(fadeMargin), fadeMargin >= 6 ? '#22c55e' : fadeMargin >= 0 ? '#f59e0b' : '#ef4444'],
        ],
        fadeMargin,
        saveBtnHtml()
    );
    showResult(html);
}

// ─── Helpers ──────────────────────────────────────────────────────────────
function round2(n) { return Math.round(n * 100) / 100; }
function fmtdB(n)  { return n.toFixed(2) + ' dB'; }
function fmtdBm(n) { return n.toFixed(2) + ' dBm'; }

function saveBtnHtml() {
    return '<div class="d-flex gap-2 align-items-center">'
         + '<button class="btn btn-sm rd-calc-btn" onclick="saveCalc()" style="padding:.25rem .75rem;font-size:.78rem;">'
         + '<i class=\'bx bx-save me-1\'></i>Simpan ke History</button>'
         + '<span id="saveMsg" style="font-size:.75rem;"></span>'
         + '</div>';
}

// ─── Save ─────────────────────────────────────────────────────────────────
async function saveCalc() {
    if (!window._lastCalc) return;
    const msgEl = document.getElementById('saveMsg');
    msgEl.textContent = 'Menyimpan...';
    msgEl.style.color = 'var(--text-muted)';

    const payload = {
        type:    window._lastCalc.type,
        name:    window._lastCalc.name || 'Untitled',
        inputs:  window._lastCalc.inputs,
        results: window._lastCalc.results,
        notes:   window._lastCalc.notes || null,
    };

    try {
        const res  = await fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const json = await res.json();
        if (json.success) {
            msgEl.textContent = '✓ Disimpan!';
            msgEl.style.color = '#22c55e';
            loadHistory();
        } else {
            msgEl.textContent = 'Gagal: ' + (json.message || 'Error');
            msgEl.style.color = '#ef4444';
        }
    } catch (e) {
        msgEl.textContent = 'Error: ' + e.message;
        msgEl.style.color = '#ef4444';
    }
}

// ─── Load History ─────────────────────────────────────────────────────────
async function loadHistory() {
    try {
        const res  = await fetch(HISTORY_URL);
        const json = await res.json();
        renderHistory(json.data || []);
    } catch (e) { /* silent */ }
}

function renderHistory(items) {
    const el = document.getElementById('historyList');
    if (!items.length) {
        el.innerHTML = '<div class="text-center py-3 text-muted" style="font-size:.8rem;">'
            + '<i class=\'bx bx-folder-open\' style="font-size:1.5rem;display:block;margin-bottom:.3rem;"></i>'
            + 'Belum ada kalkulasi tersimpan</div>';
        return;
    }
    el.innerHTML = items.map(h => {
        const m = h.results?.margin ?? null;
        const mColor = m === null ? '#9ca3af' : m >= 6 ? '#22c55e' : m >= 0 ? '#f59e0b' : '#ef4444';
        return '<div class="rd-hist-item">'
            + '<div class="d-flex align-items-start justify-content-between">'
            + '<div style="flex:1;min-width:0;">'
            + '<div style="font-weight:600;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + h.name + '</div>'
            + '<div style="font-size:.68rem;color:var(--text-muted);">'
            + '<span class="rd-type-badge ' + h.type + '">' + h.type.toUpperCase() + '</span>'
            + h.created_at + '</div>'
            + '<div style="font-size:.7rem;margin-top:.1rem;">'
            + 'Loss: <strong>' + (h.results?.total_loss ?? '—') + ' dB</strong>'
            + ' | Margin: <strong style="color:' + mColor + ';">' + (m !== null ? m.toFixed(2) : '—') + ' dB</strong>'
            + '</div>'
            + '</div>'
            + '<div class="d-flex gap-1 ms-1">'
            + '<button class="rd-hist-load" title="Load" onclick=\'loadCalc(' + h.id + ',"' + h.type + '",' + JSON.stringify(h.inputs) + ',' + JSON.stringify(h.results) + ')\'><i class=\'bx bx-upload\'></i></button>'
            + '<button class="rd-hist-del" title="Hapus" onclick="deleteCalc(' + h.id + ', this)"><i class=\'bx bx-trash\'></i></button>'
            + '</div>'
            + '</div></div>';
    }).join('');
}

// ─── Load saved calc into form ────────────────────────────────────────────
function loadCalc(id, type, inputs, results) {
    switchTab(type);
    if (type === 'fiber') {
        document.getElementById('f_length').value     = inputs.length ?? 1;
        document.getElementById('f_cable_loss').value = inputs.cableLoss ?? 0.35;
        document.getElementById('f_conn_count').value  = inputs.connCount ?? 2;
        document.getElementById('f_conn_loss').value   = inputs.connLoss ?? 0.5;
        document.getElementById('f_splice_count').value= inputs.spliceCount ?? 2;
        document.getElementById('f_splice_loss').value = inputs.spliceLoss ?? 0.1;
        document.getElementById('f_tx').value         = inputs.txPower ?? 5;
        document.getElementById('f_rx_sens').value    = inputs.rxSens ?? -27;
        document.getElementById('f_safety').value     = inputs.safety ?? 3;

        // Restore splitters
        document.getElementById('splitterRows').innerHTML = '';
        splitterCount = 0;
        (inputs.splitters || []).forEach(sp => {
            // find matching option label
            const ratio = Object.entries(SPLITTER_LOSS).find(([,l]) => l === sp.loss)?.[0] || '1:8';
            addSplitter(ratio, sp.qty);
        });
        calcFiber();
    } else {
        document.getElementById('w_dist').value    = inputs.dist ?? 1;
        document.getElementById('w_tx').value      = inputs.txPow ?? 23;
        document.getElementById('w_ant_tx').value  = inputs.antTx ?? 24;
        document.getElementById('w_cable_tx').value= inputs.cableTx ?? 1;
        document.getElementById('w_ant_rx').value  = inputs.antRx ?? 24;
        document.getElementById('w_cable_rx').value= inputs.cableRx ?? 1;
        document.getElementById('w_rx_sens').value = inputs.rxSens ?? -75;
        document.getElementById('w_obstacle').value= inputs.obstacle ?? 0;
        document.getElementById('w_safety').value  = inputs.safety ?? 10;
        // Restore frequency
        const fSel = document.getElementById('w_freq_sel');
        const fGHz = String(inputs.freqGHz ?? 5.8);
        let found = false;
        for (const opt of fSel.options) { if (opt.value === fGHz) { opt.selected = true; found = true; break; } }
        if (!found) { fSel.value = 'custom'; document.getElementById('w_freq_custom').value = fGHz; }
        freqSelectChange();
        calcWireless();
    }
}

// ─── Delete history ───────────────────────────────────────────────────────
async function deleteCalc(id, btn) {
    if (!confirm('Hapus kalkulasi ini?')) return;
    try {
        await fetch(DELETE_URL + id, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        loadHistory();
    } catch (e) { alert('Gagal hapus: ' + e.message); }
}

// ─── Reset ────────────────────────────────────────────────────────────────
function resetFiber() {
    ['f_name','f_notes'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f_length').value      = 1;
    document.getElementById('f_cable_loss').value  = 0.35;
    document.getElementById('f_conn_count').value  = 2;
    document.getElementById('f_conn_loss').value   = 0.5;
    document.getElementById('f_splice_count').value= 2;
    document.getElementById('f_splice_loss').value = 0.1;
    document.getElementById('f_tx').value          = 5;
    document.getElementById('f_rx_sens').value     = -27;
    document.getElementById('f_safety').value      = 3;
    document.getElementById('splitterRows').innerHTML = '';
    splitterCount = 0;
    document.getElementById('resultPanel').style.display = 'none';
}

function resetWireless() {
    ['w_name','w_notes'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('w_dist').value     = 1;
    document.getElementById('w_freq_sel').value = '5.8';
    document.getElementById('w_tx').value       = 23;
    document.getElementById('w_ant_tx').value   = 24;
    document.getElementById('w_cable_tx').value = 1;
    document.getElementById('w_ant_rx').value   = 24;
    document.getElementById('w_cable_rx').value = 1;
    document.getElementById('w_rx_sens').value  = -75;
    document.getElementById('w_obstacle').value = 0;
    document.getElementById('w_safety').value   = 10;
    freqSelectChange();
    document.getElementById('resultPanel').style.display = 'none';
}

// ─── Auto-recalculate on input change ────────────────────────────────────
document.querySelectorAll('.rd-input').forEach(el => {
    el.addEventListener('change', () => {
        const fiberVisible = document.getElementById('fiberPanel').style.display !== 'none';
        if (fiberVisible) { if(document.getElementById('resultPanel').style.display !== 'none') calcFiber(); }
        else              { if(document.getElementById('resultPanel').style.display !== 'none') calcWireless(); }
    });
});

// Boot: add 1 default splitter for fiber
addSplitter('1:8', 1);
</script>
@endsection
