<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;

        // Dacă e administrator, vede tot. Altfel, doar clubul de care aparține
        if ($role === 'administrator') {
            $clubs = Club::with('creator')->latest()->get();
        }
        else {
            $clubs = Club::where('id', $request->user()->club_id)->with('creator')->latest()->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $clubs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Un club poate fi creat ideal doar de un administrator
        if ($request->user()->role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $club = Club::create([
            'name' => $validated['name'],
            'created_by' => $request->user()->id
        ]);

        $club->load('creator');

        return response()->json([
            'status' => 'success',
            'message' => 'Club creat cu succes!',
            'data' => $club
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $club = Club::findOrFail($id);

        if ($request->user()->role !== 'administrator' && $request->user()->club_id !== $club->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $club
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $club = Club::findOrFail($id);

        if ($request->user()->role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $club->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Club actualizat!',
            'data' => $club
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $club = Club::findOrFail($id);

        // Nu permitem ștergerea dacă există membri înrolați
        if ($club->users()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acest club nu poate fi șters deoarece are utilizatori asociați. Transferați sau ștergeți utilizatorii mai întâi.'
            ], 422);
        }

        $club->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Club șters cu succes!'
        ]);
    }
}
