<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Location::with('club');

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role === 'administrator') {
            if ($request->filled('club_id')) {
                $query->where('club_id', $request->club_id);
            }
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'club_id' => $user->role === 'administrator' ? 'required|uuid|exists:clubs,id' : 'nullable',
        ]);

        if ($user->role === 'manager') {
            $validated['club_id'] = $user->club_id;
        }

        $location = Location::create($validated);
        return response()->json($location, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $location = Location::findOrFail($id);

        if ($user->role === 'manager' && $location->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $location->update($validated);
        return response()->json($location);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $location = Location::findOrFail($id);

        if ($user->role === 'manager' && $location->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (\App\Models\Training::where('location_id', $id)->exists()) {
            return response()->json(['message' => 'Această locație este folosită de unul sau mai multe antrenamente și nu poate fi ștearsă.'], 422);
        }

        $location->delete();
        return response()->json(null, 204);
    }
}
