<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerformanceLog;
use App\Models\User;
use App\Services\PerformanceService;
use App\Services\UserService;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    protected $performanceService;
    protected $userService;

    public function __construct(PerformanceService $performanceService, UserService $userService)
    {
        $this->performanceService = $performanceService;
        $this->userService = $userService;
    }

    /**
     * Get performance history for a specific user.
     */
    public function index(Request $request, $userId)
    {
        $viewer = $request->user();
        $targetUser = $this->userService->getUserById($userId);

        if (!$this->performanceService->canViewPerformance($viewer, $targetUser)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = $this->performanceService->getHistory($userId);

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

        $athlete = $this->userService->getUserById($validated['user_id']);
        if ($coach->role === 'manager' && $athlete->club_id !== $coach->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $log = $this->performanceService->storeEntry($validated, $coach->id);

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

        if (!$this->performanceService->canManageEntry($user, $log)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $log->delete();

        return response()->json(['status' => 'success', 'message' => 'Entry deleted.']);
    }
}
