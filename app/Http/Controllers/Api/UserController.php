<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\TeamSquadService;

class UserController extends Controller
{
    protected $userService;
    protected $teamSquadService;

    public function __construct(UserService $userService, TeamSquadService $teamSquadService)
    {
        $this->userService = $userService;
        $this->teamSquadService = $teamSquadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!in_array($request->user()->role, ['administrator', 'manager', 'antrenor', 'parinte'])) {
            return response()->json(['status' => 'error', 'message' => __('api_users.forbidden')], 403);
        }

        $paginator = $this->userService->listUsers($request);

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
        $creator = $request->user();

        if (!in_array($creator->role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => __('api_users.forbidden')], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:administrator,manager,antrenor,parinte,sportiv',
            'club_id' => 'nullable|exists:clubs,id',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'squad_ids' => 'nullable|array',
            'squad_ids.*' => 'exists:squads,id',
            'child_ids' => 'nullable|array',
            'child_ids.*' => 'exists:users,id',
            'photo' => 'nullable|image|max:2048'
        ]);

        // Security rules
        if ($creator->role !== 'administrator') {
            if ($validated['role'] === 'administrator' || $validated['role'] === 'manager') {
                return response()->json(['status' => 'error', 'message' => __('api_users.invalid_role')], 403);
            }
            $validated['club_id'] = $creator->club_id;
        }

        // Squad/Team ownership validation
        if (!empty($validated['team_ids'])) {
            $clubId = $validated['club_id'] ?? $creator->club_id;
            if (!$this->teamSquadService->validateTeamsBelongToClub($validated['team_ids'], $clubId)) {
                return response()->json(['status' => 'error', 'message' => __('api_users.teams_not_in_club')], 422);
            }
        }

        $newUser = $this->userService->createUser($validated, $request->file('photo'));

        return response()->json([
            'status' => 'success',
            'message' => __('api_users.created_success'),
            'data' => $newUser
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $creator = $request->user();

        if (!in_array($creator->role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => __('api_users.forbidden')], 403);
        }

        $userToEdit = $this->userService->getUserById($id);

        if ($creator->role !== 'administrator' && $userToEdit->club_id !== $creator->club_id) {
            return response()->json(['status' => 'error', 'message' => __('api_users.cannot_edit')], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userToEdit->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:administrator,manager,antrenor,parinte,sportiv',
            'club_id' => 'nullable|exists:clubs,id',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'squad_ids' => 'nullable|array',
            'squad_ids.*' => 'exists:squads,id',
            'child_ids' => 'nullable|array',
            'child_ids.*' => 'exists:users,id',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($creator->role !== 'administrator') {
            if ($validated['role'] === 'administrator' || $validated['role'] === 'manager') {
                return response()->json(['status' => 'error', 'message' => __('api_users.invalid_role')], 403);
            }
            $validated['club_id'] = $creator->club_id;
        }

        // Ownership checks for groups
        if ($request->has('team_ids')) {
            $teamIds = $validated['team_ids'] ?? [];
            if (!empty($teamIds)) {
                $clubIdToCheck = $validated['club_id'] ?? $userToEdit->club_id;
                if (!$this->teamSquadService->validateTeamsBelongToClub($teamIds, $clubIdToCheck)) {
                    return response()->json(['status' => 'error', 'message' => __('api_users.teams_not_in_club')], 422);
                }
            }
        }

        if ($request->has('squad_ids')) {
            $squadIds = $validated['squad_ids'] ?? [];
            if (!empty($squadIds)) {
                $validSquadTeamIds = $this->teamSquadService->getSquadTeamIds($squadIds);
                if ($userToEdit->role !== 'antrenor' && $validated['role'] !== 'antrenor') {
                    $assignedTeams = $request->has('team_ids') ? ($validated['team_ids'] ?? []) : $userToEdit->teams->pluck('id')->toArray();
                    if (!$this->teamSquadService->validateSquadsBelongToTeams($squadIds, $assignedTeams)) {
                        return response()->json(['status' => 'error', 'message' => __('api_users.squads_not_in_teams')], 422);
                    }
                }
            }
        }

        $updatedUser = $this->userService->updateUser($userToEdit, $validated, $request->file('photo'));

        return response()->json([
            'status' => 'success',
            'message' => __('api_users.updated_success'),
            'data' => $updatedUser
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $caller = $request->user();

        if (!in_array($caller->role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => __('api_users.forbidden')], 403);
        }

        $userToDelete = $this->userService->getUserById($id);

        $error = $this->userService->canDeleteUser($userToDelete, $caller);
        if ($error) {
            return response()->json(['status' => 'error', 'message' => $error], 403);
        }

        $userToDelete->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('api_users.deleted_success')
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'photo' => 'nullable|image|max:2048'
        ]);

        $updatedUser = $this->userService->updateUser($user, $validated, $request->file('photo'));

        return response()->json([
            'status' => 'success',
            'message' => __('api_users.profile_updated'),
            'data' => $updatedUser->load('club')
        ]);
    }
}
