<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DataTransferService;
use Illuminate\Http\Request;

class ExportImportController extends Controller
{
    protected $dataTransferService;

    public function __construct(DataTransferService $dataTransferService)
    {
        $this->dataTransferService = $dataTransferService;
    }

    public function export(Request $request, $type)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $this->dataTransferService->exportData($type);

        if (empty($data)) {
            return response()->json(['message' => 'Invalid export type or no data found'], 400);
        }

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="export_' . $type . '_' . now()->format('Y-m-d_H-i-s') . '.json"',
        ]);
    }

    public function import(Request $request)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:json,txt',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (!$data) {
            return response()->json(['message' => 'Invalid JSON file'], 400);
        }

        try {
            $this->dataTransferService->importData($data);
            return response()->json(['message' => 'Import completed successfully!']);
        }
        catch (\Exception $e) {
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
