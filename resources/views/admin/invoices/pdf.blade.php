<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { margin-bottom: 30px; }
        .invoice-details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .badge { padding: 5px 10px; border-radius: 3px; color: white; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; }
        .badge-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NETKING</h1>
        <p>Jl Internet Cepat No. 123<br>Jakarta, Indonesia<br>Phone: (021) 1234-5678</p>
    </div>

    <div class="invoice-details">
        <h2>Invoice {{ $invoice->invoice_number }}</h2>
        <p>
            <strong>Tanggal Tagihan:</strong> {{ $invoice->created_at->format('d F Y') }}<br>
            <strong>Jatuh Tempo:</strong> {{ $invoice->due_date->format('d F Y') }}<br>
            <strong>Status:</strong>
            @if($invoice->status === 'paid')
                <span class="badge badge-success">PAID</span>
            @elseif($invoice->isOverdue())
                <span class="badge badge-danger">OVERDUE</span>
            @else
                <span class="badge badge-warning">UNPAID</span>
            @endif
        </p>
    </div>

    <div class="customer-details">
        <h3>Tagihan Kepada:</h3>
        <p>
            <strong>{{ $invoice->customer->name }}</strong><br>
            PPPoE Username: {{ $invoice->customer->pppoe_user }}<br>
            Telepon: {{ $invoice->customer->phone }}<br>
            Area: {{ $invoice->customer->area->name ?? '-' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Layanan Internet - {{ $invoice->created_at->format('F Y') }}</strong><br>
                    Langganan bulanan
                </td>
                <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td class="text-right">Total Tagihan</td>
                <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($invoice->status === 'paid')
    <div style="margin-top: 30px;">
        <p><strong>Informasi Pembayaran:</strong></p>
        <p>
            Dibayar pada: {{ $invoice->paid_at->format('d F Y H:i') }}<br>
            Metode pembayaran: {{ ucfirst($invoice->payment_method) }}<br>
            @if($invoice->payment_reference)
                Referensi: {{ $invoice->payment_reference }}
            @endif
        </p>
    </div>
    @endif

    <div style="margin-top: 50px; text-align: center; color: #666;">
        <p>Terima kasih atas kepercayaan Anda!</p>
        <p><small>Tagihan ini dibuat secara otomatis oleh sistem dan tidak memerlukan tanda tangan.</small></p>
    </div>
</body>
</html>
