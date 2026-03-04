<?php

namespace App\Services;

use App\Models\User;
use App\Models\Team;
use App\Models\Squad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    /**
     * Get users with role-based filtering and search.
     */
    public function listUsers(Request $request)
    {
        $role = $request->user()->role;
        $query = User::with(['club', 'teams', 'squads', 'activeSubscription.subscription', 'upcomingSubscription.subscription', 'subscriptions.subscription', 'children', 'parents']);

        if ($role === 'parinte') {
            $query->whereHas('parents', function ($q) use ($request) {
                $q->where('users.id', $request->user()->id);
            });
        }
        elseif ($role !== 'administrator') {
            $query->where('club_id', $request->user()->club_id);
        }
        elseif ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        if ($request->filled('role')) {
            $roles = explode(',', $request->role);
            $query->whereIn('role', $roles);
        }

        if ($request->filled('team_id')) {
            $query->where(function (Builder $q) use ($request) {
                $q->whereHas('teams', fn($sq) => $sq->where('teams.id', $request->team_id))
                    ->orWhereHas('children.teams', fn($sq) => $sq->where('teams.id', $request->team_id));
            });
        }

        if ($request->filled('squad_id')) {
            $query->where(function (Builder $q) use ($request) {
                $q->whereHas('squads', fn($sq) => $sq->where('squads.id', $request->squad_id))
                    ->orWhereHas('children.squads', fn($sq) => $sq->where('squads.id', $request->squad_id));
            });
        }

        $query->where('id', '!=', $request->user()->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 50);
        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new user with associated data.
     */
    public function createUser(array $data, ?object $photo = null)
    {
        if ($photo) {
            $data['photo'] = $this->handlePhotoUpload($photo);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        else {
            $data['password'] = Hash::make(Str::random(10));
        }

        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        $userData = $data;
        $teamIds = $userData['team_ids'] ?? [];
        $squadIds = $userData['squad_ids'] ?? [];
        $childIds = $userData['child_ids'] ?? [];

        unset($userData['team_ids'], $userData['squad_ids'], $userData['child_ids']);

        $user = User::create($userData);

        if (!empty($teamIds)) {
            $user->teams()->sync($teamIds);
        }

        if (!empty($squadIds)) {
            $user->squads()->sync($squadIds);
            $parentTeamIds = Squad::whereIn('id', $squadIds)->pluck('team_id')->unique()->toArray();
            if (!empty($parentTeamIds)) {
                $user->teams()->syncWithoutDetaching($parentTeamIds);
            }
        }

        if ($user->role === 'parinte' && !empty($childIds)) {
            $user->children()->sync($childIds);
        }

        return $user->load(['club', 'teams', 'squads', 'children']);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data, ?object $photo = null)
    {
        if ($photo) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $this->handlePhotoUpload($photo);
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        else {
            unset($data['password']);
        }

        $teamIds = $data['team_ids'] ?? null;
        $squadIds = $data['squad_ids'] ?? null;
        $childIds = $data['child_ids'] ?? null;

        $userData = $data;
        unset($userData['team_ids'], $userData['squad_ids'], $userData['child_ids']);

        if ($userData['role'] ?? $user->role === 'administrator') {
            $userData['club_id'] = null;
        }

        $user->update($userData);

        if ($teamIds !== null) {
            $user->teams()->sync($teamIds);
        }

        if ($squadIds !== null) {
            $user->squads()->sync($squadIds);
            $validSquads = Squad::whereIn('id', $squadIds)->pluck('team_id')->unique()->toArray();
            if ($user->role === 'antrenor' || ($userData['role'] ?? $user->role) === 'antrenor') {
                $user->teams()->syncWithoutDetaching($validSquads);
            }
            if (!empty($squadIds)) {
                $parentTeamIds = Squad::whereIn('id', $squadIds)->pluck('team_id')->unique()->toArray();
                $user->teams()->syncWithoutDetaching($parentTeamIds);
            }
        }

        if ($childIds !== null) {
            if (($userData['role'] ?? $user->role) === 'parinte') {
                $user->children()->sync($childIds);
            }
            else {
                $user->children()->detach();
            }
        }

        return $user->load(['club', 'teams', 'squads', 'children']);
    }

    /**
     * Handle photo upload.
     */
    public function handlePhotoUpload($file)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('profiles', $filename, 'public');
    }

    /**
     * Check if user can be deleted.
     */
    public function canDeleteUser(User $user, User $caller): ?string
    {
        if ($caller->id === $user->id) {
            return 'Nu vă puteți șterge propriul cont.';
        }

        if ($caller->role !== 'administrator') {
            if ($user->club_id !== $caller->club_id) {
                return 'Acest utilizator nu face parte din clubul dvs.';
            }
            if (in_array($user->role, ['administrator', 'manager'])) {
                return 'Nu aveți permisiunea de a șterge acest tip de cont.';
            }
        }

        if (\App\Models\Training::where('coach_id', $user->id)->exists()) {
            return 'Acest utilizator este antrenor pentru unul sau mai multe antrenamente și nu poate fi șters.';
        }

        if ($user->subscriptions()->count() > 0) {
            return 'Acest utilizator are abonamente alocate. Anulați sau ștergeți abonamentele înainte de a șterge contul.';
        }

        return null;
    }

    /**
     * Get a user by their email address.
     */
    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get a user by their ID, supporting relationships.
     */
    public function getUserById(string $id, array $relations = []): User
    {
        $query = User::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        return $query->findOrFail($id);
    }
}
