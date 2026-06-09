<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CustomersExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = Customer::with(['area', 'partner', 'package'])
            ->withSum(['invoices as paid_total' => fn($q) => $q->where('status', 'paid')], 'amount')
            ->withSum(['invoices as unpaid_total' => fn($q) => $q->where('status', 'unpaid')], 'amount')
            ->withCount(['invoices as total_invoices']);

        if ($this->request->filled('area_id')) {
            $query->where('area_id', $this->request->area_id);
        }
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        $customers = $query->orderBy('area_id')->orderBy('name')->get();

        return view('exports.customers', compact('customers'));
    }

    public function title(): string
    {
        return 'Data Pelanggan';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Header row styling
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'], // Blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // All cells border
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Freeze header row
        $sheet->freezePane('A2');

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Number format for currency columns (Harga, Paid, Tunggakan)
        $sheet->getStyle("G2:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("I2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("J2:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
