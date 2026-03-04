<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Squad;
use App\Services\TeamSquadService;
use Illuminate\Http\Request;

class SquadController extends Controller
{
    protected $teamSquadService;

    public function __construct(TeamSquadService $teamSquadService)
    {
        $this->teamSquadService = $teamSquadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!in_array($request->user()->role, ['administrator', 'manager', 'antrenor', 'sportiv', 'parinte'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $paginator = $this->teamSquadService->listSquads($request);

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role = $request->user()->role;

        if (!in_array($role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
        ]);

        if ($role !== 'administrator') {
            if (!$this->teamSquadService->teamBelongsToClub($validated['team_id'], $request->user()->club_id)) {
                return response()->json(['status' => 'error', 'message' => 'Eroare: Grupa selectată nu aparține clubului tău.'], 403);
            }
        }

        $validated['created_by'] = $request->user()->id;
        $squad = $this->teamSquadService->createSquad($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Echipă adăugată cu succes!',
            'data' => $squad->load(['team', 'team.club'])
        ], 201);
    }

    public function show(string $id)
    {
        $squad = $this->teamSquadService->getSquadById($id, ['users' => function ($q) {
            $q->where('role', 'sportiv');
        }]);

        return response()->json($squad);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = $request->user()->role;

        if (!in_array($role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $squad = $this->teamSquadService->getSquadById($id);

        if ($role !== 'administrator') {
            if ($squad->team->club_id !== $request->user()->club_id) {
                return response()->json(['status' => 'error', 'message' => 'Vă este permis să editați doar echipele clubului dumneavoastră.'], 403);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
        ]);

        if ($role !== 'administrator') {
            if (!$this->teamSquadService->teamBelongsToClub($validated['team_id'], $request->user()->club_id)) {
                return response()->json(['status' => 'error', 'message' => 'Eroare: Noua grupă selectată nu aparține clubului tău.'], 403);
            }
        }

        $updatedSquad = $this->teamSquadService->updateSquad($squad, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Echipă actualizată!',
            'data' => $updatedSquad->load(['team', 'team.club'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $role = $request->user()->role;

        if (!in_array($role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $squad = $this->teamSquadService->getSquadById($id);

        if ($role !== 'administrator') {
            if ($squad->team->club_id !== $request->user()->club_id) {
                return response()->json(['status' => 'error', 'message' => 'Vă este permis să ștergeți doar echipele clubului dumneavoastră.'], 403);
            }
        }

        $error = $this->teamSquadService->canDeleteSquad($squad);
        if ($error) {
            return response()->json([
                'status' => 'error',
                'message' => $error
            ], 422);
        }

        $squad->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Echipă ștearsă!',
        ]);
    }
}
