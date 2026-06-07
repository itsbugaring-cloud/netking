@extends('layouts.app')
@section('title', 'Haspel: ' . $invKabel->id_haspel)

@section('content')
<div class="ms-page inv-kabel-show-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-transfer'></i> Inventaris / Kabel</div>
      <h1 class="ms-page-title">Haspel {{ $invKabel->id_haspel }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.kabel.edit', $invKabel) }}" class="ms-btn-secondary">
        <i class='bx bx-edit'></i> Ubah
      </a>
      <a href="{{ route('admin.inventory.kabel.index') }}" class="ms-btn-ghost">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>



  @php
    $panjangAwal = $invKabel->panjang_awal ?? 0;
    $sisaMeter   = $invKabel->sisa_meter ?? $panjangAwal;
    $terpakai    = $panjangAwal - $sisaMeter;
    $pct         = $panjangAwal > 0 ? round(($terpakai / $panjangAwal) * 100) : 0;
    $pctColor    = $pct >= 80 ? 'var(--red,#ef4444)' : ($pct >= 50 ? 'var(--orange,#f59e0b)' : 'var(--green)');
  @endphp

  <div class="row g-3">
    <div class="col-md-7">
      {{-- Detail Panel --}}
      <div class="ms-panel mb-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title">Detail Haspel</h5>
        </div>
        <div class="ms-panel-body">
          <div class="ms-detail-grid">
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-barcode'></i></div>
              <div class="ms-detail-label">ID Haspel</div>
              <div class="ms-detail-value">
                <code style="font-size:0.9rem;background:var(--surface-2);padding:2px 8px;border-radius:6px;border:1px solid var(--border)">
                  {{ $invKabel->id_haspel }}
                </code>
              </div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-transfer'></i></div>
              <div class="ms-detail-label">Jenis Kabel</div>
              <div class="ms-detail-value">{{ $invKabel->masterBarang->merek ?? '' }} {{ $invKabel->masterBarang->tipe ?? $invKabel->jenis_kabel ?? '-' }}</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-ruler'></i></div>
              <div class="ms-detail-label">Panjang Awal</div>
              <div class="ms-detail-value">{{ number_format($panjangAwal, 1) }} m</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-check-circle'></i></div>
              <div class="ms-detail-label">Sisa</div>
              <div class="ms-detail-value">{{ number_format($sisaMeter, 1) }} m</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-scissors'></i></div>
              <div class="ms-detail-label">Terpakai</div>
              <div class="ms-detail-value">{{ number_format($terpakai, 1) }} m ({{ $pct }}%)</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-dollar'></i></div>
              <div class="ms-detail-label">Nilai/m</div>
              <div class="ms-detail-value">
                @if($invKabel->nilai_per_meter)
                  Rp {{ number_format($invKabel->nilai_per_meter, 0, ',', '.') }}
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-map-pin'></i></div>
              <div class="ms-detail-label">Lokasi</div>
              <div class="ms-detail-value">{{ $invKabel->lokasi->nama_lokasi ?? '-' }}</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-user'></i></div>
              <div class="ms-detail-label">Penanggung Jawab</div>
              <div class="ms-detail-value">{{ $invKabel->penanggung_jawab ?? '-' }}</div>
            </div>
          </div>

          {{-- Progress bar --}}
          <div class="mt-3">
            <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;color:var(--txt-3)">
              <span>Terpakai: {{ number_format($terpakai, 1) }} m</span>
              <span>Sisa: {{ number_format($sisaMeter, 1) }} m</span>
            </div>
            <div style="height:10px;border-radius:999px;background:var(--border);overflow:hidden;">
              <div style="width:{{ $pct }}%;height:100%;background:{{ $pctColor }};border-radius:999px;"></div>
            </div>
          </div>
        </div>
      </div>

      {{-- Log --}}
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title">Riwayat Pemotongan</h5>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table table-flat mb-0">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Tipe</th>
                  <th class="text-end">Panjang (m)</th>
                  <th>Pelaku</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($logs as $log)
                <tr>
                  <td style="white-space:nowrap;color:var(--txt-3)">
                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                  </td>
                  <td>
                    @if($log->tipe === 'potong_kabel')
                      <span class="badge-status badge-pending">Potong</span>
                    @elseif($log->tipe === 'masuk_baru')
                      <span class="badge-status badge-active">Masuk</span>
                    @else
                      <span class="badge-status badge-inactive">{{ $log->tipe }}</span>
                    @endif
                  </td>
                  <td class="text-end">{{ $log->qty ? number_format($log->qty, 1) : '-' }}</td>
                  <td style="font-size:0.82rem">{{ $log->user->name ?? $log->pelaku ?? '-' }}</td>
                  <td style="font-size:0.82rem;color:var(--txt-3)">{{ $log->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5">
                  <div class="empty-state">
                    <div class="empty-state-icon"><i class='bx bx-history'></i></div>
                    <div class="empty-state-title">Belum ada riwayat</div>
                  </div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-scissors'></i> Potong Kabel</h5>
        </div>
        <div class="ms-panel-body">
          <form action="{{ route('admin.inventory.kabel.potong', $invKabel) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Panjang Potong (m) <span class="text-danger">*</span></label>
              <input type="number" name="panjang_potong" id="panjang_potong"
                     min="0.1" step="0.1" max="{{ $sisaMeter }}"
                     class="form-control @error('panjang_potong') is-invalid @enderror"
                     placeholder="0.0" required
                     oninput="updateSisaPreview(this.value)">
              @error('panjang_potong')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div id="sisa-preview" class="form-text" style="display:none;font-weight:560;color:var(--nk-info)"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Keterangan</label>
              <input type="text" name="keterangan"
                     class="form-control @error('keterangan') is-invalid @enderror"
                     placeholder="Lokasi pemasangan / kegunaan...">
              @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="alert" style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:10px 14px;font-size:0.84rem">
              <strong>Sisa saat ini:</strong> {{ number_format($sisaMeter, 1) }} m
            </div>
            <button type="submit" class="ms-btn w-100 mt-2"
                    onclick="return confirm('Potong kabel sejumlah meter yang ditentukan?')">
              <i class='bx bx-scissors'></i> Potong Kabel
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function updateSisaPreview(val) {
  const sisa = {{ $sisaMeter }};
  const potong = parseFloat(val) || 0;
  const preview = document.getElementById('sisa-preview');
  if (potong > 0 && potong <= sisa) {
    preview.style.display = 'block';
    preview.textContent = 'Sisa setelah dipotong: ' + (sisa - potong).toFixed(1) + ' m';
  } else {
    preview.style.display = 'none';
  }
}
</script>
@endsection
