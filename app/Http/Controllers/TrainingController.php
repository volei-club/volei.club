<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Services\TeamSquadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingController extends Controller
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
        $trainings = $this->eventService->listTrainings($request);
        if ($trainings === null) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($trainings);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['administrator', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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

        $club_id = $user->role === 'administrator' ? $validated['club_id'] : $user->club_id;
        $validated['club_id'] = $club_id;

        $error = $this->eventService->validateClubOwnership($club_id, $validated['squad_id'], $validated['location_id'], $validated['coach_id']);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $squad = $this->teamSquadService->getSquadById($validated['squad_id']);
        $validated['team_id'] = $squad->team_id;

        $training = $this->eventService->saveTraining($validated);
        return response()->json($training->load(['club', 'location', 'team', 'squad', 'coach']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $training = $this->eventService->getTrainingById($id);

        if ($user->role === 'manager' && $training->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($user->role !== 'administrator' && $user->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
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
        $validated['club_id'] = $club_id;

        $error = $this->eventService->validateClubOwnership($club_id, $validated['squad_id'], $validated['location_id'], $validated['coach_id']);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $squad = $this->teamSquadService->getSquadById($validated['squad_id']);
        $validated['team_id'] = $squad->team_id;

        $updatedTraining = $this->eventService->saveTraining($validated, $training);
        return response()->json($updatedTraining->load(['club', 'location', 'team', 'squad', 'coach']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $training = $this->eventService->getTrainingById($id);

        if ($user->role === 'manager' && $training->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($user->role !== 'administrator' && $user->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $training->delete();
        return response()->json(null, 204);
    }
}
