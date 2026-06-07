@extends('layouts.app')
@section('title', 'Unit: ' . $invUnit->serial_number)

@section('content')
<div class="ms-page inv-unit-show-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-chip'></i> Inventaris / Unit</div>
      <h1 class="ms-page-title">{{ $invUnit->masterBarang->merek ?? '' }} {{ $invUnit->masterBarang->tipe ?? '' }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.units.edit', $invUnit) }}" class="ms-btn-secondary">
        <i class='bx bx-edit'></i> Ubah
      </a>
      <a href="{{ route('admin.inventory.units.index') }}" class="ms-btn-ghost">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>



  <div class="row g-3">
    <div class="col-md-8">
      {{-- Detail Panel --}}
      <div class="ms-panel mb-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title">Detail Unit</h5>
          <div class="ms-toolbar-right">
            @php $st = $invUnit->status ?? ''; @endphp
            @if($st === 'gudang')
              <span class="badge-status badge-active">Gudang</span>
            @elseif($st === 'terpasang')
              <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Terpasang</span>
            @elseif($st === 'dibawa_teknisi')
              <span class="badge-status badge-pending">Teknisi</span>
            @elseif($st === 'rusak')
              <span class="badge-status badge-inactive" style="background:color-mix(in srgb,var(--red) 12%,var(--surface));color:var(--red);border-color:color-mix(in srgb,var(--red) 25%,var(--border));">Rusak</span>
            @elseif($st === 'rma')
              <span class="badge-status badge-inactive" style="background:color-mix(in srgb,var(--red) 12%,var(--surface));color:var(--red);border-color:color-mix(in srgb,var(--red) 25%,var(--border));">RMA</span>
            @elseif($st === 'terjual')
              <span class="badge-status badge-inactive">Terjual</span>
            @elseif($st === 'hilang')
              <span class="badge-status badge-inactive" style="background:color-mix(in srgb,var(--red) 12%,var(--surface));color:var(--red);border-color:color-mix(in srgb,var(--red) 25%,var(--border));">Hilang</span>
            @else
              <span class="badge-status badge-inactive">{{ $st }}</span>
            @endif
          </div>
        </div>
        <div class="ms-panel-body">
          <div class="ms-detail-grid">
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-chip'></i></div>
              <div class="ms-detail-label">Barang</div>
              <div class="ms-detail-value">{{ $invUnit->masterBarang->merek ?? '-' }} {{ $invUnit->masterBarang->tipe ?? '' }}</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-category'></i></div>
              <div class="ms-detail-label">Kategori</div>
              <div class="ms-detail-value">{{ $invUnit->masterBarang->kategori->nama ?? '-' }}</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-barcode'></i></div>
              <div class="ms-detail-label">Serial Number</div>
              <div class="ms-detail-value">
                <code style="font-size:0.9rem;background:var(--surface-2);padding:2px 8px;border-radius:6px;border:1px solid var(--border)">
                  {{ $invUnit->serial_number }}
                </code>
              </div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-wifi'></i></div>
              <div class="ms-detail-label">MAC Address</div>
              <div class="ms-detail-value">
                @if($invUnit->mac_address)
                  <code style="font-size:0.9rem;background:var(--surface-2);padding:2px 8px;border-radius:6px;border:1px solid var(--border)">
                    {{ $invUnit->mac_address }}
                  </code>
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-dollar'></i></div>
              <div class="ms-detail-label">Nilai Aset</div>
              <div class="ms-detail-value">
                @if($invUnit->nilai_aset)
                  Rp {{ number_format($invUnit->nilai_aset, 0, ',', '.') }}
                @else
                  <span style="color:var(--txt-3)">-</span>
                @endif
              </div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-map-pin'></i></div>
              <div class="ms-detail-label">Lokasi</div>
              <div class="ms-detail-value">{{ $invUnit->lokasi->nama_lokasi ?? '-' }}</div>
            </div>
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-user'></i></div>
              <div class="ms-detail-label">Penanggung Jawab</div>
              <div class="ms-detail-value">{{ $invUnit->penanggung_jawab ?? '-' }}</div>
            </div>
            @if($invUnit->catatan)
            <div class="ms-detail-row">
              <div class="ms-detail-icon"><i class='bx bx-note'></i></div>
              <div class="ms-detail-label">Catatan</div>
              <div class="ms-detail-value">{{ $invUnit->catatan }}</div>
            </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Log History --}}
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title">Riwayat Aktivitas</h5>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table table-flat mb-0">
              <thead>
                <tr>
                  <th>Waktu</th>
                  <th>Tipe</th>
                  <th>Dari → Ke</th>
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
                    @php $tipe = $log->tipe ?? ''; @endphp
                    @if($tipe === 'masuk_baru')
                      <span class="badge-status badge-active">Masuk</span>
                    @elseif($tipe === 'mutasi')
                      <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Mutasi</span>
                    @elseif($tipe === 'potong_kabel')
                      <span class="badge-status badge-pending">Potong</span>
                    @elseif($tipe === 'pasang')
                      <span class="badge-status badge-active" style="background:color-mix(in srgb,var(--blue) 12%,var(--surface));color:var(--blue);border-color:color-mix(in srgb,var(--blue) 25%,var(--border));">Pasang</span>
                    @elseif($tipe === 'retur')
                      <span class="badge-status badge-inactive">Retur</span>
                    @elseif($tipe === 'barang_keluar')
                      <span class="badge-status badge-inactive">Keluar</span>
                    @elseif($tipe === 'penyesuaian')
                      <span class="badge-status badge-inactive">Sesuai</span>
                    @else
                      <span class="badge-status badge-inactive">{{ $tipe }}</span>
                    @endif
                  </td>
                  <td style="font-size:0.82rem">
                    {{ $log->dari ?? '-' }}
                    @if($log->ke) → {{ $log->ke }} @endif
                  </td>
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

    <div class="col-md-4">
      {{-- Mutasi Form --}}
      <div class="ms-panel mb-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-transfer'></i> Mutasi Lokasi</h5>
        </div>
        <div class="ms-panel-body">
          <form action="{{ route('admin.inventory.units.mutasi', $invUnit) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Lokasi Tujuan <span class="text-danger">*</span></label>
              <select name="lokasi_id" class="form-select form-select-sm" required>
                <option value="">-- Pilih Lokasi --</option>
                @foreach($lokasi_list as $loc)
                  <option value="{{ $loc->id }}" {{ $loc->id === $invUnit->lokasi_id ? 'disabled style=color:var(--txt-3)' : '' }}>
                    {{ $loc->nama_lokasi }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Keterangan</label>
              <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Alasan mutasi...">
            </div>
            <button type="submit" class="ms-btn-secondary w-100">
              <i class='bx bx-transfer'></i> Mutasi
            </button>
          </form>
        </div>
      </div>

      {{-- Pasang Form --}}
      <div class="ms-panel mb-3">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-plug'></i> Pasang ke Pelanggan</h5>
        </div>
        <div class="ms-panel-body">
          <form action="{{ route('admin.inventory.units.pasang', $invUnit) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Penanggung Jawab</label>
              <input type="text" name="penanggung_jawab" class="form-control form-control-sm"
                     value="{{ $invUnit->penanggung_jawab ?? '' }}"
                     placeholder="Nama teknisi / pelanggan">
            </div>
            <div class="mb-3">
              <label class="form-label">Keterangan</label>
              <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Alamat / info pemasangan...">
            </div>
            <button type="submit" class="ms-btn w-100">
              <i class='bx bx-plug'></i> Pasang
            </button>
          </form>
        </div>
      </div>

      {{-- Retur Form --}}
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-undo'></i> Retur ke Gudang</h5>
        </div>
        <div class="ms-panel-body">
          <form action="{{ route('admin.inventory.units.retur', $invUnit) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Kondisi</label>
              <select name="kondisi" class="form-select form-select-sm">
                <option value="baik">Baik</option>
                <option value="rusak">Rusak</option>
                <option value="rma">RMA</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Keterangan</label>
              <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Keterangan retur...">
            </div>
            <button type="submit" class="ms-btn-secondary w-100"
                    onclick="return confirm('Retur unit ini ke gudang?')">
              <i class='bx bx-undo'></i> Retur
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
