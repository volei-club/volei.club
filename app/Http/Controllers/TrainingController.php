<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Location;
use App\Models\Team;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Training::with(['club', 'location', 'team', 'squad', 'coach']);

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role !== 'administrator') {
            return response()->json(['message' => 'Unauthorized'], 403);
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

        return response()->json($query->orderBy('day_of_week')->orderBy('start_time')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'club_id' => $user->role === 'administrator' ? 'required|uuid|exists:clubs,id' : 'nullable',
            'location_id' => 'required|uuid|exists:locations,id',
            'team_id' => 'nullable|uuid|exists:teams,id',
            'squad_id' => 'required|uuid|exists:squads,id',
            'coach_id' => 'required|uuid|exists:users,id',
            'day_of_week' => ['required', Rule::in(['luni', 'marti', 'miercuri', 'joi', 'vineri', 'sambata', 'duminica'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($user->role === 'manager') {
            $validated['club_id'] = $user->club_id;
        }

        $club_id = $validated['club_id'];

        // Automatically set team_id from squad
        $squad = Squad::where('id', $validated['squad_id'])->whereHas('team', function ($q) use ($club_id) {
            $q->where('club_id', $club_id);
        })->first();

        if (!$squad) {
            return response()->json(['message' => 'Echipa nu există sau nu aparține clubului selectat.'], 422);
        }

        $validated['team_id'] = $squad->team_id;

        // Validate that location and coach belong to the same club
        $location = Location::where('id', $validated['location_id'])->where('club_id', $club_id)->first();
        $coach = User::where('id', $validated['coach_id'])->where('club_id', $club_id)->first();

        if (!$location || !$coach) {
            return response()->json(['message' => 'Locația sau antrenorul nu aparțin clubului selectat.'], 422);
        }

        $training = Training::create($validated);
        return response()->json($training->load(['club', 'location', 'team', 'squad', 'coach']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $training = Training::findOrFail($id);

        if ($user->role === 'manager' && $training->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'club_id' => $user->role === 'administrator' ? 'nullable|uuid|exists:clubs,id' : 'nullable',
            'location_id' => 'required|uuid|exists:locations,id',
            'team_id' => 'nullable|uuid|exists:teams,id',
            'squad_id' => 'required|uuid|exists:squads,id',
            'coach_id' => 'required|uuid|exists:users,id',
            'day_of_week' => ['required', Rule::in(['luni', 'marti', 'miercuri', 'joi', 'vineri', 'sambata', 'duminica'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $club_id = ($user->role === 'administrator' && !empty($validated['club_id'])) ? $validated['club_id'] : $training->club_id;

        // Automatically set team_id from squad
        $squad = Squad::where('id', $validated['squad_id'])->whereHas('team', function ($q) use ($club_id) {
            $q->where('club_id', $club_id);
        })->first();

        if (!$squad) {
            return response()->json(['message' => 'Echipa nu există sau nu aparține clubului selectat.'], 422);
        }

        $validated['team_id'] = $squad->team_id;
        $validated['club_id'] = $club_id;

        // Validate entities
        $location = Location::where('id', $validated['location_id'])->where('club_id', $club_id)->first();
        $coach = User::where('id', $validated['coach_id'])->where('club_id', $club_id)->first();

        if (!$location || !$coach) {
            return response()->json(['message' => 'Locația sau antrenorul nu aparțin clubului.'], 422);
        }

        $training->update($validated);
        return response()->json($training->load(['club', 'location', 'team', 'squad', 'coach']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $training = Training::findOrFail($id);

        if ($user->role === 'manager' && $training->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $training->delete();
        return response()->json(null, 204);
    }
}
