<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role;
        $query = User::with('club');

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
            $query->where('role', $request->role);
        }

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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:administrator,manager,antrenor,parinte,sportiv',
            'club_id' => 'nullable|exists:clubs,id',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean'
        ]);

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

        $user = User::create($validated);

        // Aici s-ar putea trimite un mail de bun-venit cu parola $password.

        return response()->json([
            'status' => 'success',
            'message' => 'Utilizator adăugat cu succes!',
            'data' => $user->load('club')
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $creatorRole = $request->user()->role;
        $userToEdit = User::findOrFail($id);

        if ($creatorRole !== 'administrator' && $userToEdit->club_id !== $request->user()->club_id) {
            return response()->json(['status' => 'error', 'message' => 'Nu aveți acces să editați acest utilizator.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userToEdit->id,
            'role' => 'required|in:administrator,manager,antrenor,parinte,sportiv',
            'club_id' => 'nullable|exists:clubs,id',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean'
        ]);

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

        $userToEdit->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Utilizator actualizat!',
            'data' => $userToEdit->load('club')
        ]);
    }
}
