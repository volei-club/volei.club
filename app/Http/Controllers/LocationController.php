<?php

namespace App\Http\Controllers;

use App\Services\ClubService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $clubService;

    public function __construct(ClubService $clubService)
    {
        $this->clubService = $clubService;
    }

    public function index(Request $request)
    {
        $locations = $this->clubService->listLocations($request);
        return response()->json($locations);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['administrator', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'club_id' => $user->role === 'administrator' ? 'required|uuid|exists:clubs,id' : 'nullable',
        ]);

        if ($user->role === 'manager') {
            $validated['club_id'] = $user->club_id;
        }

        $location = $this->clubService->createLocation($validated);
        return response()->json($location, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $location = $this->clubService->getLocationById($id);

        if (!in_array($user->role, ['administrator', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role === 'manager' && $location->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        $updatedLocation = $this->clubService->updateLocation($location, $validated);
        return response()->json($updatedLocation);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $location = $this->clubService->getLocationById($id);

        if (!in_array($user->role, ['administrator', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->role === 'manager' && $location->club_id !== $user->club_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $error = $this->clubService->canDeleteLocation($location);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        $location->delete();
        return response()->json(null, 204);
    }
}
