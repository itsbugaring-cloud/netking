<table>
    <thead>
        <tr>
            <th>No</th>
            <th>ID Pelanggan</th>
            <th>PIC</th>
            <th>Area</th>
            <th>Nama</th>
            <th>No. HP</th>
            <th>Layanan</th>
            <th>Bayar (Rp)</th>
            <th>Status</th>
            <th>Tgl Berlangganan</th>
            <th>Tgl Bayar</th>
            <th>Pembayaran</th>
            <th>Rekening</th>
            <th>Approved by</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $i => $c)
        @php
            $latestPayment = $c->latestPayment;
            $metodeLabel = match($latestPayment?->metode) {
                'transfer' => 'Transfer',
                'cash' => 'Tunai',
                default => $latestPayment?->metode ? ucfirst($latestPayment->metode) : '',
            };
            $statusLabel = match(true) {
                (bool) $c->is_free => 'Gratis',
                $c->status === 'active' => 'Aktif',
                $c->status === 'suspended' => 'Diisolir',
                $c->status === 'inactive' => 'Nonaktif',
                default => ucfirst($c->status),
            };
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $c->customer_code ?? '' }}</td>
            <td>{{ $c->partner?->name ?? '' }}</td>
            <td>{{ $c->area?->name ?? '-' }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ $c->phone ?? '' }}</td>
            <td>{{ $c->package?->name ?? '-' }}</td>
            <td>{{ (int) round((float) ($c->paid_total ?? $latestPayment?->jumlah ?? 0)) }}</td>
            <td>{{ $statusLabel }}</td>
            <td>{{ $c->billing_start_date?->format('d/m/Y') ?? '' }}</td>
            <td>{{ $latestPayment?->approved_at?->format('d/m/Y') ?? '' }}</td>
            <td>{{ $metodeLabel }}</td>
            <td>{{ $latestPayment?->rekening_tujuan ?? '' }}</td>
            <td>{{ $latestPayment?->approvedBy?->name ?? '' }}</td>
            <td>{{ $latestPayment?->catatan ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
