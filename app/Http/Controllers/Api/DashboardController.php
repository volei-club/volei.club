<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard statistics based on role.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role, ['administrator', 'manager'])) {
            $clubId = ($user->role === 'manager') ? $user->club_id : $request->query('club_id');

            $stats = $this->dashboardService->getSummaryStats($clubId);
            $trends = $this->dashboardService->getActivityTrends($clubId);

            $data = [
                'kpi' => $stats,
                'trends' => [
                    'sportivi' => array_column($trends, 'sportivi'),
                    'grupe' => array_column($trends, 'grupe'),
                    'antrenamente' => array_column($trends, 'antrenamente'),
                    'abonamente' => array_column($trends, 'abonamente'),
                ],
                'recent_clubs' => ($user->role === 'administrator') ? $this->dashboardService->getRecentClubs() : [],
                'recent_members' => $this->dashboardService->getRecentMembers($clubId),
                'recent_subscriptions' => $this->dashboardService->getRecentSubscriptions($clubId),
                'recent_conversations' => $this->dashboardService->getRecentConversations($user),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        if ($user->role === 'sportiv' || $user->role === 'parinte') {
            $subject = $user;
            if ($user->role === 'parinte' && $request->filled('child_id')) {
                $child = $user->children()->where('users.id', $request->child_id)->first();
                if ($child) {
                    $subject = $child;
                }
            }

            $stats = $this->dashboardService->getAthleteStats($subject);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'athlete_stats' => $stats,
                    'recent_conversations' => $this->dashboardService->getRecentConversations($user),
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }
}