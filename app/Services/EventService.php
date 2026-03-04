<?php

namespace App\Services;

use App\Models\Training;
use App\Models\Game;
use App\Models\Squad;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventService
{
    /**
     * List trainings with filtering.
     */
    public function listTrainings(Request $request)
    {
        $user = $request->user();
        $query = Training::with(['club', 'location', 'team', 'squad', 'coach']);

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role !== 'administrator') {
            return null; // Should be handled by controller 403
        }

        if ($request->has('club_id') && $user->role === 'administrator') {
            $query->where('club_id', $request->club_id);
        }

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('squad_id')) {
            $query->where('squad_id', $request->squad_id);
        }

        return $query->orderBy('day_of_week')->orderBy('start_time')->get();
    }

    /**
     * Create or update a training.
     */
    public function saveTraining(array $data, ?Training $training = null)
    {
        if ($training) {
            $training->update($data);
            return $training;
        }
        return Training::create($data);
    }

    /**
     * List games with filtering.
     */
    public function listGames(Request $request)
    {
        $user = $request->user();
        $query = Game::with(['club', 'team', 'squad', 'players']);

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif (in_array($user->role, ['antrenor', 'sportiv'])) {
            $squadIds = $user->squads()->pluck('squads.id');
            $query->whereIn('squad_id', $squadIds);
        }
        elseif ($user->role === 'parinte') {
            $squadIds = $user->children()->with('squads')->get()->pluck('squads.*.id')->flatten()->unique();
            $query->whereIn('squad_id', $squadIds);
        }
        elseif ($user->role !== 'administrator') {
            return null;
        }

        if ($request->filled('squad_id')) {
            $query->where('squad_id', $request->squad_id);
        }

        return $query->orderBy('match_date', 'desc')->get();
    }

    /**
     * Create or update a game.
     */
    public function saveGame(array $data, ?Game $game = null)
    {
        $starters = $data['starters'] ?? [];
        $substitutes = $data['substitutes'] ?? [];
        unset($data['starters'], $data['substitutes']);

        if ($game) {
            $game->update($data);
        }
        else {
            $game = Game::create($data);
        }

        $this->syncPlayers($game, $starters, $substitutes);
        return $game;
    }

    /**
     * Sync players to a game.
     */
    private function syncPlayers(Game $game, array $starters, array $substitutes)
    {
        $syncData = [];
        foreach ($starters as $id) {
            $syncData[$id] = ['type' => 'titular'];
        }
        foreach ($substitutes as $id) {
            if (!isset($syncData[$id])) {
                $syncData[$id] = ['type' => 'rezerva'];
            }
        }
        $game->players()->sync($syncData);
    }

    /**
     * Validate cross-model club ownership.
     */
    public function validateClubOwnership(string $clubId, string $squadId, string $locationId, string $coachId): ?string
    {
        $squad = Squad::where('id', $squadId)->whereHas('team', fn($q) => $q->where('club_id', $clubId))->first();
        if (!$squad)
            return 'Echipa nu există sau nu aparține clubului selectat.';

        $location = Location::where('id', $locationId)->where('club_id', $clubId)->first();
        if (!$location)
            return 'Locația nu aparține clubului selectat.';

        $coach = User::where('id', $coachId)->where('club_id', $clubId)->first();
        if (!$coach)
            return 'Antrenorul nu aparține clubului selectat.';

        return null;
    }
}
