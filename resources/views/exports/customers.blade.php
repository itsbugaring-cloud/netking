<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Area</th>
            <th>Nama Pelanggan</th>
            <th>PPPoE User</th>
            <th>No. HP</th>
            <th>Paket</th>
            <th>Harga Paket (Rp)</th>
            <th>Harga Customer (Rp)</th>
            <th>Status</th>
            <th>Total Bayar (Rp)</th>
            <th>Tunggakan (Rp)</th>
            <th>Tgl Pasang</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $i => $c)
        @php
            $pkgPrice = (int) ($c->package?->price ?? 0);
            $custPrice = (int) ($c->package_price ?? 0);
            $hargaBeda = $custPrice > 0 && $pkgPrice > 0 && $custPrice !== $pkgPrice;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $c->area?->name ?? '-' }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ $c->pppoe_user ?? '-' }}</td>
            <td>{{ $c->phone ?? '' }}</td>
            <td>{{ $c->package?->name ?? '-' }} {{ $c->package ? '(' . $c->package->speed_down . 'M)' : '' }}</td>
            <td>{{ $pkgPrice }}</td>
            <td>{{ $custPrice > 0 ? $custPrice : '' }}</td>
            <td>{{ ['active' => 'Aktif', 'suspended' => 'Diisolir', 'inactive' => 'Nonaktif'][$c->status] ?? ucfirst($c->status) }}</td>
            <td>{{ (int) ($c->paid_total ?? 0) }}</td>
            <td>{{ (int) ($c->unpaid_total ?? 0) }}</td>
            <td>{{ $c->billing_start_date?->format('d/m/Y') ?? '' }}</td>
            <td>{{ $hargaBeda ? 'HARGA BEDA (paket=' . number_format($pkgPrice,0,',','.') . ', bayar=' . number_format($custPrice,0,',','.') . ')' : '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
