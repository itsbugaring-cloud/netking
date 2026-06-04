@extends('layouts.app')
@section('title', 'NMS Devices')

@section('styles')
<style>
    .nms-devices-page [style*="color:#64748b"],
    .nms-devices-page [style*="color: #64748b"],
    .nms-devices-page [style*="color:#94a3b8"],
    .nms-devices-page [style*="color: #94a3b8"] {
        color: var(--txt-3) !important;
    }

    .nms-devices-page [style*="background:rgba(0,0,0,.06)"] {
        background: var(--surface-2) !important;
    }

    .nms-devices-page [style*="background:rgba(37,99,235,.08)"] {
        background: color-mix(in srgb, var(--nk-info) 10%, var(--surface)) !important;
        border-color: color-mix(in srgb, var(--nk-info) 22%, var(--border)) !important;
        color: var(--blue) !important;
    }

    .nms-devices-page code {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--blue);
    }

    .nms-devices-page .table a {
        text-decoration: none;
    }

</style>
@endsection

@section('content')
<div class="ms-page nms-devices-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-chip'></i> Pemantauan Perangkat</div>
            <h1 class="ms-page-title">Perangkat NMS</h1>
        </div>
    </div>

    <div class="ms-panel mb-4">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title"><i class='bx bx-server me-2' style="color:var(--orange);"></i>Perangkat OLT</h5>
            </div>
            <span class="ms-kpi-chip"><strong>{{ $olts->count() }}</strong> total</span>
        </div>
        <div class="ms-table-shell">
            <div class="table-responsive">
                <table class="table ms-table-wide mb-0" style="min-width:1040px;">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Nama</th>
                            <th style="min-width:150px;">IP Address</th>
                            <th style="min-width:200px;">Merek / Model</th>
                            <th style="min-width:110px;">Total ONT</th>
                            <th style="min-width:120px;">ONT Online</th>
                            <th style="min-width:180px;">Kesehatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($olts as $olt)
                        @php $healthPct = $olt->onts_count > 0 ? round(($olt->online_count / $olt->onts_count) * 100) : 0; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.olts.show', $olt) }}" style="font-weight:600;color:inherit;text-decoration:none;">{{ $olt->name }}</a>
                            </td>
                            <td><code style="font-size:.78rem;">{{ $olt->ip_address }}</code></td>
                            <td>{{ $olt->brand }} {{ $olt->model }}</td>
                            <td>{{ $olt->onts_count }}</td>
                            <td><span style="color:var(--green);font-weight:700;">{{ $olt->online_count }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="min-width:145px;">
                                    <div style="flex:1;height:6px;background:rgba(0,0,0,.06);border-radius:999px;overflow:hidden;">
                                        <div style="width:{{ $healthPct }}%;height:100%;background:{{ $healthPct > 90 ? 'var(--green)' : ($healthPct > 70 ? 'var(--orange)' : 'var(--red)') }};"></div>
                                    </div>
                                    <span style="font-size:.72rem;font-weight:700;">{{ $healthPct }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-head">
            <div>
                <h5 class="ms-panel-title"><i class='bx bx-wifi me-2' style="color:var(--green);"></i>Router Area</h5>
            </div>
        </div>
        <div class="ms-table-shell">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:220px;">Area</th>
                            <th style="min-width:150px;">IP Router</th>
                            <th style="min-width:160px;">Pengguna</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($areas as $area)
                        <tr>
                            <td style="font-weight:600;">{{ $area->name }}</td>
                            <td><code style="font-size:.78rem;">{{ $area->router_ip ?? '—' }}</code></td>
                            <td>{{ $area->router_user ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
</script>
@endsection
