@extends('layouts.app')
@section('title', 'Stok Qty (Non-SN)')

@section('content')
<div class="ms-page nk-list-page inv-qty-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-box'></i> Inventaris</div>
      <h1 class="ms-page-title">Stok Qty</h1>
    </div>
  </div>



  <div class="row g-3">
    <div class="col-md-8">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <div>
            <h5 class="ms-panel-title">Daftar Stok Qty</h5>
            <div class="ms-panel-subtitle">Barang tanpa serial number (aksesori, konektor, dll)</div>
          </div>
          <div class="ms-toolbar-right">
            <span class="ms-chip"><i class='bx bx-data'></i> {{ $stocks->total() }} item</span>
          </div>
        </div>
        <div class="ms-table-shell">
          <div class="table-responsive">
            <table class="table table-flat mb-0">
              <thead>
                <tr>
                  <th>Barang</th>
                  <th>Lokasi</th>
                  <th class="text-end">Jumlah</th>
                  <th class="text-end">Harga Satuan</th>
                  <th class="text-end">Total</th>
                  <th style="width:60px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($stocks as $stock)
                <tr>
                  <td>
                    <div style="font-weight:500;">{{ $stock->masterBarang->merek ?? '' }} {{ $stock->masterBarang->tipe ?? '-' }}</div>
                    <div style="font-size:.78rem;color:var(--txt-3)">{{ $stock->masterBarang->kategori->nama ?? '' }}</div>
                  </td>
                  <td>{{ $stock->lokasi->nama_lokasi ?? '-' }}</td>
                  <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end align-items-center">
                      <form action="{{ route('admin.inventory.qty.adjust', $stock) }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="action" value="minus">
                        <button type="submit" class="nk-action-btn delete" style="width:26px;height:26px;"
                                onclick="return confirm('Kurangi 1 unit?')" title="Kurangi">
                          <i class='bx bx-minus'></i>
                        </button>
                      </form>
                      <span style="min-width:40px;text-align:center;font-weight:600">{{ number_format($stock->jumlah) }}</span>
                      <form action="{{ route('admin.inventory.qty.adjust', $stock) }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="action" value="plus">
                        <button type="submit" class="nk-action-btn pay" style="width:26px;height:26px;" title="Tambah">
                          <i class='bx bx-plus'></i>
                        </button>
                      </form>
                    </div>
                  </td>
                  <td class="text-end">
                    @if($stock->harga_satuan)
                      Rp {{ number_format($stock->harga_satuan, 0, ',', '.') }}
                    @else
                      <span style="color:var(--txt-3)">-</span>
                    @endif
                  </td>
                  <td class="text-end">
                    @if($stock->harga_satuan)
                      Rp {{ number_format($stock->jumlah * $stock->harga_satuan, 0, ',', '.') }}
                    @else
                      <span style="color:var(--txt-3)">-</span>
                    @endif
                  </td>
                  <td>
                    <form action="{{ route('admin.inventory.qty.destroy', $stock) }}" method="POST" class="m-0"
                          onsubmit="return confirm('Hapus stok ini?')">
                      @csrf @method('DELETE')
                      <button class="nk-action-btn delete" title="Hapus">
                        <i class='bx bx-trash'></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6">
                  <div class="empty-state">
                    <div class="empty-state-icon"><i class='bx bx-box'></i></div>
                    <div class="empty-state-title">Belum ada stok qty</div>
                    <div class="empty-state-desc">Tambahkan stok pertama melalui form di samping</div>
                  </div>
                </td></tr>
                @endforelse
              </tbody>
              @if($stocks->count() > 0 && $total_nilai_qty)
              <tfoot>
                <tr>
                  <td colspan="4" class="text-end" style="font-weight:600;">Total Nilai:</td>
                  <td class="text-end" style="font-weight:700;color:var(--green);">
                    Rp {{ number_format($total_nilai_qty, 0, ',', '.') }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
        </div>

        @if($stocks->hasPages())
        <div class="ms-panel-footer">{{ $stocks->withQueryString()->links() }}</div>
        @endif
      </div>
    </div>

    <div class="col-md-4">
      <div class="ms-panel">
        <div class="ms-panel-head">
          <h5 class="ms-panel-title"><i class='bx bx-plus-circle'></i> Tambah Stok Baru</h5>
        </div>
        <div class="ms-panel-body">
          <form action="{{ route('admin.inventory.qty.store') }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Barang <span class="text-danger">*</span></label>
              <select name="master_barang_id" class="form-select @error('master_barang_id') is-invalid @enderror" required>
                <option value="">-- Pilih Barang --</option>
                @foreach($master_list as $mb)
                  <option value="{{ $mb->id }}" {{ old('master_barang_id') == $mb->id ? 'selected' : '' }}>
                    {{ $mb->merek }} {{ $mb->tipe }}
                  </option>
                @endforeach
              </select>
              @error('master_barang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Lokasi <span class="text-danger">*</span></label>
              <select name="lokasi_id" class="form-select @error('lokasi_id') is-invalid @enderror" required>
                <option value="">-- Pilih Lokasi --</option>
                @foreach($lokasi_list as $loc)
                  <option value="{{ $loc->id }}" {{ old('lokasi_id') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->nama_lokasi }}
                  </option>
                @endforeach
              </select>
              @error('lokasi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Jumlah <span class="text-danger">*</span></label>
              <input type="number" name="jumlah" min="1" step="1"
                     value="{{ old('jumlah', 1) }}"
                     class="form-control @error('jumlah') is-invalid @enderror" required>
              @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Harga Satuan (Rp)</label>
              <input type="number" name="harga_satuan" min="0" step="100"
                     value="{{ old('harga_satuan') }}"
                     class="form-control @error('harga_satuan') is-invalid @enderror"
                     placeholder="0">
              @error('harga_satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <input type="text" name="catatan"
                     value="{{ old('catatan') }}"
                     class="form-control @error('catatan') is-invalid @enderror"
                     placeholder="Keterangan...">
              @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="ms-btn w-100">
              <i class='bx bx-plus'></i> Tambah Stok
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
