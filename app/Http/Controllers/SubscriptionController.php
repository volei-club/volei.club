<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Club;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Return defined subscriptions.
     * Admin sees all, Managers see only their club's subscriptions.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Subscription::with('club');

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        else if ($user->role === 'administrator') {
            if ($request->has('club_id') && $request->club_id) {
                $query->where('club_id', $request->club_id);
            }
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }

    /**
     * Create a new defined Subscription package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'period' => 'required|string|in:1_saptamana,2_saptamani,1_luna,3_luni,6_luni,1_an',
            'club_id' => 'sometimes|exists:clubs,id'
        ]);

        $user = $request->user();
        $clubId = $user->role === 'manager' ? $user->club_id : $request->club_id;

        if (!$clubId) {
            return response()->json(['message' => 'Club ID is required.'], 400);
        }

        $subscription = Subscription::create([
            'club_id' => $clubId,
            'name' => $request->name,
            'price' => $request->price,
            'period' => $request->period,
        ]);

        $subscription->load('club');

        return response()->json([
            'message' => 'Subscription created successfully',
            'data' => $subscription
        ], 201);
    }

    /**
     * Update an existing subscription.
     */
    public function update(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        $user = $request->user();

        if ($user->role === 'manager' && $subscription->club_id !== $user->club_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'period' => 'required|string|in:1_saptamana,2_saptamani,1_luna,3_luni,6_luni,1_an',
        ]);

        $subscription->update([
            'name' => $request->name,
            'price' => $request->price,
            'period' => $request->period,
        ]);

        $subscription->load('club');

        return response()->json([
            'message' => 'Subscription updated successfully',
            'data' => $subscription
        ]);
    }

    /**
     * Delete a generic subscription.
     */
    public function destroy(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        $user = $request->user();

        if ($user->role === 'manager' && $subscription->club_id !== $user->club_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Optional: Check if it has active users attached before deleting
        if ($subscription->userSubscriptions()->count() > 0) {
            return response()->json(['message' => 'Acest abonament este in uz de catre unii membri. Nu poate fi sters.'], 400);
        }

        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }
}
