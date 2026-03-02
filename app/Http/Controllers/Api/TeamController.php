<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;
        $query = Team::query();

        if ($role !== 'administrator') {
            $query->where('club_id', $request->user()->club_id);
        }
        else {
            if ($request->filled('club_id')) {
                $query->where('club_id', $request->club_id);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->latest()->get()
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

        // Administratorii trebuie să trimită club_id ca să știe unde adaugă grupa.
        // Managerii vor moșteni automat propriul club.
        if ($role === 'administrator') {
            $rules['club_id'] = 'required|exists:clubs,id';
        }

        $validated = $request->validate($rules);

        if ($role !== 'administrator') {
            $validated['club_id'] = $request->user()->club_id;
        }

        $team = Team::create($validated);

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
        $team = Team::findOrFail($id);

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

        $team = Team::findOrFail($id);

        if ($request->user()->role !== 'administrator' && $team->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Grupa a fost actualizată!',
            'data' => $team
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

        $team = Team::findOrFail($id);

        if ($request->user()->role !== 'administrator' && $team->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Dacă grupa are jucători alocați, nu o ștergem direct pentru a nu lăsa utilizatori "orfani".
        if ($team->users()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Această grupă are jucători sau antrenori asociați. Pentru siguranță, eliminați membrii înainte de ștergere.'
            ], 422);
        }

        if ($team->squads()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Această grupă are sub-echipe asociate. Ștergeți-le mai întâi.'
            ], 422);
        }

        if (\App\Models\Training::where('team_id', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Această grupă are antrenamente programate. Ștergeți antrenamentele mai întâi.'
            ], 422);
        }

        $team->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Grupa a fost ștearsă.'
        ]);
    }
}
