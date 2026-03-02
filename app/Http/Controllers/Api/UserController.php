<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;

        if (!in_array($role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis. Doar administratorii și managerii pot gestiona utilizatori.'], 403);
        }

        $query = User::with(['club', 'teams', 'squads', 'activeSubscription.subscription', 'upcomingSubscription.subscription', 'subscriptions.subscription', 'children', 'parents']);

        if ($role !== 'administrator') {
            // Managerii/Antrenorii etc. văd doar userii din clubul lor.
            $query->where('club_id', $request->user()->club_id);
        }
        else {
            // Dacă e admin și a ales un club din filtru
            if ($request->filled('club_id')) {
                $query->where('club_id', $request->club_id);
            }
        }

        if ($request->filled('role')) {
            $roles = explode(',', $request->role);
            if (count($roles) > 1) {
                $query->whereIn('role', $roles);
            }
            else {
                $query->where('role', $request->role);
            }
        }

        if ($request->filled('team_id')) {
            $query->where(function ($q) use ($request) {
                // Direct association
                $q->whereHas('teams', function ($sq) use ($request) {
                        $sq->where('teams.id', $request->team_id);
                    }
                    )
                        // Indirect via children (for parents)
                        ->orWhereHas('children.teams', function ($sq) use ($request) {
                    $sq->where('teams.id', $request->team_id);
                }
                );
            });
        }

        if ($request->filled('squad_id')) {
            $query->where(function ($q) use ($request) {
                // Direct association
                $q->whereHas('squads', function ($sq) use ($request) {
                        $sq->where('squads.id', $request->squad_id);
                    }
                    )
                        // Indirect via children (for parents)
                        ->orWhereHas('children.squads', function ($sq) use ($request) {
                    $sq->where('squads.id', $request->squad_id);
                }
                );
            });
        }

        // Ascunde utilizatorul apelant din lista proprie
        $query->where('id', '!=', $request->user()->id);

        return response()->json([
            'status' => 'success',
            'data' => $query->latest()->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $creatorRole = $request->user()->role;

        if (!in_array($creatorRole, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
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

        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->handlePhotoUpload($request->file('photo'));
        }

        // Reguli de business
        if ($creatorRole !== 'administrator') {
            if ($validated['role'] === 'administrator' || $validated['role'] === 'manager') {
                return response()->json(['status' => 'error', 'message' => 'Rol invalid pentru nivelul tău de acces.'], 403);
            }
            // Forțăm clubul creatorului
            $validated['club_id'] = $request->user()->club_id;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        else {
            $validated['password'] = Hash::make(Str::random(10)); // Generăm o parolă temporară dacă nu o setăm explicit
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        // Validare de securitate la nivel de grupe
        if (!empty($validated['team_ids'])) {
            if ($creatorRole !== 'administrator') {
                $validTeamsCount = \App\Models\Team::whereIn('id', $validated['team_ids'])
                    ->where('club_id', $validated['club_id'])
                    ->count();
                if ($validTeamsCount !== count($validated['team_ids'])) {
                    return response()->json(['status' => 'error', 'message' => 'Eroare: Se pot asigna doar grupe care aparțin clubului selectat.'], 422);
                }
            }
            else {
                // Pentru administrator verificăm doar să aparțină clubului ales în request
                $validTeamsCount = \App\Models\Team::whereIn('id', $validated['team_ids'])
                    ->where('club_id', $validated['club_id'])
                    ->count();
                if ($validTeamsCount !== count($validated['team_ids'])) {
                    return response()->json(['status' => 'error', 'message' => 'Eroare: Grupele selectate nu aparțin clubului selectat.'], 422);
                }
            }
        }

        $userData = $validated;
        unset($userData['team_ids']);
        unset($userData['squad_ids']);
        unset($userData['child_ids']);
        $newUser = User::create($userData);

        if (!empty($validated['team_ids'])) {
            $newUser->teams()->sync($validated['team_ids']);
        }

        if (!empty($validated['squad_ids'])) {
            $newUser->squads()->sync($validated['squad_ids']);
        }

        if ($validated['role'] === 'parinte' && !empty($validated['child_ids'])) {
            $newUser->children()->sync($validated['child_ids']);
        }

        // Aici s-ar putea trimite un mail de bun-venit cu parola $password.

        return response()->json([
            'status' => 'success',
            'message' => 'Utilizator adăugat cu succes!',
            'data' => $newUser->load(['club', 'teams', 'squads', 'children'])
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $creatorRole = $request->user()->role;

        if (!in_array($creatorRole, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        $userToEdit = User::findOrFail($id);

        if ($creatorRole !== 'administrator' && $userToEdit->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Nu aveți acces să editați acest utilizator.'], 403);
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

        if ($request->hasFile('photo')) {
            if ($userToEdit->photo) {
                Storage::disk('public')->delete($userToEdit->photo);
            }
            $validated['photo'] = $this->handlePhotoUpload($request->file('photo'));
        }

        if ($creatorRole !== 'administrator') {
            if ($validated['role'] === 'administrator' || $validated['role'] === 'manager') {
                return response()->json(['status' => 'error', 'message' => 'Rol invalid pentru nivelul tău de acces.'], 403);
            }
            $validated['club_id'] = $request->user()->club_id;
        }

        if ($validated['role'] === 'administrator') {
            $validated['club_id'] = null;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        else {
            unset($validated['password']);
        }

        // Sincronizare echipe
        if ($request->has('team_ids')) {
            $teamIds = $validated['team_ids'] ?? [];
            if (!empty($teamIds)) {
                if ($creatorRole !== 'administrator') {
                    $validTeamsCount = \App\Models\Team::whereIn('id', $teamIds)
                        ->where('club_id', $validated['club_id'])
                        ->count();
                    if ($validTeamsCount !== count($teamIds)) {
                        return response()->json(['status' => 'error', 'message' => 'Eroare: Nu puteți asocia grupe care nu aparțin clubului curent.'], 422);
                    }
                }
                else {
                    $validTeamsCount = \App\Models\Team::whereIn('id', $teamIds)
                        ->where('club_id', $validated['club_id'] ?? $userToEdit->club_id)
                        ->count();
                    if ($validTeamsCount !== count($teamIds)) {
                        return response()->json(['status' => 'error', 'message' => 'Eroare: Grupele selectate nu aparțin clubului selectat.'], 422);
                    }
                }
            }
            $userToEdit->teams()->sync($teamIds);
        }

        // Sincronizare squads (echipe)
        if ($request->has('squad_ids')) {
            $squadIds = $validated['squad_ids'] ?? [];
            if (!empty($squadIds)) {
                $validSquads = \App\Models\Squad::whereIn('id', $squadIds)->pluck('team_id')->unique()->toArray();
                $assignedTeams = $request->has('team_ids') ? ($validated['team_ids'] ?? []) : $userToEdit->teams->pluck('id')->toArray();

                $diff = array_diff($validSquads, $assignedTeams);
                if (count($diff) > 0) {
                    return response()->json(['status' => 'error', 'message' => 'Eroare: Echipele selectate aparțin de alte grupe decât cele asociate utilizatorului.'], 422);
                }
            }
            $userToEdit->squads()->sync($squadIds);
        }

        // Sincronizare copii (pentru parinti)
        if ($request->has('child_ids')) {
            $childIds = $validated['child_ids'] ?? [];
            if ($validated['role'] === 'parinte') {
                $userToEdit->children()->sync($childIds);
            }
            else {
                $userToEdit->children()->detach();
            }
        }

        $updateData = $validated;
        unset($updateData['team_ids']);
        unset($updateData['squad_ids']);
        unset($updateData['child_ids']);
        $userToEdit->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Utilizator actualizat!',
            'data' => $userToEdit->load(['club', 'teams', 'squads', 'children'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $caller = $request->user();

        if (!in_array($caller->role, ['administrator', 'manager'])) {
            return response()->json(['status' => 'error', 'message' => 'Acces interzis.'], 403);
        }

        if ($caller->id === $id) {
            return response()->json(['status' => 'error', 'message' => 'Nu vă puteți șterge propriul cont.'], 403);
        }

        $userToDelete = User::findOrFail($id);

        if ($caller->role !== 'administrator') {
            if ($userToDelete->club_id !== $caller->club_id) {
                return response()->json(['status' => 'error', 'message' => 'Acest utilizator nu face parte din clubul dvs.'], 403);
            }
            if ($userToDelete->role === 'administrator' || $userToDelete->role === 'manager') {
                return response()->json(['status' => 'error', 'message' => 'Nu aveți permisiunea de a șterge acest tip de cont.'], 403);
            }
        }

        if (\App\Models\Training::where('coach_id', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acest utilizator este antrenor pentru unul sau mai multe antrenamente și nu poate fi șters.'
            ], 422);
        }

        if ($userToDelete->subscriptions()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acest utilizator are abonamente alocate. Anulați sau ștergeți abonamentele înainte de a șterge contul.'
            ], 422);
        }

        $userToDelete->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Utilizatorul a fost șters.'
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

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $updateData['photo'] = $this->handlePhotoUpload($request->file('photo'));
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil actualizat cu succes!',
            'data' => $user->load('club')
        ]);
    }

    protected function handlePhotoUpload($file)
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('profiles', $filename, 'public');
    }
}
