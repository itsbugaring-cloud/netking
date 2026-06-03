@extends('layouts.app')
@section('title', isset($invMasterBarang) && $invMasterBarang ? 'Edit Master Barang' : 'Tambah Master Barang')

@section('content')
<div class="ms-page inv-master-barang-form-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker">Inventaris / Master Barang</div>
      <h1 class="ms-page-title">{{ isset($invMasterBarang) && $invMasterBarang ? 'Edit: ' . $invMasterBarang->merek . ' ' . $invMasterBarang->tipe : 'Tambah Master Barang' }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.master-barang.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <form action="{{ isset($invMasterBarang) && $invMasterBarang ? route('admin.inventory.master-barang.update', $invMasterBarang) : route('admin.inventory.master-barang.store') }}" method="POST">
    @csrf
    @if(isset($invMasterBarang) && $invMasterBarang) @method('PUT') @endif

    <div class="ms-panel">
      <div class="ms-panel-head">
        <h5 class="ms-panel-title">Informasi Barang</h5>
      </div>
      <div class="ms-panel-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Kategori <span class="text-danger">*</span></label>
              <div class="d-flex gap-2 align-items-center">
                <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                  <option value="">-- Pilih Kategori --</option>
                  @foreach($kategori as $kat)
                    <option value="{{ $kat->id }}"
                      {{ old('kategori_id', $invMasterBarang->kategori_id ?? '') == $kat->id ? 'selected' : '' }}>
                      {{ $kat->nama }}
                    </option>
                  @endforeach
                </select>
                <a href="{{ route('admin.inventory.kategori.index') }}" class="ms-btn-ghost ms-btn-sm" target="_blank"
                   style="white-space:nowrap">
                  <i class='bx bx-plus'></i> Kelola
                </a>
              </div>
              @error('kategori_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Jenis Penghitungan <span class="text-danger">*</span></label>
              <select name="jenis_penghitungan" class="form-select @error('jenis_penghitungan') is-invalid @enderror">
                <option value="">-- Pilih Jenis --</option>
                @foreach($jenis_options as $val => $label)
                  <option value="{{ $val }}"
                    {{ old('jenis_penghitungan', $invMasterBarang->jenis_penghitungan ?? '') === $val ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
              @error('jenis_penghitungan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Merek <span class="text-danger">*</span></label>
              <input type="text" name="merek"
                     value="{{ old('merek', $invMasterBarang->merek ?? '') }}"
                     class="form-control @error('merek') is-invalid @enderror"
                     placeholder="Contoh: Huawei, ZTE, TP-Link">
              @error('merek')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Tipe / Model <span class="text-danger">*</span></label>
              <input type="text" name="tipe"
                     value="{{ old('tipe', $invMasterBarang->tipe ?? '') }}"
                     class="form-control @error('tipe') is-invalid @enderror"
                     placeholder="Contoh: HG8145V5, F601">
              @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Harga Default (Rp)</label>
              <input type="number" name="harga_default" min="0" step="1000"
                     value="{{ old('harga_default', $invMasterBarang->harga_default ?? '') }}"
                     class="form-control @error('harga_default') is-invalid @enderror"
                     placeholder="0">
              @error('harga_default')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Deskripsi</label>
              <textarea name="deskripsi" rows="3"
                        class="form-control @error('deskripsi') is-invalid @enderror"
                        placeholder="Keterangan tambahan...">{{ old('deskripsi', $invMasterBarang->deskripsi ?? '') }}</textarea>
              @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>
      <div class="ms-panel-footer d-flex gap-2">
        <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan</button>
        <a href="{{ route('admin.inventory.master-barang.index') }}" class="ms-btn-ghost">Batal</a>
      </div>
    </div>
  </form>
</div>
@endsection
