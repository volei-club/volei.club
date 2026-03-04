<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Return defined subscriptions.
     * Admin sees all, Managers see only their club's subscriptions.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['administrator', 'manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $definitions = $this->subscriptionService->listDefinitions($request);

        return response()->json([
            'data' => $definitions
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
        if ($user->role === 'manager') {
            $clubId = $user->club_id;
        }
        else if ($user->role === 'administrator') {
            $clubId = $request->club_id;
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$clubId) {
            return response()->json(['message' => 'Club ID is required.'], 400);
        }

        $subscription = $this->subscriptionService->createDefinition([
            'club_id' => $clubId,
            'name' => $request->name,
            'price' => $request->price,
            'period' => $request->period,
        ]);

        return response()->json([
            'message' => 'Subscription created successfully',
            'data' => $subscription->load('club')
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

        $updated = $this->subscriptionService->updateDefinition($subscription, $request->only(['name', 'price', 'period']));

        return response()->json([
            'message' => 'Subscription updated successfully',
            'data' => $updated->load('club')
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

        if ($subscription->userSubscriptions()->count() > 0) {
            return response()->json(['message' => 'Acest abonament este in uz de catre unii membri. Nu poate fi sters.'], 400);
        }

        $subscription->delete();

        return response()->json(['message' => 'Subscription deleted successfully']);
    }
}
