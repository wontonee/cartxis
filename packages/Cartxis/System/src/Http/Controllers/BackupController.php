<?php

declare(strict_types=1);

namespace Cartxis\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Cartxis\System\Services\BackupService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {}

    public function index()
    {
        return Inertia::render('Admin/System/Backups/Index', [
            'backups' => $this->backupService->getBackups(),
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'option' => 'nullable|in:only-db,only-files',
        ]);

        try {
            $this->backupService->createBackup($request->input('option', ''));
            return redirect()->back()->with('success', 'Backup started successfully.');
        } catch (\Spatie\DbDumper\Exceptions\CannotStartDump $e) {
            return redirect()->back()->with('error', 'Backup failed: mysqldump not found. Please install mysqldump on the server.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $request->validate([
            'disk' => 'required|string',
            'path' => 'required|string',
        ]);

        $filePath = $this->backupService->getBackupPath($request->disk, $request->path);

        if (! $filePath) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        return response()->download($filePath);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'disk' => 'required|string',
            'path' => 'required|string',
        ]);

        try {
            $this->backupService->deleteBackup($request->disk, $request->path);
            return redirect()->back()->with('success', 'Backup deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Deletion failed: ' . $e->getMessage());
        }
    }
}
