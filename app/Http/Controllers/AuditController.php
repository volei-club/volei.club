<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Display a listing of audit logs.
     * Administrators see everything.
     * Managers see logs related to their club.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = AuditLog::with('user:id,name,email')
            ->latest();

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role !== 'administrator') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Add some basic search/filtering if needed
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        if ($request->has('auditable_type') && $request->auditable_type) {
            // Support partial match for type (e.g. "User")
            $type = $request->auditable_type;
            if (!str_contains($type, '\\')) {
                $type = "App\\Models\\" . ucfirst($type);
            }
            $query->where('auditable_type', $type);
        }

        return response()->json($query->paginate(50));
    }
}
