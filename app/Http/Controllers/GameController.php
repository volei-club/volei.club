<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Squad;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
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
        elseif ($user->role === 'administrator') {
        // Admin sees everything
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->filled('squad_id')) {
            $query->where('squad_id', $request->squad_id);
        }

        return response()->json($query->orderBy('match_date', 'desc')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $squad = Squad::with('team')->findOrFail($request->squad_id);

        $request->merge([
            'team_id' => $squad->team_id,
            'club_id' => $squad->team->club_id,
        ]);

        $validated = $request->validate([
            'club_id' => 'required|uuid|exists:clubs,id',
            'team_id' => 'required|uuid|exists:teams,id',
            'squad_id' => 'required|uuid|exists:squads,id',
            'opponent_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'match_date' => 'required|date',
            'set1_home' => 'nullable|integer',
            'set1_away' => 'nullable|integer',
            'set2_home' => 'nullable|integer',
            'set2_away' => 'nullable|integer',
            'set3_home' => 'nullable|integer',
            'set3_away' => 'nullable|integer',
            'set4_home' => 'nullable|integer',
            'set4_away' => 'nullable|integer',
            'set5_home' => 'nullable|integer',
            'set5_away' => 'nullable|integer',
            'notes' => 'nullable|string',
            'starters' => 'array',
            'starters.*' => 'uuid|exists:users,id',
            'substitutes' => 'array',
            'substitutes.*' => 'uuid|exists:users,id',
        ]);

        $gameData = collect($validated)->except(['starters', 'substitutes'])->toArray();
        $game = Game::create($gameData);
        $this->syncPlayers($game, $validated['starters'] ?? [], $validated['substitutes'] ?? []);

        return response()->json($game->load(['players']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $game = Game::findOrFail($id);

        if ($user->role === 'manager' && $game->club_id !== $user->club_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->has('squad_id')) {
            $squad = Squad::with('team')->findOrFail($request->squad_id);
            $request->merge([
                'team_id' => $squad->team_id,
                'club_id' => $squad->team->club_id,
            ]);
        }

        $validated = $request->validate([
            'club_id' => 'sometimes|uuid|exists:clubs,id',
            'team_id' => 'sometimes|uuid|exists:teams,id',
            'squad_id' => 'required|uuid|exists:squads,id',
            'opponent_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'match_date' => 'required|date',
            'set1_home' => 'nullable|integer',
            'set1_away' => 'nullable|integer',
            'set2_home' => 'nullable|integer',
            'set2_away' => 'nullable|integer',
            'set3_home' => 'nullable|integer',
            'set3_away' => 'nullable|integer',
            'set4_home' => 'nullable|integer',
            'set4_away' => 'nullable|integer',
            'set5_home' => 'nullable|integer',
            'set5_away' => 'nullable|integer',
            'notes' => 'nullable|string',
            'starters' => 'array',
            'starters.*' => 'uuid|exists:users,id',
            'substitutes' => 'array',
            'substitutes.*' => 'uuid|exists:users,id',
        ]);

        $gameData = collect($validated)->except(['starters', 'substitutes'])->toArray();
        $game->update($gameData);
        $this->syncPlayers($game, $validated['starters'] ?? [], $validated['substitutes'] ?? []);

        return response()->json($game->load(['players']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $game = Game::findOrFail($id);

        if ($user->role === 'manager' && $game->club_id !== $user->club_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->delete();
        return response()->json(null, 204);
    }

    private function syncPlayers(Game $game, array $starters, array $substitutes)
    {
        $syncData = [];
        foreach ($starters as $id) {
            $syncData[$id] = ['type' => 'titular'];
        }
        foreach ($substitutes as $id) {
            // Priority to titular if duplicated for some reason, but they shouldn't be.
            if (!isset($syncData[$id])) {
                $syncData[$id] = ['type' => 'rezerva'];
            }
        }
        $game->players()->sync($syncData);
    }
}
