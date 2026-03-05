<?php

namespace App\Services;

use App\Models\User;
use App\Models\Team;
use App\Models\Training;
use App\Models\UserSubscription;
use App\Models\PerformanceLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get summary KPIs for a club or across all clubs.
     */
    public function getSummaryStats(?string $clubId = null)
    {
        $userQuery = User::query();
        $teamQuery = Team::query();
        $trainingQuery = Training::query();
        $subQuery = UserSubscription::query();

        if ($clubId) {
            $userQuery->where('club_id', $clubId);
            $teamQuery->where('club_id', $clubId);
            $trainingQuery->where('club_id', $clubId);
            $subQuery->whereHas('user', fn($q) => $q->where('club_id', $clubId));
        }

        return [
            'sportivi' => (clone $userQuery)->where('role', 'sportiv')->count(),
            'grupe' => $teamQuery->count(),
            'antrenamente' => $trainingQuery->count(),
            'abonamente' => $subQuery->where('status', 'active_paid')
            ->where('expires_at', '>=', now())
            ->count(),
            'clubs' => (clone $userQuery)->distinct('club_id')->count('club_id'), // Simplified for admin
        ];
    }

    /**
     * Get recent clubs.
     */
    public function getRecentClubs()
    {
        return \App\Models\Club::orderBy('created_at', 'desc')->limit(5)->get()->map(function ($club) {
            return [
                'id' => $club->id,
                'name' => $club->name,
                'created_at' => $club->created_at->format('d.m.Y')
            ];
        });
    }

    /**
     * Get recent members for a club.
     */
    public function getRecentMembers(?string $clubId = null)
    {
        $query = User::whereIn('role', ['sportiv', 'antrenor'])->orderBy('created_at', 'desc');
        if ($clubId) {
            $query->where('club_id', $clubId);
        }
        return $query->limit(5)->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'photo' => $user->photo,
                'created_at' => $user->created_at->format('d.m.Y')
            ];
        });
    }

    /**
     * Get recent subscriptions.
     */
    public function getRecentSubscriptions(?string $clubId = null)
    {
        $query = UserSubscription::with(['user', 'subscription'])->orderBy('created_at', 'desc');
        if ($clubId) {
            $query->whereHas('user', fn($q) => $q->where('club_id', $clubId));
        }
        return $query->limit(5)->get()->map(function ($sub) {
            return [
                'id' => $sub->id,
                'plan_name' => $sub->subscription->name ?? 'Abonament',
                'user_name' => $sub->user->name ?? 'Utilizator',
                'status' => $sub->status,
                'created_at' => $sub->created_at->format('d.m.Y')
            ];
        });
    }

    /**
     * Get recent conversations for dash.
     */
    public function getRecentConversations(User $user)
    {
        return \App\Models\Conversation::whereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->with(['users', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($conv) use ($user) {
            $partner = $conv->users->first(fn($u) => $u->id !== $user->id);
            return [
                'conversation_id' => $conv->id,
                'other_name' => $partner->name ?? 'Necunoscut',
                'other_photo' => $partner->photo ?? null,
                'last_message' => $conv->lastMessage->content ?? 'Niciun mesaj',
                'time' => $conv->updated_at->format('H:i'),
                'unread' => 0 // Simplified for dash
            ];
        });
    }

    /**
     * Get activity trends (e.g. active subscriptions over last 6 months).
     */
    public function getActivityTrends(?string $clubId = null)
    {
        $trends = [];
        $now = Carbon::now();

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $subQuery = UserSubscription::where('status', 'active_paid')
                ->where('starts_at', '<=', $end)
                ->where('expires_at', '>=', $start);

            $userQuery = User::where('role', 'sportiv')
                ->where('created_at', '<=', $end);

            $squadQuery = \App\Models\Squad::where('created_at', '<=', $end);

            $trainingQuery = Training::whereBetween('created_at', [$start, $end]);

            if ($clubId) {
                $subQuery->whereHas('user', fn($q) => $q->where('club_id', $clubId));
                $userQuery->where('club_id', $clubId);
                $squadQuery->whereHas('team', fn($q) => $q->where('club_id', $clubId));
                $trainingQuery->where('club_id', $clubId);
            }

            $trends[] = [
                'month' => $month->format('M'),
                'abonamente' => $subQuery->count(),
                'sportivi' => $userQuery->count(),
                'grupe' => $squadQuery->count(),
                'antrenamente' => $trainingQuery->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get specific stats for an athlete.
     */
    public function getAthleteStats(User $athlete)
    {
        // 1. Next Session
        $attendanceService = app(AttendanceService::class);
        $calendar = $attendanceService->generateCalendar($athlete, 2); // Get next 2 weeks
        $nextSession = collect($calendar)
            ->filter(fn($s) => !($s['is_cancelled'] ?? false) && Carbon::parse($s['start'])->isFuture())
            ->first();

        // 2. Latest Performance
        $latestPerformance = PerformanceLog::where('user_id', $athlete->id)
            ->orderBy('log_date', 'desc')
            ->first();

        $performanceData = null;
        if ($latestPerformance) {
            $performanceData = [
                'vertical_jump' => $latestPerformance->vertical_jump,
                'serve_speed' => $latestPerformance->serve_speed,
                'date' => $latestPerformance->log_date ?Carbon::parse($latestPerformance->log_date)->format('d.m.Y') : '-',
            ];
        }

        // 3. Active Subscription
        $activeSubscription = UserSubscription::where('user_id', $athlete->id)
            ->whereIn('status', ['active_paid', 'active_pending'])
            ->where('expires_at', '>=', now())
            ->with('subscription')
            ->orderBy('expires_at', 'desc')
            ->first();

        $subscriptionData = null;
        if ($activeSubscription) {
            $subscriptionData = [
                'plan_name' => $activeSubscription->subscription->name ?? 'Abonament',
                'status' => $activeSubscription->status,
                'expiry' => $activeSubscription->expires_at ?Carbon::parse($activeSubscription->expires_at)->format('d.m.Y') : '-',
            ];
        }

        // 4. Attendance Rate
        $totalSessions = DB::table('attendances')->where('user_id', $athlete->id)->count();
        $presentSessions = DB::table('attendances')->where('user_id', $athlete->id)->where('status', 'prezent')->count();
        $attendanceRate = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100) : 0;

        return [
            'next_session' => $nextSession ? [
                'title' => $nextSession['title'],
                'date' => Carbon::parse($nextSession['start'])->translatedFormat('d F Y'),
                'time' => $nextSession['start_time'] . ' - ' . $nextSession['end_time'],
                'location' => $nextSession['location'],
                'type' => $nextSession['type'],
            ] : null,
            'latest_performance' => $performanceData,
            'subscription' => $subscriptionData,
            'attendance_rate' => $attendanceRate,
        ];
    }
}
