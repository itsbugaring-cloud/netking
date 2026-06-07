@extends('layouts.app')
@section('title', isset($unit) ? 'Edit Unit: ' . $unit->serial_number : 'Tambah Unit SN')

@section('content')
<div class="ms-page inv-unit-form-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker">Inventaris / Unit</div>
      <h1 class="ms-page-title">{{ isset($unit) ? 'Edit Unit' : 'Tambah Unit SN' }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.units.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>



  <form action="{{ isset($unit) ? route('admin.inventory.units.update', $unit) : route('admin.inventory.units.store') }}" method="POST">
    @csrf
    @if(isset($unit)) @method('PUT') @endif

    <div class="ms-panel">
      <div class="ms-panel-head">
        <h5 class="ms-panel-title">Informasi Unit</h5>
      </div>
      <div class="ms-panel-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Master Barang <span class="text-danger">*</span></label>
              <select name="master_barang_id" class="form-select @error('master_barang_id') is-invalid @enderror" required>
                <option value="">-- Pilih Barang --</option>
                @foreach($master_list->groupBy(fn($m) => $m->kategori->nama ?? 'Lainnya') as $kat => $items)
                  <optgroup label="{{ $kat }}">
                    @foreach($items as $mb)
                      <option value="{{ $mb->id }}"
                        {{ old('master_barang_id', $unit->master_barang_id ?? '') == $mb->id ? 'selected' : '' }}>
                        {{ $mb->merek }} {{ $mb->tipe }}
                      </option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
              @error('master_barang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Serial Number <span class="text-danger">*</span></label>
              <input type="text" name="serial_number"
                     value="{{ old('serial_number', $unit->serial_number ?? '') }}"
                     class="form-control @error('serial_number') is-invalid @enderror"
                     placeholder="Contoh: HWTC1234567890"
                     {{ isset($unit) ? 'readonly' : '' }}>
              @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">MAC Address</label>
              <input type="text" name="mac_address"
                     value="{{ old('mac_address', $unit->mac_address ?? '') }}"
                     class="form-control @error('mac_address') is-invalid @enderror"
                     placeholder="Contoh: AA:BB:CC:DD:EE:FF">
              @error('mac_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Nilai Aset (Rp)</label>
              <input type="number" name="nilai_aset" min="0" step="1000"
                     value="{{ old('nilai_aset', $unit->nilai_aset ?? '') }}"
                     class="form-control @error('nilai_aset') is-invalid @enderror"
                     placeholder="0">
              @error('nilai_aset')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Lokasi <span class="text-danger">*</span></label>
              <select name="lokasi_id" class="form-select @error('lokasi_id') is-invalid @enderror" required>
                <option value="">-- Pilih Lokasi --</option>
                @foreach($lokasi_list as $loc)
                  <option value="{{ $loc->id }}"
                    {{ old('lokasi_id', $unit->lokasi_id ?? '') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->nama_lokasi }}
                  </option>
                @endforeach
              </select>
              @error('lokasi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach($status_options as $val => $label)
                  <option value="{{ $val }}"
                    {{ old('status', $unit->status ?? 'gudang') === $val ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
              @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Penanggung Jawab</label>
              <select name="penanggung_jawab_user_id" class="form-select @error('penanggung_jawab_user_id') is-invalid @enderror">
                <option value="">-- Pilih Partner --</option>
                @foreach($partner_list as $partner)
                  <option value="{{ $partner->id }}"
                    {{ old('penanggung_jawab_user_id', $unit->penanggung_jawab_user_id ?? '') == $partner->id ? 'selected' : '' }}>
                    {{ $partner->name }}
                  </option>
                @endforeach
              </select>
              <input type="text" name="penanggung_jawab"
                     value="{{ old('penanggung_jawab', $unit->penanggung_jawab ?? '') }}"
                     class="form-control mt-1 @error('penanggung_jawab') is-invalid @enderror"
                     placeholder="Atau nama manual (opsional)">
              @error('penanggung_jawab_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan" rows="2"
                        class="form-control @error('catatan') is-invalid @enderror"
                        placeholder="Keterangan tambahan...">{{ old('catatan', $unit->catatan ?? '') }}</textarea>
              @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>
      <div class="ms-panel-footer d-flex gap-2">
        <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan</button>
        <a href="{{ route('admin.inventory.units.index') }}" class="ms-btn-ghost">Batal</a>
      </div>
    </div>
  </form>
</div>
@endsection
