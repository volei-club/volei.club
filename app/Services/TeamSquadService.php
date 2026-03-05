<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Squad;
use App\Models\User;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamSquadService
{
    /**
     * List teams with filtering.
     */
    public function listTeams(Request $request)
    {
        $user = $request->user();
        $query = Team::query();

        if ($user->role !== 'administrator') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('per_page', 50);
        return $query->with('users')->latest()->paginate($perPage);
    }

    /**
     * Create a new team.
     */
    public function createTeam(array $data)
    {
        return Team::create($data);
    }

    /**
     * Update a team.
     */
    public function updateTeam(Team $team, array $data)
    {
        $team->update($data);
        return $team;
    }

    /**
     * Check if a team can be deleted.
     */
    public function canDeleteTeam(Team $team): ?string
    {
        if ($team->users()->count() > 0) {
            return __('api_teams.delete_has_members');
        }

        if ($team->squads()->count() > 0) {
            return __('api_teams.delete_has_squads');
        }

        if (Training::where('team_id', $team->id)->exists()) {
            return __('api_teams.delete_has_trainings');
        }

        return null;
    }

    /**
     * List squads with complex filtering.
     */
    public function listSquads(Request $request)
    {
        $user = $request->user();
        $role = $user->role;
        $query = Squad::with(['team', 'team.club', 'creator']);

        if ($role === 'manager') {
            $query->whereHas('team', function ($q) use ($user) {
                $q->where('club_id', $user->club_id);
            });
        }
        elseif (in_array($role, ['antrenor', 'sportiv'])) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        }
        elseif ($role === 'parinte') {
            $query->whereHas('users', function ($q) use ($user) {
                $q->whereHas('parents', fn($pq) => $pq->where('parent_id', $user->id));
            });
        }
        elseif ($role !== 'administrator') {
            return null; // Should be handled by controller 403
        }

        if ($request->filled('club_id')) {
            $query->whereHas('team', fn($q) => $q->where('club_id', $request->club_id));
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('per_page', 50);
        return $query->with('users')->latest()->paginate($perPage);
    }

    /**
     * Create a new squad.
     */
    public function createSquad(array $data)
    {
        return Squad::create($data);
    }

    /**
     * Update a squad.
     */
    public function updateSquad(Squad $squad, array $data)
    {
        $squad->update($data);
        return $squad;
    }

    /**
     * Check if a squad can be deleted.
     */
    public function canDeleteSquad(Squad $squad): ?string
    {
        if ($squad->users()->count() > 0) {
            return __('api_squads.delete_has_members');
        }
        return null;
    }

    /**
     * Validate that all provided team IDs belong to a specific club.
     */
    public function validateTeamsBelongToClub(array $teamIds, string $clubId): bool
    {
        if (empty($teamIds)) {
            return true;
        }

        $validTeamsCount = Team::whereIn('id', $teamIds)
            ->where('club_id', $clubId)
            ->count();

        return $validTeamsCount === count($teamIds);
    }

    /**
     * Validate that all provided squad IDs belong to a specific set of teams.
     */
    public function validateSquadsBelongToTeams(array $squadIds, array $teamIds): bool
    {
        if (empty($squadIds)) {
            return true;
        }

        $validSquads = Squad::whereIn('id', $squadIds)->pluck('team_id')->unique()->toArray();

        return count(array_diff($validSquads, $teamIds)) === 0;
    }

    /**
     * Get a team by ID.
     */
    public function getTeamById(string $id, array $relations = []): Team
    {
        $query = Team::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        return $query->findOrFail($id);
    }

    /**
     * Get a squad by ID.
     */
    public function getSquadById(string $id, array $relations = []): Squad
    {
        $query = Squad::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        return $query->findOrFail($id);
    }

    /**
     * Check if a team belongs to a given club.
     */
    public function teamBelongsToClub(string $teamId, string $clubId): bool
    {
        return Team::where('id', $teamId)
            ->where('club_id', $clubId)
            ->exists();
    }

    /**
     * Get the unique team IDs that the given squads belong to.
     */
    public function getSquadTeamIds(array $squadIds): array
    {
        return Squad::whereIn('id', $squadIds)
            ->pluck('team_id')
            ->unique()
            ->toArray();
    }
}
