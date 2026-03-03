<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Models\Team;
use App\Models\Training;
use App\Models\UserSubscription;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\PerformanceLog;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function monthlyTrend(string $table, string $dateColumn, ?string $whereColumn = null, $whereValue = null): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $query = DB::table($table)
                ->whereYear($dateColumn, $date->year)
                ->whereMonth($dateColumn, $date->month)
                ->whereNull('deleted_at');
            if ($whereColumn) {
                $query->where($whereColumn, $whereValue);
            }
            $months[] = (int) $query->count();
        }
        return $months;
    }

    private function monthlyTrendEloquent($model, string $dateColumn, ?callable $scope = null): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $query = $model::whereYear($dateColumn, $date->year)
                           ->whereMonth($dateColumn, $date->month);
            if ($scope) $scope($query);
            $months[] = (int) $query->count();
        }
        return $months;
    }

    public function stats(Request $request)
    {
        $role    = $request->user()->role;
        $clubId  = $request->user()->club_id;
        $userId  = $request->user()->id;
        $isAdmin = $role === 'administrator';

        // KPI counts
        $clubsCount = $isAdmin ? Club::count() : 1;

        $sportiviCount = User::where('role', 'sportiv')
            ->when(!$isAdmin, fn($q) => $q->where('club_id', $clubId))
            ->count();

        $teamsCount = Team::query()
            ->when(!$isAdmin, fn($q) => $q->where('club_id', $clubId))
            ->count();

        $trainingsCount = Training::query()
            ->when(!$isAdmin, fn($q) => $q->whereHas('team', fn($tq) => $tq->where('club_id', $clubId)))
            ->count();

        $subsCount = UserSubscription::query()
            ->when(!$isAdmin, fn($q) => $q->whereHas('user', fn($uq) => $uq->where('club_id', $clubId)))
            ->count();

        // Monthly trends (last 6 months)
        $clubsTrend = $this->monthlyTrendEloquent(Club::class, 'created_at');

        $sportiviTrend = $this->monthlyTrendEloquent(User::class, 'created_at', function($q) use ($isAdmin, $clubId) {
            $q->where('role', 'sportiv');
            if (!$isAdmin) $q->where('club_id', $clubId);
        });

        $teamsTrend = $this->monthlyTrendEloquent(Team::class, 'created_at', function($q) use ($isAdmin, $clubId) {
            if (!$isAdmin) $q->where('club_id', $clubId);
        });

        $subsTrend = $this->monthlyTrendEloquent(UserSubscription::class, 'created_at', function($q) use ($isAdmin, $clubId) {
            if (!$isAdmin) $q->whereHas('user', fn($uq) => $uq->where('club_id', $clubId));
        });

        $trainingsTrend = $this->monthlyTrendEloquent(Training::class, 'created_at', function($q) use ($isAdmin, $clubId) {
            if (!$isAdmin) $q->whereHas('team', fn($tq) => $tq->where('club_id', $clubId));
        });

        // Recent subscriptions (last 5)
        $recentSubscriptions = UserSubscription::with(['user:id,name,photo', 'subscription:id,name,price'])
            ->when(!$isAdmin, fn($q) => $q->whereHas('user', fn($uq) => $uq->where('club_id', $clubId)))
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'user_name'  => $s->user?->name,
                'user_photo' => $s->user?->photo,
                'plan_name'  => $s->subscription?->name ?? '-',
                'price'      => $s->subscription?->price,
                'status'     => $s->status,
                'created_at' => $s->created_at?->format('d M Y'),
            ]);

        // Recent conversations
        $recentConversations = Conversation::whereHas('users', fn($q) => $q->where('users.id', $userId))
            ->with([
                'lastMessage.sender:id,name,photo',
                'users' => fn($q) => $q->where('users.id', '!=', $userId)->select('users.id', 'users.name', 'users.photo'),
            ])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($conv) use ($userId) {
                $other   = $conv->users->first();
                $lastMsg = $conv->lastMessage;
                $pivot   = DB::table('conversation_user')
                    ->where('conversation_id', $conv->id)
                    ->where('user_id', $userId)
                    ->first();
                $lastReadAt = $pivot?->last_read_at;
                $unreadQ = Message::where('conversation_id', $conv->id)->where('sender_id', '!=', $userId);
                if ($lastReadAt) {
                    $unreadQ->where('created_at', '>', $lastReadAt);
                }
                return [
                    'conversation_id' => $conv->id,
                    'other_name'      => $other?->name ?? 'Grup',
                    'other_photo'     => $other?->photo,
                    'last_message'    => $lastMsg?->content ?? '',
                    'time'            => $lastMsg ? Carbon::parse($lastMsg->created_at)->diffForHumans(null, true, true) : '',
                    'unread'          => $unreadQ->count(),
                ];
            });

        // Recent clubs / members
        $recentClubs = $isAdmin
            ? Club::latest()->limit(5)->get(['id', 'name', 'created_at'])->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'created_at' => $c->created_at?->format('d M Y'),
              ])
            : collect([]);

        $recentMembers = !$isAdmin
            ? User::where('club_id', $clubId)->whereIn('role', ['sportiv', 'antrenor'])
                ->latest()->limit(5)->get(['id', 'name', 'photo', 'role', 'created_at'])
                ->map(fn($u) => [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'photo'      => $u->photo,
                    'role'       => $u->role,
                    'created_at' => $u->created_at?->format('d M Y'),
                ])
            : collect([]);

        $data = [
            'kpi' => [
                'clubs'        => $clubsCount,
                'sportivi'     => $sportiviCount,
                'grupe'        => $teamsCount,
                'antrenamente' => $trainingsCount,
                'abonamente'   => $subsCount,
            ],
            'trends' => [
                'clubs'        => $clubsTrend,
                'sportivi'     => $sportiviTrend,
                'grupe'        => $teamsTrend,
                'abonamente'   => $subsTrend,
                'antrenamente' => $trainingsTrend,
            ],
            'recent_clubs'         => $recentClubs,
            'recent_members'       => $recentMembers,
            'recent_subscriptions' => $recentSubscriptions,
            'recent_conversations' => $recentConversations,
        ];

        // Athlete / Parent Specifics
        if (in_array($role, ['sportiv', 'parinte'])) {
            if ($role === 'parinte') {
                $athletes = $request->user()->children()->get();
            } else {
                $athletes = collect([$request->user()]);
            }

            $squadIds = [];
            foreach ($athletes as $athlete) {
                foreach ($athlete->squads()->pluck('squads.id')->toArray() as $sid) {
                    $squadIds[] = $sid;
                }
            }
            $squadIds = array_unique($squadIds);

            $sessions = collect();

            // 1. Recurring Trainings (compute next occurrence)
            $dayMap = [
                'luni' => 1, 'marti' => 2, 'miercuri' => 3, 'joi' => 4,
                'vineri' => 5, 'sambata' => 6, 'duminica' => 0
            ];

            $trainings = Training::with(['location', 'team', 'squad'])
                ->whereIn('squad_id', $squadIds)
                ->get()
                ->map(function($t) use ($dayMap) {
                    $targetDow = $dayMap[strtolower($t->day_of_week)] ?? 1;
                    $now = Carbon::now();
                    $next = $now->copy()->next($targetDow === 0 ? Carbon::SUNDAY : $targetDow);
                    // If today's the day and session hasn't started yet, use today
                    if ($now->dayOfWeek === $targetDow && $now->format('H:i:s') < $t->start_time) {
                        $next = $now->copy()->startOfDay();
                    }
                    $sortKey = $next->format('Y-m-d') . ' ' . $t->start_time;
                    return [
                        'type'     => 'training',
                        'title'    => 'Antrenament ' . ($t->team?->name ?: ($t->squad?->name ?: '')),
                        'sort_key' => $sortKey,
                        'date'     => $next->format('d M Y'),
                        'time'     => substr($t->start_time, 0, 5),
                        'location' => $t->location?->name,
                    ];
                });
            $sessions = $sessions->concat($trainings);

            // 2. Upcoming Matches (by squad OR directly added as player)
            $athleteIds = $athletes->pluck('id')->toArray();
            $games = Game::with(['squad', 'team'])
                ->where(function($q) use ($squadIds, $athleteIds) {
                    $q->whereIn('squad_id', $squadIds)
                      ->orWhereHas('players', fn($pq) => $pq->whereIn('users.id', $athleteIds));
                })
                ->where('match_date', '>=', Carbon::now())
                ->orderBy('match_date')
                ->get()
                ->map(function($g) {
                    return [
                        'type'     => 'match',
                        'title'    => 'Meci vs ' . $g->opponent_name,
                        'sort_key' => $g->match_date->format('Y-m-d H:i:s'),
                        'date'     => $g->match_date->format('d M Y'),
                        'time'     => $g->match_date->format('H:i'),
                        'location' => $g->location,
                    ];
                });
            $sessions = $sessions->concat($games);

            $nextSession = $sessions->sortBy('sort_key')->values()->first();

            // Latest Performance Log
            $latestPerformance = PerformanceLog::whereIn('user_id', $athleteIds)
                ->orderByDesc('log_date')
                ->orderByDesc('created_at')
                ->first();

            // Active Subscription
            $activeSub = UserSubscription::with('subscription')
                ->whereIn('user_id', $athleteIds)
                ->whereIn('status', ['ACTIVE', 'activ', 'ACTIVE_PAID', 'active_paid', 'active_pending'])
                ->orderByRaw("FIELD(status, 'active_paid', 'ACTIVE_PAID', 'ACTIVE', 'activ', 'active_pending')")
                ->first();

            $data['athlete_stats'] = [
                'next_session'       => $nextSession,
                'latest_performance' => $latestPerformance ? [
                    'vertical_jump' => $latestPerformance->vertical_jump,
                    'serve_speed'   => $latestPerformance->serve_speed,
                    'weight'        => $latestPerformance->weight,
                    'date'          => $latestPerformance->log_date
                        ? Carbon::parse($latestPerformance->log_date)->format('d M Y')
                        : null,
                ] : null,
                'subscription'       => $activeSub ? [
                    'plan_name' => $activeSub->subscription?->name ?: ($activeSub->plan_name ?? 'Abonament Activ'),
                    'status'    => $activeSub->status,
                    'expiry'    => $activeSub->expires_at
                        ? Carbon::parse($activeSub->expires_at)->format('d M Y')
                        : 'N/A',
                ] : null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }
}