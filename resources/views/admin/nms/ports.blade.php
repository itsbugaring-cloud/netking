@extends('layouts.app')
@section('title', 'NMS Port Traffic')

@section('content')
<div class="ms-page">
    <div class="ms-page-head">
        <div>
            <div class="ms-page-kicker"><i class='bx bx-transfer'></i> Pemantauan Jaringan</div>
            <h1 class="ms-page-title">Lalu Lintas Port</h1>
        </div>
    </div>

    @foreach($olts as $olt)
    <div class="ms-panel mb-3">
        <div class="ms-panel-head">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon si-blue" style="width:36px;height:36px;min-width:36px;">
                    <i class='bx bx-server'></i>
                </div>
                <div>
                    <div style="font-weight:700;color:var(--txt);">{{ $olt->name }}</div>
                    <div style="font-size:.75rem;color:var(--txt-3);">Merek: {{ $olt->brand }} | IP: {{ $olt->ip_address }}</div>
                </div>
            </div>
            <span class="ms-chip">{{ $olt->onts->count() }} ONTs</span>
        </div>
        <div class="ms-table-shell">
            @if($olt->onts->isEmpty())
            <div class="empty-state" style="padding:2rem 1rem;">
                <div class="empty-state-icon"><i class='bx bx-info-circle'></i></div>
                <div class="empty-state-title">Tidak ada ONT terdaftar</div>
                <div class="empty-state-desc">OLT ini belum memiliki inventaris ONT.</div>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="cell-nowrap">Port</th>
                            <th class="cell-nowrap cell-index">ID ONT</th>
                            <th class="cell-nowrap cell-serial">SN / Mac</th>
                            <th>Pelanggan</th>
                            <th>Status</th>
                            <th>Sinyal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentPort = null; @endphp
                        @foreach($olt->onts as $ont)
                            @if($currentPort !== $ont->pon_port)
                                @if($currentPort !== null)
                                    <tr><td colspan="6" style="padding:0;height:4px;background:#f8fafc;"></td></tr>
                                @endif
                                @php $currentPort = $ont->pon_port; @endphp
                            @endif
                            <tr>
                                <td class="fw-semibold">
                                    <span class="ms-chip" style="min-height:28px;">PON {{ $ont->pon_port }}</span>
                                </td>
                                <td class="cell-nowrap cell-index">#{{ $ont->olt_port_index ?? '—' }}</td>
                                <td class="cell-nowrap cell-serial">
                                    <code class="bg-primary-subtle text-primary px-2 py-1 rounded" style="font-size:0.75rem;">{{ $ont->serial_number ?: '—' }}</code>
                                </td>
                                <td>
                                    @if($ont->customer)
                                        <span class="fw-semibold" style="color:var(--txt);">{{ $ont->customer->name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Belum Ditetapkan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ont->status === 'online')
                                    <span class="badge-status badge-active">Online</span>
                                    @else
                                    <span class="badge-status badge-danger">Offline</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $sig = (float)$ont->rx_power;
                                        $color = 'var(--txt-3)';
                                        if($sig < 0 && $sig > -30) {
                                            $color = $sig > -25 ? 'var(--green)' : ($sig > -28 ? 'var(--orange)' : 'var(--red)');
                                        }
                                    @endphp
                                    <span style="color:{{ $color }};font-weight:700;">{{ $ont->rx_power ?: '—' }} dBm</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection

