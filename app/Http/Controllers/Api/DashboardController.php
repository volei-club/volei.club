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

        // ── KPI counts ────────────────────────────────────────────────
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

        // ── Monthly trends (last 6 months) ────────────────────────────
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

        // ── Recent subscriptions (last 5) ────────────────────────────
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

        // ── Recent conversations ──────────────────────────────────────
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

        // ── Recent clubs / members ──────────────────────────────────
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

        return response()->json([
            'status' => 'success',
            'data'   => [
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
            ],
        ]);
    }
}