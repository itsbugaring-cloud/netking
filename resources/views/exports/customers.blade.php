<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Area</th>
            <th>Nama Pelanggan</th>
            <th>PPPoE User</th>
            <th>No. HP</th>
            <th>Paket</th>
            <th>Harga (Rp)</th>
            <th>Status</th>
            <th>Total Bayar (Rp)</th>
            <th>Tunggakan (Rp)</th>
            <th>Tgl Billing Start</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $c->area?->name ?? '-' }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ $c->pppoe_user ?? '-' }}</td>
            <td>{{ $c->phone ?? '-' }}</td>
            <td>{{ $c->package?->name ?? '-' }} {{ $c->package ? '(' . $c->package->speed_down . 'M)' : '' }}</td>
            <td>{{ (int) ($c->package_price ?: ($c->package?->price ?? 0)) }}</td>
            <td>{{ ['active' => 'Aktif', 'suspended' => 'Diisolir', 'inactive' => 'Nonaktif'][$c->status] ?? ucfirst($c->status) }}</td>
            <td>{{ (int) ($c->paid_total ?? 0) }}</td>
            <td>{{ (int) ($c->unpaid_total ?? 0) }}</td>
            <td>{{ $c->billing_start_date?->format('d/m/Y') ?? '-' }}</td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>
