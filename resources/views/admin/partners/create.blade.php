@extends('layouts.app')
@section('title', 'Buat Mitra')

@section('content')
<div class="ms-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bx-buildings'></i> Kepemilikan Pelanggan</div>
      <h1 class="ms-page-title">Buat Mitra</h1>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.partners.index') }}" class="ms-btn-secondary">
        <i class='bx bx-arrow-back'></i> Kembali
      </a>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-xl-8">
      <form action="{{ route('admin.partners.store') }}" method="POST">
        @csrf
        <div class="ms-panel">
          <div class="ms-panel-head">
            <h5 class="ms-panel-title"><i class='bx bx-buildings me-2' style="color:#2563eb;"></i>Informasi Mitra</h5>
          </div>
          <div class="ms-panel-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Telegram Username</label>
                <div class="input-group">
                  <span class="input-group-text">@</span>
                  <input type="text" name="telegram_username" class="form-control @error('telegram_username') is-invalid @enderror" value="{{ old('telegram_username') }}" placeholder="contoh: caesarbugar">
                </div>
                <div class="form-text">Opsional. Dipakai untuk lock akses bot Telegram mitra.</div>
                @error('telegram_username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Area <span class="text-danger">*</span></label>
                <select name="area_id" class="form-select @error('area_id') is-invalid @enderror" required>
                  <option value="">Pilih Area</option>
                  @foreach($areas as $area)
                  <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                  @endforeach
                </select>
                @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Saldo Awal Dompet (Rp)</label>
                <input type="number" name="wallet_balance" class="form-control @error('wallet_balance') is-invalid @enderror" value="{{ old('wallet_balance', 0) }}" min="0" step="1000">
                @error('wallet_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="ms-panel-foot d-flex justify-content-between">
            <a href="{{ route('admin.partners.index') }}" class="ms-btn-ghost">Batal</a>
            <button type="submit" class="ms-btn"><i class='bx bx-save'></i> Buat Mitra</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
