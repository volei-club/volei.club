<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $logs = $this->auditService->listLogs($request->user(), $request);

        if ($logs === null) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($logs);
    }
}
