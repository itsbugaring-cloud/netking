<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Masuk - Portal Pelanggan</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --tblr-font-sans-serif: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 50%, #7c3aed 100%);
            min-height: 100vh;
            font-feature-settings: "cv03", "cv04", "cv11";
        }
    </style>
</head>

<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <span class="avatar avatar-xl bg-white text-primary mb-3" style="font-size: 2rem;">
                    <i class="ti ti-network"></i>
                </span>
                <h1 class="text-white mb-1">NETKING</h1>
                <p class="text-white-50">Portal Pelanggan</p>
            </div>
            <div class="card card-md shadow-lg">
                <div class="card-body">
                    <h2 class="h3 text-center mb-4">Masuk ke akun Anda</h2>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="d-flex">
                            <div><i class="ti ti-alert-circle me-2"></i></div>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('customer.login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">ID Pelanggan / Nomor HP / Username PPPoE</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text"><i class="ti ti-user"></i></span>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                    placeholder="Contoh: NK000123 atau 0812xxxx atau username PPPoE" value="{{ old('username', request('username')) }}" required autofocus>
                            </div>
                            @error('username')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">Gunakan ID pelanggan yang dikirim via admin/WhatsApp agar pelanggan langsung masuk ke portal pembayaran.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password Portal</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Masukkan password portal Anda" required>
                            </div>
                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input">
                                <span class="form-check-label">Ingat saya</span>
                            </label>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-login me-2"></i> Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center text-white-50 mt-3">
                <small>Butuh reset password portal? Hubungi admin atau partner Netking Anda</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>

</html>
