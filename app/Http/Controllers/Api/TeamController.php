<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Services\TeamSquadService;

class TeamController extends Controller
{
    protected $teamSquadService;

    public function __construct(TeamSquadService $teamSquadService)
    {
        $this->teamSquadService = $teamSquadService;
    }

    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        $paginator = $this->teamSquadService->listTeams($request);

        return response()->json([
            'status' => 'success',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $role = $request->user()->role;

        if (!in_array($role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($role === 'administrator') {
            $rules['club_id'] = 'required|exists:clubs,id';
        }

        $validated = $request->validate($rules);

        if ($role !== 'administrator') {
            $validated['club_id'] = $request->user()->club_id;
        }

        $team = $this->teamSquadService->createTeam($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Grupa a fost creată cu succes!',
            'data' => $team
        ], 201);
    }

    /**
     * Display the specified team.
     */
    public function show(Request $request, string $id)
    {
        $team = $this->teamSquadService->getTeamById($id);

        if ($request->user()->role !== 'administrator' && $team->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $team
        ]);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!in_array($request->user()->role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $team = $this->teamSquadService->getTeamById($id);

        if ($request->user()->role !== 'administrator' && $team->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $updatedTeam = $this->teamSquadService->updateTeam($team, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Grupa a fost actualizată!',
            'data' => $updatedTeam
        ]);
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if (!in_array($request->user()->role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $team = $this->teamSquadService->getTeamById($id);

        if ($request->user()->role !== 'administrator' && $team->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $error = $this->teamSquadService->canDeleteTeam($team);
        if ($error) {
            return response()->json([
                'status' => 'error',
                'message' => $error
            ], 422);
        }

        $team->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Grupa a fost ștearsă.'
        ]);
    }
}
