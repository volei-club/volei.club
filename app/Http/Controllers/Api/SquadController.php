<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Squad;
use Illuminate\Http\Request;

class SquadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;
        $query = Squad::with(['team', 'team.club', 'creator']);

        if ($role === 'manager') {
            $query->whereHas('team', function ($q) use ($request) {
                $q->where('club_id', $request->user()->club_id);
            });
        }
        elseif (in_array($role, ['antrenor', 'sportiv'])) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            });
        }
        elseif ($role === 'parinte') {
            $query->whereHas('users', function ($q) use ($request) {
                $q->whereHas('parents', function ($pq) use ($request) {
                        $pq->where('parent_id', $request->user()->id);
                    }
                    );
                });
        }
        elseif ($role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        // Filtru opțional de listare UI bazat strict pe Club (for admins)
        if ($request->filled('club_id')) {
            $query->whereHas('team', function ($q) use ($request) {
                $q->where('club_id', $request->club_id);
            });
        }

        // Filtru opțional de listare UI bazat pe Grupă (Team)
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 50);
        $paginator = $query->with('users')->latest()->paginate($perPage);

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
            // Managerul poate adăuga o echipă doar dacă Grupa aparține clubului său
            $validTeam = \App\Models\Team::where('id', $validated['team_id'])
                ->where('club_id', $request->user()->club_id)
                ->exists();

            if (!$validTeam) {
                return response()->json(['status' => 'error', 'message' => 'Eroare: Grupa selectată nu aparține clubului tău.'], 403);
            }
        }

        $validated['created_by'] = $request->user()->id;
        $squad = Squad::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Echipă adăugată cu succes!',
            'data' => $squad->load(['team', 'team.club'])
        ], 201);
    }

    public function show(string $id)
    {
        $squad = Squad::with(['users' => function ($q) {
            $q->where('role', 'sportiv');
        }])->findOrFail($id);

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

        $squad = Squad::findOrFail($id);

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
            $validTeam = \App\Models\Team::where('id', $validated['team_id'])
                ->where('club_id', $request->user()->club_id)
                ->exists();

            if (!$validTeam) {
                return response()->json(['status' => 'error', 'message' => 'Eroare: Noua grupă selectată nu aparține clubului tău.'], 403);
            }
        }

        $squad->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Echipă actualizată!',
            'data' => $squad->load(['team', 'team.club'])
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

        $squad = Squad::findOrFail($id);

        if ($role !== 'administrator') {
            if ($squad->team->club_id !== $request->user()->club_id) {
                return response()->json(['status' => 'error', 'message' => 'Vă este permis să ștergeți doar echipele clubului dumneavoastră.'], 403);
            }
        }

        if ($squad->users()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Această echipă are jucători asociați. Pentru siguranță, eliminați membrii înainte de ștergere.'
            ], 422);
        }

        $squad->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Echipă ștearsă!',
        ]);
    }
}
