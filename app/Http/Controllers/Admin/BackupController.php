<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\RouterBackup;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::whereNotNull('router_ip')
            ->where('router_ip', '!=', '')
            ->get();

        $selectedArea = null;
        $backups = collect();

        if ($request->filled('area_id')) {
            $selectedArea = Area::findOrFail($request->area_id);
            $backups = RouterBackup::where('area_id', $selectedArea->id)
                ->orderByDesc('created_at')
                ->paginate(20)
                ->appends(['area_id' => $selectedArea->id]);
        }

        return view('admin.backups.index', compact('areas', 'selectedArea', 'backups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'type' => 'required|in:binary,text',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        $timestamp = now()->format('Ymd_His');
        $areaSlug = str_replace(' ', '-', strtolower($area->name));

        if ($request->type === 'binary') {
            $backupName = "netking_{$areaSlug}_{$timestamp}";
            $result = $mikrotik->createBackup($backupName);

            if (!$result['success']) {
                return back()->with('error', 'Gagal membuat backup: ' . ($result['error'] ?? 'Unknown'));
            }

            // Wait briefly for file to be created on router
            sleep(2);

            $filename = $result['filename'];
            $fileInfo = $mikrotik->getFileContents($filename);
            $sizeBytes = (int)($fileInfo['data']['size'] ?? 0);

            RouterBackup::create([
                'area_id' => $area->id,
                'filename' => $filename,
                'type' => 'binary',
                'size_bytes' => $sizeBytes,
                'notes' => "Binary backup created by " . auth()->user()->name,
            ]);
        } else {
            // Text export
            $result = $mikrotik->createExport();

            if (!$result['success']) {
                return back()->with('error', 'Gagal membuat export: ' . ($result['error'] ?? 'Unknown'));
            }

            $exportContent = '';
            if (is_array($result['data'])) {
                $exportContent = implode("\n", array_map(function ($line) {
                    return is_array($line) ? implode(' ', $line) : (string)$line;
                }, $result['data']));
            } else {
                $exportContent = (string)$result['data'];
            }

            $filename = "netking_{$areaSlug}_{$timestamp}.rsc";
            $storagePath = "backups/{$area->id}/{$filename}";

            Storage::disk('local')->put($storagePath, $exportContent);

            RouterBackup::create([
                'area_id' => $area->id,
                'filename' => $filename,
                'type' => 'text',
                'size_bytes' => strlen($exportContent),
                'notes' => "Text export created by " . auth()->user()->name,
            ]);
        }

        Log::info('Router backup created', [
            'admin' => auth()->user()->name,
            'area' => $area->name,
            'type' => $request->type,
        ]);

        return redirect()->route('admin.backups.index', ['area_id' => $area->id])
            ->with('success', 'Backup berhasil dibuat.');
    }

    public function download(RouterBackup $backup)
    {
        if ($backup->type === 'text') {
            $storagePath = "backups/{$backup->area_id}/{$backup->filename}";
            if (!Storage::disk('local')->exists($storagePath)) {
                return back()->with('error', 'File backup tidak ditemukan di storage.');
            }
            return Storage::disk('local')->download($storagePath, $backup->filename);
        }

        // Binary backup — stream from router
        $area = $backup->area;
        if (!$area) {
            return back()->with('error', 'Area tidak ditemukan.');
        }

        $mikrotik = MikroTikService::forArea($area);
        $fileInfo = $mikrotik->getFileContents($backup->filename);

        if (!$fileInfo['success']) {
            return back()->with('error', 'File tidak ditemukan di router: ' . ($fileInfo['error'] ?? ''));
        }

        // For binary backups we can only confirm existence; direct download
        // from RouterOS API isn't straightforward. Redirect with info.
        return back()->with('error', 'Binary backup tersimpan di router. Gunakan Winbox/FTP untuk download file: ' . $backup->filename);
    }

    public function destroy(RouterBackup $backup)
    {
        // Delete from local storage if text type
        if ($backup->type === 'text') {
            $storagePath = "backups/{$backup->area_id}/{$backup->filename}";
            Storage::disk('local')->delete($storagePath);
        }

        // Try to delete from router if binary
        if ($backup->type === 'binary' && $backup->area) {
            $mikrotik = MikroTikService::forArea($backup->area);
            $mikrotik->deleteFile($backup->filename);
        }

        $areaId = $backup->area_id;
        $backup->delete();

        Log::info('Router backup deleted', [
            'admin' => auth()->user()->name,
            'filename' => $backup->filename,
        ]);

        return redirect()->route('admin.backups.index', ['area_id' => $areaId])
            ->with('success', 'Backup berhasil dihapus.');
    }
}
