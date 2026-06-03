@extends('layouts.app')
@section('title', isset($invKabel) ? 'Edit Kabel: ' . $invKabel->id_haspel : 'Tambah Kabel')

@section('content')
<div class="ms-page inv-kabel-form-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker">Inventaris / Kabel Haspel</div>
      <h1 class="ms-page-title">{{ isset($invKabel) ? 'Edit Haspel: ' . $invKabel->id_haspel : 'Tambah Kabel Haspel' }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.kabel.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <form action="{{ isset($invKabel) ? route('admin.inventory.kabel.update', $invKabel) : route('admin.inventory.kabel.store') }}" method="POST">
    @csrf
    @if(isset($invKabel)) @method('PUT') @endif

    <div class="ms-panel">
      <div class="ms-panel-head">
        <h5 class="ms-panel-title">Informasi Kabel Haspel</h5>
      </div>
      <div class="ms-panel-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Jenis Kabel (Master Barang) <span class="text-danger">*</span></label>
              <select name="master_barang_id" class="form-select @error('master_barang_id') is-invalid @enderror" required>
                <option value="">-- Pilih Kabel --</option>
                @foreach($master_list as $mb)
                  <option value="{{ $mb->id }}"
                    {{ old('master_barang_id', $invKabel->master_barang_id ?? '') == $mb->id ? 'selected' : '' }}>
                    {{ $mb->merek }} {{ $mb->tipe }}
                  </option>
                @endforeach
              </select>
              @error('master_barang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">ID Haspel <span class="text-danger">*</span></label>
              @if(isset($invKabel))
                <input type="text" name="id_haspel"
                       value="{{ old('id_haspel', $invKabel->id_haspel) }}"
                       class="form-control @error('id_haspel') is-invalid @enderror" readonly>
              @else
                <input type="text" name="id_haspel"
                       value="{{ old('id_haspel', $next_haspel ?? '') }}"
                       class="form-control @error('id_haspel') is-invalid @enderror"
                       placeholder="{{ $next_haspel ?? 'HSP-001' }}">
                @if($next_haspel ?? false)
                  <div class="form-text">Nomor haspel berikutnya: <strong>{{ $next_haspel }}</strong></div>
                @endif
              @endif
              @error('id_haspel')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Panjang Awal (m) <span class="text-danger">*</span></label>
              <input type="number" name="panjang_awal" min="0.1" step="0.1"
                     value="{{ old('panjang_awal', $invKabel->panjang_awal ?? '') }}"
                     class="form-control @error('panjang_awal') is-invalid @enderror"
                     placeholder="500" {{ isset($invKabel) ? 'readonly' : '' }} required>
              @if(isset($invKabel))
                <div class="form-text">Panjang awal tidak dapat diubah setelah haspel dibuat.</div>
              @endif
              @error('panjang_awal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Nilai per Meter (Rp)</label>
              <input type="number" name="nilai_per_meter" min="0" step="100"
                     value="{{ old('nilai_per_meter', $invKabel->nilai_per_meter ?? '') }}"
                     class="form-control @error('nilai_per_meter') is-invalid @enderror"
                     placeholder="0">
              @error('nilai_per_meter')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Lokasi <span class="text-danger">*</span></label>
              <select name="lokasi_id" class="form-select @error('lokasi_id') is-invalid @enderror" required>
                <option value="">-- Pilih Lokasi --</option>
                @foreach($lokasi_list as $loc)
                  <option value="{{ $loc->id }}"
                    {{ old('lokasi_id', $invKabel->lokasi_id ?? '') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->nama_lokasi }}
                  </option>
                @endforeach
              </select>
              @error('lokasi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Penanggung Jawab</label>
              <select name="penanggung_jawab_user_id" class="form-select @error('penanggung_jawab_user_id') is-invalid @enderror">
                <option value="">-- Pilih Partner --</option>
                @foreach($partner_list as $partner)
                  <option value="{{ $partner->id }}"
                    {{ old('penanggung_jawab_user_id', $invKabel->penanggung_jawab_user_id ?? '') == $partner->id ? 'selected' : '' }}>
                    {{ $partner->name }}
                  </option>
                @endforeach
              </select>
              <input type="text" name="penanggung_jawab"
                     value="{{ old('penanggung_jawab', $invKabel->penanggung_jawab ?? '') }}"
                     class="form-control mt-1 @error('penanggung_jawab') is-invalid @enderror"
                     placeholder="Atau nama manual (opsional)">
              @error('penanggung_jawab_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan" rows="2"
                        class="form-control @error('catatan') is-invalid @enderror"
                        placeholder="Keterangan tambahan...">{{ old('catatan', $invKabel->catatan ?? '') }}</textarea>
              @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>
      <div class="ms-panel-footer d-flex gap-2">
        <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan</button>
        <a href="{{ route('admin.inventory.kabel.index') }}" class="ms-btn-ghost">Batal</a>
      </div>
    </div>
  </form>
</div>
@endsection
