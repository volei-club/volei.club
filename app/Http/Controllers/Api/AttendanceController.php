<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Training;
use App\Models\Game;
use App\Models\Squad;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * List attendance for a specific training + date.
     * Coach/Manager: returns all squad members with status.
     * Athlete: returns only their own record.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'training_id' => 'required|uuid|exists:trainings,id',
            'date'        => 'required|date',
        ]);

        $training = Training::with('squad.users')->findOrFail($request->training_id);
        $date = Carbon::parse($request->date)->toDateString();

        if ($user->role === 'antrenor' && $training->coach_id !== $user->id) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $existing = Attendance::where('training_id', $request->training_id)
            ->where('date', $date)
            ->get()
            ->keyBy('user_id');

        if (in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            $members = $training->squad?->users ?? collect();

            $result = $members->map(function ($member) use ($existing) {
                $record = $existing->get($member->id);
                return [
                    'user_id'       => $member->id,
                    'name'          => $member->name,
                    'photo'         => $member->photo,
                    'attendance_id' => $record?->id,
                    'status'        => $record?->status ?? null,
                    'notes'         => $record?->notes ?? null,
                ];
            });

            return response()->json(['status' => 'success', 'data' => $result]);
        }

        $record = $existing->get($user->id);
        return response()->json(['status' => 'success', 'data' => $record]);
    }

    /**
     * Mark attendance for a single athlete (create or update).
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $validated = $request->validate([
            'training_id' => 'required|uuid|exists:trainings,id',
            'user_id'     => 'required|uuid|exists:users,id',
            'date'        => 'required|date',
            'status'      => 'required|in:prezent,absent,motivat',
            'notes'       => 'nullable|string|max:500',
        ]);

        $training = Training::findOrFail($validated['training_id']);

        if ($user->role === 'antrenor' && $training->coach_id !== $user->id) {
            return response()->json(['message' => 'Nu esti antrenorul acestui antrenament.'], 403);
        }

        $date = Carbon::parse($validated['date'])->toDateString();

        $attendance = Attendance::updateOrCreate(
            [
                'training_id' => $validated['training_id'],
                'user_id'     => $validated['user_id'],
                'date'        => $date,
            ],
            [
                'status'    => $validated['status'],
                'notes'     => $validated['notes'] ?? null,
                'marked_by' => $user->id,
            ]
        );

        return response()->json(['status' => 'success', 'data' => $attendance->load('user')], 201);
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(string $id)
    {
        $user = request()->user();
        $attendance = Attendance::findOrFail($id);

        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Acces interzis.'], 403);
        }

        $attendance->delete();
        return response()->json(null, 204);
    }

    /**
     * Return calendar sessions for the current user (or a child if parent).
     */
    public function myCalendar(Request $request)
    {
        $user    = $request->user();
        $weeks   = (int) $request->input('weeks', 4);
        $childId = $request->input('child_id');

        if ($user->role === 'parinte' && $childId) {
            $isParent = $user->children()->where('users.id', $childId)->exists();
            if (!$isParent) {
                return response()->json(['message' => 'Acces interzis.'], 403);
            }
            $subject = User::findOrFail($childId);
        } else {
            $subject = $user;
        }

        $trainings = $this->getTrainingsForUser($subject);
        $games     = $this->getGamesForUser($subject);

        $start = Carbon::today();
        $end   = Carbon::today()->addWeeks($weeks)->endOfDay();

        $sessions = [];

        // 1. Process Trainings
        foreach ($trainings as $training) {
            $current = $this->nextOccurrence($start->copy(), $training->day_of_week);

            while ($current->lte($end)) {
                $dateStr = $current->toDateString();

                $attendance = Attendance::where('training_id', $training->id)
                    ->where('user_id', $subject->id)
                    ->where('date', $dateStr)
                    ->first();

                $sessions[] = [
                    'id'            => 'training-' . $training->id . '-' . $dateStr,
                    'type'          => 'training',
                    'training_id'   => $training->id,
                    'date'          => $dateStr,
                    'day_of_week'   => $training->day_of_week,
                    'start_time'    => $training->start_time,
                    'end_time'      => $training->end_time,
                    'location'      => $training->location?->name,
                    'team'          => $training->team?->name,
                    'squad'         => $training->squad?->name,
                    'status'        => $attendance?->status ?? null,
                    'attendance_id' => $attendance?->id,
                    'is_past'       => $current->isPast(),
                ];

                $current->addWeek();
            }
        }

        // 2. Process Games
        foreach ($games as $game) {
            $gameDate = Carbon::parse($game->match_date);
            if ($gameDate->lt($start) || $gameDate->gt($end)) continue;

            $dateStr = $gameDate->toDateString();
            
            // For athletes, check their role
            $role = null;
            if ($subject->role === 'sportiv') {
                $pivot = $game->players()->where('user_id', $subject->id)->first();
                $role = $pivot?->pivot?->role;
            }

            $sessions[] = [
                'id'            => 'game-' . $game->id,
                'type'          => 'game',
                'game_id'       => $game->id,
                'date'          => $dateStr,
                'start_time'    => $gameDate->format('H:i:s'),
                'opponent'      => $game->opponent_name,
                'location'      => $game->location,
                'squad'         => $game->squad?->name,
                'score'         => $this->formatGameScore($game),
                'role'          => $role,
                'raw_game'      => $game,
                'is_past'       => $gameDate->isPast(),
            ];
        }

        usort($sessions, fn($a, $b) => strcmp($a['date'] . $a['start_time'], $b['date'] . $b['start_time']));

        return response()->json(['status' => 'success', 'data' => $sessions]);
    }

    private function getTrainingsForUser(User $user)
    {
        $query = Training::with(['location', 'team', 'squad.users', 'coach']);

        if ($user->role === 'antrenor') {
            $query->where('coach_id', $user->id);
        } elseif ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        } elseif ($user->role === 'administrator') {
            // all
        } else {
            $squadIds = $user->squads()->pluck('squads.id');
            $query->whereIn('squad_id', $squadIds);
        }

        return $query->get();
    }

    private function getGamesForUser(User $user)
    {
        $query = Game::with(['squad', 'players']);

        if ($user->role === 'antrenor') {
            $query->where('club_id', $user->club_id);
        } elseif ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        } elseif ($user->role === 'administrator') {
            // all
        } else {
            $squadIds = $user->squads()->pluck('squads.id');
            $query->whereIn('squad_id', $squadIds);
        }

        return $query->get();
    }

    private function formatGameScore(Game $game)
    {
        $home = 0;
        $away = 0;
        for ($i = 1; $i <= 5; $i++) {
            $h = $game->{"set{$i}_home"};
            $a = $game->{"set{$i}_away"};
            if ($h === null || $a === null) continue;
            if ($h > $a) $home++; else if ($a > $h) $away++;
        }
        if ($home === 0 && $away === 0) return null;
        return "$home - $away";
    }

    private function nextOccurrence(Carbon $from, string $dayOfWeek): Carbon
    {
        $map = [
            'luni'     => Carbon::MONDAY,
            'marti'    => Carbon::TUESDAY,
            'miercuri' => Carbon::WEDNESDAY,
            'joi'      => Carbon::THURSDAY,
            'vineri'   => Carbon::FRIDAY,
            'sambata'  => Carbon::SATURDAY,
            'duminica' => Carbon::SUNDAY,
        ];

        $target = $map[strtolower($dayOfWeek)] ?? Carbon::MONDAY;
        $date   = $from->copy();

        if ($date->dayOfWeek === $target) {
            return $date;
        }

        return $date->next($target);
    }
}