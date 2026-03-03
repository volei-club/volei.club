<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerformanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerformanceController extends Controller
{
    /**
     * Get performance history for a specific user.
     */
    public function index(Request $request, $userId)
    {
        $viewer = $request->user();
        $targetUser = User::findOrFail($userId);

        // Authorization
        if ($viewer->role === 'parinte') {
            $isChild = $viewer->children()->where('student_id', $userId)->exists();
            if (!$isChild && $viewer->id !== $userId) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        elseif (in_array($viewer->role, ['sportiv'])) {
            if ($viewer->id !== $userId) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        elseif ($viewer->role === 'manager') {
            if ($targetUser->club_id !== $viewer->club_id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $logs = PerformanceLog::where('user_id', $userId)
            ->with('coach:id,name')
            ->orderBy('log_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    /**
     * Store a new performance entry.
     */
    public function store(Request $request)
    {
        $coach = $request->user();

        if (!in_array($coach->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'log_date' => 'required|date',
            'weight' => 'nullable|numeric|min:0|max:300',
            'vertical_jump' => 'nullable|numeric|min:0|max:200',
            'serve_speed' => 'nullable|numeric|min:0|max:200',
            'reception_rating' => 'nullable|integer|min:1|max:5',
            'attack_rating' => 'nullable|integer|min:1|max:5',
            'block_rating' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        $athlete = User::findOrFail($validated['user_id']);
        if ($coach->role === 'manager' && $athlete->club_id !== $coach->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $log = PerformanceLog::create([
            'user_id' => $validated['user_id'],
            'coach_id' => $coach->id,
            'log_date' => $validated['log_date'],
            'weight' => $validated['weight'],
            'vertical_jump' => $validated['vertical_jump'],
            'serve_speed' => $validated['serve_speed'],
            'reception_rating' => $validated['reception_rating'],
            'attack_rating' => $validated['attack_rating'],
            'block_rating' => $validated['block_rating'],
            'notes' => $validated['notes'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $log->load('coach:id,name')
        ], 201);
    }

    /**
     * Delete a performance entry.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $log = PerformanceLog::findOrFail($id);

        if ($user->role === 'antrenor' && $log->coach_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role === 'manager') {
            $athlete = User::find($log->user_id);
            if ($athlete->club_id !== $user->club_id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $log->delete();

        return response()->json(['status' => 'success', 'message' => 'Entry deleted.']);
    }
}
