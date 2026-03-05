<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Game;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Get attendance for a training session on a specific date.
     */
    public function getAttendance(Training $training, string $date, User $user)
    {
        $squad = $training->squad;
        if (!$squad) {
            return [];
        }

        $athletes = $squad->users()->where('role', 'sportiv')->get();
        $attendances = Attendance::where('training_id', $training->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('user_id');

        return $athletes->map(function ($athlete) use ($attendances) {
            $att = $attendances->get($athlete->id);
            return [
                'user_id' => $athlete->id,
                'name' => $athlete->name,
                'photo' => $athlete->photo,
                'status' => $att?->status,
                'attendance_id' => $att?->id,
                'notes' => $att?->notes,
            ];
        });
    }

    /**
     * Mark or update attendance for an athlete.
     */
    public function markAttendance(array $data, User $markedBy)
    {
        return Attendance::updateOrCreate(
        [
            'training_id' => $data['training_id'],
            'user_id' => $data['user_id'],
            'date' => $data['date'],
        ],
        [
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'marked_by' => $markedBy->id,
        ]);
    }

    /**
     * Generate calendar sessions for a user (trainings and games).
     */
    public function generateCalendar(User $user, int $weeks = 4, ?string $startDateStr = null)
    {
        $startDate = $startDateStr ? Carbon::parse($startDateStr)->startOfDay() : now()->startOfDay();
        $endDate = $startDate->copy()->addWeeks($weeks)->endOfDay();
        $period = CarbonPeriod::create($startDate, $endDate);

        $trainingQuery = Training::with(['location', 'team', 'squad', 'cancellations']);
        $gameQuery = Game::whereBetween('match_date', [$startDate, $endDate->format('Y-m-d')])
            ->with(['team', 'squad', 'players']);

        if ($user->role === 'administrator') {
            // Admin sees everything
        } elseif ($user->role === 'manager') {
            $trainingQuery->where('club_id', $user->club_id);
            $gameQuery->where('club_id', $user->club_id);
        } elseif ($user->role === 'antrenor') {
            // Coaches see sessions they are assigned to, and games for squads they oversee
            $squadIds = $user->squads()->pluck('squads.id');
            $trainingQuery->where(function ($q) use ($user, $squadIds) {
                $q->where('coach_id', $user->id)
                  ->orWhereIn('squad_id', $squadIds);
            });
            $gameQuery->whereIn('squad_id', $squadIds);
        } else {
            // Sportiv / Parinte (already handled in controller if parent)
            $squadIds = $user->squads()->pluck('squads.id');
            $trainingQuery->whereIn('squad_id', $squadIds);
            $gameQuery->whereIn('squad_id', $squadIds);
        }

        $trainings = $trainingQuery->get();
        $games = $gameQuery->get();

        // Get attendances for this user in the date range (if sportiv)
        $attendances = collect();
        if ($user->role === 'sportiv') {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get()
                ->groupBy(fn($a) => $a->training_id . '_' . ($a->date instanceof Carbon ? $a->date->format('Y-m-d') : $a->date));
        }

        $sessions = [];

        // Romanian day names mapping to Carbon dayOfWeek (0=Sun, 1=Mon...)
        // Includes versions with and without diacritics to be robust
        $dayMap = [
            'duminica' => 0, 'duminică' => 0,
            'luni'     => 1,
            'marti'    => 2, 'marți'    => 2,
            'miercuri' => 3,
            'joi'      => 4,
            'vineri'   => 5,
            'sambata'  => 6, 'sâmbătă'  => 6,
        ];

        // Generate training instances
        foreach ($period as $date) {
            $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)
            $dateStr = $date->format('Y-m-d');

            $todaysTrainings = $trainings->filter(function ($t) use ($dayOfWeek, $dayMap, $dateStr) {
                $storedDay = mb_strtolower(trim($t->day_of_week));
                $matchesDay = isset($dayMap[$storedDay]) && $dayMap[$storedDay] === $dayOfWeek;

                if (!$matchesDay) {
                    return false;
                }

                // Check date bounds
                $tStartDate = $t->start_date instanceof Carbon ? $t->start_date->format('Y-m-d') : $t->start_date;
                $tEndDate = $t->end_date instanceof Carbon ? $t->end_date->format('Y-m-d') : $t->end_date;

                if ($tStartDate && $dateStr < $tStartDate) {
                    return false;
                }
                if ($tEndDate && $dateStr > $tEndDate) {
                    return false;
                }

                return true;
            });

            foreach ($todaysTrainings as $training) {
                // Check if cancelled
                $cancellation = $training->cancellations->first(function($c) use ($dateStr) {
                    $cDate = $c->date instanceof Carbon ? $c->date->format('Y-m-d') : $c->date;
                    return $cDate === $dateStr;
                });

                $attKey = $training->id . '_' . $dateStr;
                $userAttendance = $attendances->get($attKey)?->first();

                $sessions[] = [
                    'id' => 'training_' . $training->id . '_' . $dateStr,
                    'type' => 'training',
                    'title' => 'Antrenament ' . ($training->squad->name ?? $training->team->name),
                    'start' => $dateStr . ' ' . $training->start_time,
                    'start_time' => $training->start_time,
                    'end' => $dateStr . ' ' . $training->end_time,
                    'end_time' => $training->end_time,
                    'location' => $training->location->name ?? 'Nespecificat',
                    'training_id' => $training->id,
                    'is_cancelled' => (bool)$cancellation,
                    'cancellation_reason' => $cancellation?->reason,
                    'squad' => $training->squad->name ?? null,
                    'team' => $training->team->name ?? null,
                    'date' => $dateStr,
                    'status' => $userAttendance?->status,
                    'attendance_id' => $userAttendance?->id,
                ];
            }
        }

        // Add games
        foreach ($games as $game) {
            $startTime = $game->match_date->format('H:i');
            // Assuming games last roughly 2 hours for calendar visualization if end_time not in DB
            $endTime = $game->match_date->copy()->addHours(2)->format('H:i');

            $sessions[] = [
                'id' => 'game_' . $game->id,
                'type' => 'game',
                'title' => 'Meci: ' . ($game->opponent_name ?? 'Adversar necunoscut'),
                'start' => $game->match_date->format('Y-m-d') . ' ' . $startTime,
                'start_time' => $startTime,
                'end' => $game->match_date->format('Y-m-d') . ' ' . $endTime,
                'end_time' => $endTime,
                'location' => $game->location ?? 'Nespecificat',
                'game_id' => $game->id,
                'opponent' => $game->opponent_name,
                'score' => $this->formatGameScore($game),
                'date' => $game->match_date->format('Y-m-d'),
                'raw_game' => $game,
            ];
        }

        // Sort by start time
        usort($sessions, fn($a, $b) => strcmp($a['start'], $b['start']));

        return $sessions;
    }

    /**
     * Format the game score for the calendar view.
     */
    protected function formatGameScore(Game $game)
    {
        $sets = [];
        $homeSets = 0;
        $awaySets = 0;

        for ($i = 1; $i <= 5; $i++) {
            $home = $game->{"set{$i}_home"};
            $away = $game->{"set{$i}_away"};
            if ($home !== null && $away !== null) {
                $sets[] = "$home-$away";
                if ($home > $away) {
                    $homeSets++;
                } elseif ($away > $home) {
                    $awaySets++;
                }
            }
        }

        if (empty($sets)) {
            return null;
        }

        return "$homeSets-$awaySets";
    }

    /**
     * Get a training by ID, with optional eager-loaded relations.
     */
    public function getTrainingById(string $id, array $relations = []): Training
    {
        $query = Training::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        return $query->findOrFail($id);
    }

    /**
     * Get an attendance record by ID.
     */
    public function getAttendanceById(string $id): Attendance
    {
        return Attendance::findOrFail($id);
    }
}
