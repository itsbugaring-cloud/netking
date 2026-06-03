@extends('layouts.app')
@section('title', 'Device Monitor')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-chip'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Pantau Perangkat</h1>
        </div>
    </div>

<div class="ms-panel">
    <div class="ms-panel-body py-4">
        <div style="font-weight:600;font-size:1rem;">Detail pemantauan untuk perangkat #{{ $id }}</div>
        <p class="mb-0 mt-2 text-muted">
            Halaman ini sebelumnya belum punya tampilan dan bisa memicu error jika diakses langsung.
            Untuk sementara, gunakan halaman <a href="{{ route('admin.nms.devices') }}">Perangkat NMS</a>
            atau <a href="{{ route('admin.nms.ports') }}">Port NMS</a> untuk pemantauan operasional.
        </p>
    </div>
</div>
</div>
@endsection
