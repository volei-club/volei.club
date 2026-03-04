<?php

namespace App\Services;

use App\Models\PerformanceLog;
use App\Models\User;

class PerformanceService
{
    /**
     * Get performance history for a user.
     */
    public function getHistory(string $userId)
    {
        return PerformanceLog::where('user_id', $userId)
            ->with('coach:id,name')
            ->orderBy('log_date', 'desc')
            ->get();
    }

    /**
     * Store a new performance entry.
     */
    public function storeEntry(array $data, string $coachId)
    {
        return PerformanceLog::create(array_merge($data, [
            'coach_id' => $coachId
        ]));
    }

    /**
     * Check if a caller can view a user's performance.
     */
    public function canViewPerformance(User $caller, User $target): bool
    {
        if ($caller->id === $target->id)
            return true;

        switch ($caller->role) {
            case 'administrator':
                return true;
            case 'manager':
                return $target->club_id === $caller->club_id;
            case 'parinte':
                return $caller->children()->where('users.id', $target->id)->exists();
            case 'antrenor':
                // Coaches can see everyone in their club for now, or we could restrict to squads
                return $target->club_id === $caller->club_id;
            default:
                return false;
        }
    }

    /**
     * Check if a coach can manage an entry.
     */
    public function canManageEntry(User $user, PerformanceLog $log): bool
    {
        if ($user->role === 'administrator')
            return true;

        if ($user->role === 'antrenor') {
            return $log->coach_id === $user->id;
        }

        if ($user->role === 'manager') {
            $athlete = User::find($log->user_id);
            return $athlete && $athlete->club_id === $user->club_id;
        }

        return false;
    }

    /**
     * Get a performance log entry by ID.
     */
    public function getLogById(string $id): PerformanceLog
    {
        return PerformanceLog::findOrFail($id);
    }
}
