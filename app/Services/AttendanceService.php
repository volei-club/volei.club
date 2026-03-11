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

        $trainings = $trainingQuery->with('reschedules')->get();
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
        $dayMap = [
            'duminica' => 0,
            'luni'     => 1,
            'marti'    => 2,
            'miercuri' => 3,
            'joi'      => 4,
            'vineri'   => 5,
            'sambata'  => 6,
        ];

        // 1. Identify "Natural" series instances and check for reschedules
        foreach ($period as $date) {
            $dayOfWeek = $date->dayOfWeek;
            $dateStr = $date->format('Y-m-d');

            $activeTrainings = $trainings->filter(function ($t) use ($dayOfWeek, $dayMap, $dateStr) {
                $storedDay = mb_strtolower(trim($t->day_of_week));
                $matchesDay = isset($dayMap[$storedDay]) && $dayMap[$storedDay] === $dayOfWeek;
                if (!$matchesDay) return false;

                $tStartDate = $t->start_date instanceof Carbon ? $t->start_date->format('Y-m-d') : $t->start_date;
                $tEndDate = $t->end_date instanceof Carbon ? $t->end_date->format('Y-m-d') : $t->end_date;

                if ($tStartDate && $dateStr < $tStartDate) return false;
                if ($tEndDate && $dateStr > $tEndDate) return false;

                return true;
            });

            foreach ($activeTrainings as $training) {
                // Check if this specific instance ($dateStr) was rescheduled
                $reschedule = $training->reschedules->first(function($r) use ($dateStr) {
                    $rDate = $r->original_date instanceof Carbon ? $r->original_date->format('Y-m-d') : $r->original_date;
                    return $rDate === $dateStr;
                });
                
                if ($reschedule) {
                    $newDateStr = $reschedule->new_date instanceof Carbon ? $reschedule->new_date->format('Y-m-d') : $reschedule->new_date;
                    $origDateStr = $reschedule->original_date instanceof Carbon ? $reschedule->original_date->format('Y-m-d') : $reschedule->original_date;

                    // If moved to a DIFFERENT day, skip the original slot
                    if ($newDateStr !== $origDateStr) {
                        continue;
                    }
                    // If same day, we'll use updated times below
                    $startTime = $reschedule->new_start_time;
                    $endTime = $reschedule->new_end_time;
                } else {
                    $startTime = $training->start_time;
                    $endTime = $training->end_time;
                }

                /** @var Training $training */
                $sessions[] = $this->formatTrainingSession($training, $dateStr, $startTime, $endTime, $attendances);
            }
        }

        // 2. Add "Virtual" instances (Trainings moved TO a date in this range from another date)
        foreach ($trainings as $training) {
            foreach ($training->reschedules as $reschedule) {
                $newDate = $reschedule->new_date instanceof Carbon ? $reschedule->new_date : Carbon::parse($reschedule->new_date);
                $newDateStr = $newDate->format('Y-m-d');
                
                $origDate = $reschedule->original_date instanceof Carbon ? $reschedule->original_date : Carbon::parse($reschedule->original_date);
                $origDateStr = $origDate->format('Y-m-d');

                // If the new date is in our current period
                if ($newDate->between($startDate, $endDate)) {
                    // If it was moved FROM a different day, we have to add it as a new session
                    // (If it was same day, it's already handled in the loop above)
                    if ($newDateStr !== $origDateStr) {
                         $sessions[] = $this->formatTrainingSession($training, $newDateStr, $reschedule->new_start_time, $reschedule->new_end_time, $attendances);
                    }
                }
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
                'title' => __('calendar.game_title', ['name' => ($game->opponent_name ?? __('calendar.unknown_opponent'))]),
                'start' => $game->match_date->format('Y-m-d') . ' ' . $startTime,
                'start_time' => $startTime,
                'end' => $game->match_date->format('Y-m-d') . ' ' . $endTime,
                'end_time' => $endTime,
                'location' => $game->location ?? __('calendar.unspecified_location'),
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
     * Helper to format a training session for the calendar.
     */
    private function formatTrainingSession(Training $training, string $dateStr, string $startTime, string $endTime, $attendances): array
    {
        $cancellation = $training->cancellations->first(function($c) use ($dateStr) {
            $cDate = $c->date instanceof Carbon ? $c->date->format('Y-m-d') : $c->date;
            return $cDate === $dateStr;
        });

        $attKey = $training->id . '_' . $dateStr;
        $userAttendance = $attendances->get($attKey)?->first();

        $reschedule = $training->reschedules->first(function($r) use ($dateStr) {
            $rDate = $r->original_date instanceof Carbon ? $r->original_date->format('Y-m-d') : $r->original_date;
            return $rDate === $dateStr;
        });

        return [
            'id' => 'training_' . $training->id . '_' . $dateStr,
            'type' => 'training',
            'title' => __('calendar.training_title', ['name' => ($training->squad->name ?? $training->team->name)]),
            'start' => $dateStr . ' ' . $startTime,
            'start_time' => $startTime,
            'end' => $dateStr . ' ' . $endTime,
            'end_time' => $endTime,
            'location' => $training->location->name ?? __('calendar.unspecified_location'),
            'training_id' => $training->id,
            'is_cancelled' => (bool)$cancellation,
            'cancellation_reason' => $cancellation?->reason,
            'is_rescheduled' => (bool)$reschedule,
            'reschedule_reason' => $reschedule?->reason,
            'squad' => $training->squad->name ?? null,
            'team' => $training->team->name ?? null,
            'date' => $dateStr,
            'status' => $userAttendance?->status,
            'attendance_id' => $userAttendance?->id,
        ];
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
