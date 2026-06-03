@extends('customer.layouts.dashboard')

@section('title', 'Profil Saya - Portal Pelanggan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Profil Saya</h2>
                <div class="text-muted mt-1">Lihat dan perbarui informasi Anda</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <span class="avatar avatar-xl mb-3" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth('customer')->user()->name) }}&background=206bc4&color=fff&size=128)"></span>
                        <h3 class="mb-0">{{ auth('customer')->user()->name }}</h3>
                        <p class="text-muted">{{ auth('customer')->user()->pppoe_user }}</p>
                        @if(auth('customer')->user()->status === 'active')
                        <span class="badge bg-success">Aktif</span>
                        @else
                        <span class="badge bg-danger">{{ ucfirst(auth('customer')->user()->status) }}</span>
                        @endif
                    </div>
                    <div class="card-body border-top">
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Paket</div>
                                <div class="datagrid-content">{{ auth('customer')->user()->package->name ?? 'N/A' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Area</div>
                                <div class="datagrid-content">{{ auth('customer')->user()->area->name ?? 'N/A' }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">IP Address</div>
                                <div class="datagrid-content"><code>{{ auth('customer')->user()->remote_ip ?? 'Dinamis' }}</code></div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Bergabung Sejak</div>
                                <div class="datagrid-content">{{ auth('customer')->user()->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <form action="{{ route('customer.profile.contact') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-user me-2"></i>Informasi Pribadi</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth('customer')->user()->name) }}">
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Telepon</label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth('customer')->user()->phone) }}">
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', auth('customer')->user()->address) }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy icon"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>

                <form action="{{ route('customer.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="ti ti-lock me-2"></i>Ganti Password Portal</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Password Portal Lama</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Password Portal Baru</label>
                                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required>
                                        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Konfirmasi Password</label>
                                        <input type="password" name="new_password_confirmation" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="ti ti-lock icon"></i> Ganti Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
