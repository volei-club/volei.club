<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;
use App\Services\ClubService;

class ClubController extends Controller
{
    protected $clubService;

    public function __construct(ClubService $clubService)
    {
        $this->clubService = $clubService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $clubs = $this->clubService->listClubs($request);

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
        if ($request->user()->role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $club = $this->clubService->createClub($validated, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Club creat cu succes!',
            'data' => $club->load('creator')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $club = $this->clubService->getClubById($id);

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
        $club = $this->clubService->getClubById($id);

        if ($request->user()->role !== 'administrator') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $updatedClub = $this->clubService->updateClub($club, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Club actualizat!',
            'data' => $updatedClub
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

        $club = $this->clubService->getClubById($id);

        $error = $this->clubService->canDeleteClub($club);
        if ($error) {
            return response()->json([
                'status' => 'error',
                'message' => $error
            ], 422);
        }

        $club->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Club șters cu succes!'
        ]);
    }
}
