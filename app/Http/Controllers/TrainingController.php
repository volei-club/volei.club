<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Location;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Training::with(['club', 'location', 'team', 'coach']);

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

        return response()->json($query->orderBy('day_of_week')->orderBy('start_time')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'club_id' => $user->role === 'administrator' ? 'required|uuid|exists:clubs,id' : 'nullable',
            'location_id' => 'required|uuid|exists:locations,id',
            'team_id' => 'required|uuid|exists:teams,id',
            'coach_id' => 'required|uuid|exists:users,id',
            'day_of_week' => ['required', Rule::in(['luni', 'marti', 'miercuri', 'joi', 'vineri', 'sambata', 'duminica'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($user->role === 'manager') {
            $validated['club_id'] = $user->club_id;
        }

        $club_id = $validated['club_id'];

        // Validate that location, team, and coach belong to the same club
        $location = Location::where('id', $validated['location_id'])->where('club_id', $club_id)->first();
        $team = Team::where('id', $validated['team_id'])->where('club_id', $club_id)->first();
        $coach = User::where('id', $validated['coach_id'])->where('club_id', $club_id)->first();

        if (!$location || !$team || !$coach) {
            return response()->json(['message' => 'One or more entities do not belong to the selected club.'], 422);
        }

        $training = Training::create($validated);
        return response()->json($training->load(['club', 'location', 'team', 'coach']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $training = Training::findOrFail($id);

        if ($user->role === 'manager' && $training->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'location_id' => 'required|uuid|exists:locations,id',
            'team_id' => 'required|uuid|exists:teams,id',
            'coach_id' => 'required|uuid|exists:users,id',
            'day_of_week' => ['required', Rule::in(['luni', 'marti', 'miercuri', 'joi', 'vineri', 'sambata', 'duminica'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $club_id = $training->club_id;

        // Validate entities
        $location = Location::where('id', $validated['location_id'])->where('club_id', $club_id)->first();
        $team = Team::where('id', $validated['team_id'])->where('club_id', $club_id)->first();
        $coach = User::where('id', $validated['coach_id'])->where('club_id', $club_id)->first();

        if (!$location || !$team || !$coach) {
            return response()->json(['message' => 'One or more entities do not belong to the club.'], 422);
        }

        $training->update($validated);
        return response()->json($training->load(['club', 'location', 'team', 'coach']));
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
