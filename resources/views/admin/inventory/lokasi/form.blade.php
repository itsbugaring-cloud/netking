@extends('layouts.app')
@section('title', isset($lokasi) && $lokasi ? 'Edit Lokasi' : 'Tambah Lokasi')

@section('content')
<div class="ms-page inv-lokasi-form-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker">Inventaris / Lokasi</div>
      <h1 class="ms-page-title">{{ isset($lokasi) && $lokasi ? 'Edit Lokasi' : 'Tambah Lokasi' }}</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.inventory.lokasi.index') }}" class="ms-btn-secondary"><i class='bx bx-arrow-back'></i> Kembali</a>
    </div>
  </div>



  <form action="{{ isset($lokasi) && $lokasi ? route('admin.inventory.lokasi.update', $lokasi) : route('admin.inventory.lokasi.store') }}" method="POST">
    @csrf
    @if(isset($lokasi) && $lokasi) @method('PUT') @endif

    <div class="ms-panel">
      <div class="ms-panel-head">
        <h5 class="ms-panel-title">Informasi Lokasi</h5>
      </div>
      <div class="ms-panel-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
              <input type="text" name="nama_lokasi"
                     value="{{ old('nama_lokasi', $lokasi->nama_lokasi ?? '') }}"
                     class="form-control @error('nama_lokasi') is-invalid @enderror"
                     placeholder="Contoh: Gudang Pusat Jakarta">
              @error('nama_lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Jenis Lokasi <span class="text-danger">*</span></label>
              <select name="jenis" class="form-select @error('jenis') is-invalid @enderror">
                <option value="">-- Pilih Jenis --</option>
                @foreach(($jenis_options ?? ['gudang_utama' => 'Gudang Utama', 'pop_distribusi' => 'POP Distribusi']) as $val => $label)
                  <option value="{{ $val }}" {{ old('jenis', $lokasi->jenis ?? '') === $val ? 'selected' : '' }}>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
              @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">PIC / Penanggung Jawab</label>
              <select name="pic_user_id" class="form-select @error('pic_user_id') is-invalid @enderror" id="pic_user_select">
                <option value="">-- Pilih PIC --</option>
                @foreach($partner_list as $partner)
                  <option value="{{ $partner->id }}"
                    {{ old('pic_user_id', $lokasi->pic_user_id ?? '') == $partner->id ? 'selected' : '' }}>
                    {{ $partner->name }}
                  </option>
                @endforeach
              </select>
              <input type="text" name="pic_nama" id="pic_nama_manual"
                     value="{{ old('pic_nama', $lokasi->pic_nama ?? '') }}"
                     class="form-control mt-1 @error('pic_nama') is-invalid @enderror"
                     placeholder="Atau ketik nama manual (opsional)">
              @error('pic_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="alamat" rows="3"
                        class="form-control @error('alamat') is-invalid @enderror"
                        placeholder="Alamat lengkap lokasi">{{ old('alamat', $lokasi->alamat ?? '') }}</textarea>
              @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>
      <div class="ms-panel-footer d-flex gap-2">
        <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Simpan</button>
        <a href="{{ route('admin.inventory.lokasi.index') }}" class="ms-btn-ghost">Batal</a>
      </div>
    </div>
  </form>
</div>
@endsection
