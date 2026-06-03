<?php

namespace App\Http\Controllers\Admin;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Imports\CustomersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;

class CustomersImportController extends Controller
{
    /**
     * Show the import form
     */
    public function import()
    {
        return view('admin.customers.import');
    }

    /**
     * Process the import file
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            'mode' => 'required|in:create,update',
        ]);

        try {
            $file = $request->file('file');
            $mode = $request->input('mode', 'create');

            // Create import instance
            $import = new CustomersImport($mode);

            // Execute import
            Excel::import($import, $file);

            // Get statistics
            $stats = $import->getStats();

            // Get failures
            $failures = $import->failures();

            // Prepare error details for display
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'value' => $failure->values()[$failure->attribute()] ?? null,
                    'errors' => $failure->errors(),
                ];
            }

            // If there are errors, return with error details
            if (count($errors) > 0) {
                return redirect()
                    ->route('admin.customers.import')
                    ->with('import_errors', $errors)
                    ->with('import_success', [
                        'success' => $stats['success'],
                        'skipped' => $stats['skipped'],
                    ])
                    ->with('warning', "Import completed with {$stats['success']} successful and " . count($errors) . " failed rows.");
            }

            // Success
            return redirect()
                ->route('admin.customers.index')
                ->with('import_success', $stats)
                ->with('success', "Successfully imported {$stats['success']} customers! Skipped {$stats['skipped']} existing customers.");
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'value' => $failure->values()[$failure->attribute()] ?? null,
                    'errors' => $failure->errors(),
                ];
            }

            return redirect()
                ->route('admin.customers.import')
                ->with('import_errors', $errors)
                ->with('error', 'Import failed with validation errors. Please check the error details below.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.customers.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'name',
            'pppoe_user',
            'pppoe_password',
            'area_id',
            'partner_id',
            'package_id',
            'remote_ip',
            'phone',
            'address',
        ];

        $sampleData = [
            [
                'name' => 'Budi Santoso',
                'pppoe_user' => 'budi.santoso',
                'pppoe_password' => 'changeme123',
                'area_id' => '1',
                'partner_id' => '2',
                'package_id' => '1',
                'remote_ip' => '', // Leave empty for auto-allocation
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 10, RT 01/RW 02',
            ],
            [
                'name' => 'Siti Rahayu',
                'pppoe_user' => 'siti.rahayu',
                'pppoe_password' => 'changeme456',
                'area_id' => '1',
                'partner_id' => '2',
                'package_id' => '2',
                'remote_ip' => '10.10.10.50', // Or specify IP manually
                'phone' => '081234567891',
                'address' => 'Jl. Sudirman No. 25, RT 03/RW 04',
            ],
        ];

        $csv = fopen('php://temp', 'w+');

        // Write headers
        fputcsv($csv, $headers);

        // Write sample data
        foreach ($sampleData as $row) {
            fputcsv($csv, $row);
        }

        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);

        return Response::make($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customer_import_template.csv"',
        ]);
    }
}
