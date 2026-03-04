<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Squad;
use App\Services\EventService;
use App\Services\TeamSquadService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $eventService;
    protected $teamSquadService;

    public function __construct(EventService $eventService, TeamSquadService $teamSquadService)
    {
        $this->eventService = $eventService;
        $this->teamSquadService = $teamSquadService;
    }

    public function index(Request $request)
    {
        $games = $this->eventService->listGames($request);
        if ($games === null) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($games);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $squad = $this->teamSquadService->getSquadById($request->squad_id, ['team']);

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

        $game = $this->eventService->saveGame($validated);

        return response()->json($game->load(['players']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $game = $this->eventService->getGameById($id);

        if ($user->role === 'manager' && $game->club_id !== $user->club_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->has('squad_id')) {
            $squad = $this->teamSquadService->getSquadById($request->squad_id, ['team']);
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

        $updatedGame = $this->eventService->saveGame($validated, $game);

        return response()->json($updatedGame->load(['players']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager', 'antrenor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $game = $this->eventService->getGameById($id);

        if ($user->role === 'manager' && $game->club_id !== $user->club_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $game->delete();
        return response()->json(null, 204);
    }
}
