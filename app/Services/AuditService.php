<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * List audit logs with filtering based on user role and request params.
     */
    public function listLogs(User $user, Request $request)
    {
        $query = AuditLog::with('user:id,name,email')
            ->latest();

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role !== 'administrator') {
            return null; // Signals unauthorized
        }

        // Apply filters
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('auditable_type')) {
            $type = $request->auditable_type;
            if (!str_contains($type, '\\')) {
                $type = "App\\Models\\" . ucfirst($type);
            }
            $query->where('auditable_type', $type);
        }

        return $query->paginate(50);
    }
}
