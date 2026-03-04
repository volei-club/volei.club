<?php

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserSubscriptionController extends Controller
{
    /**
     * Assign a generic subscription to a specific user (athlete)
     * Managers can only assign to members of their club.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'starts_at' => 'required|date',
            'status' => 'required|in:active_paid,active_pending'
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($request->user_id);
        $subscriptionDef = Subscription::findOrFail($request->subscription_id);

        if ($user->role === 'manager' && $targetUser->club_id !== $user->club_id) {
            return response()->json(['message' => 'Nu poti asocia abonamente acestui sportiv.'], 403);
        }

        // Calculate expires_at
        $starts = Carbon::parse($request->starts_at);
        $expires = $starts->copy();

        switch ($subscriptionDef->period) {
            case '1_saptamana':
                $expires->addWeek();
                break;
            case '2_saptamani':
                $expires->addWeeks(2);
                break;
            case '1_luna':
                $expires->addMonth();
                break;
            case '3_luni':
                $expires->addMonths(3);
                break;
            case '6_luni':
                $expires->addMonths(6);
                break;
            case '1_an':
                $expires->addYear();
                break;
            default:
                $expires->addMonth();
        }

        $userSubscription = UserSubscription::create([
            'user_id' => $targetUser->id,
            'subscription_id' => $subscriptionDef->id,
            'starts_at' => $starts,
            'expires_at' => $expires,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Subscription assigned to user successfully.',
            'data' => $userSubscription->load('subscription')
        ], 201);
    }

    /**
     * Update a specific user subscription instance
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'subscription_id' => 'sometimes|exists:subscriptions,id',
            'starts_at' => 'sometimes|date',
            'status' => 'sometimes|in:active_paid,active_pending,expired,cancelled'
        ]);

        $userSubscription = UserSubscription::findOrFail($id);
        $user = $request->user();
        $targetUser = User::findOrFail($userSubscription->user_id);

        if ($user->role === 'manager' && $targetUser->club_id !== $user->club_id) {
            return response()->json(['message' => 'Nu aveti permisiunea.'], 403);
        }

        $data = $request->only(['status', 'starts_at', 'subscription_id']);

        if (isset($data['subscription_id']) || isset($data['starts_at'])) {
            $subDef = isset($data['subscription_id'])
                ?Subscription::findOrFail($data['subscription_id'])
                : Subscription::findOrFail($userSubscription->subscription_id);

            $starts = isset($data['starts_at']) ?Carbon::parse($data['starts_at']) : Carbon::parse($userSubscription->starts_at);

            $expires = $starts->copy();
            switch ($subDef->period) {
                case '1_saptamana':
                    $expires->addWeek();
                    break;
                case '2_saptamani':
                    $expires->addWeeks(2);
                    break;
                case '1_luna':
                    $expires->addMonth();
                    break;
                case '3_luni':
                    $expires->addMonths(3);
                    break;
                case '6_luni':
                    $expires->addMonths(6);
                    break;
                case '1_an':
                    $expires->addYear();
                    break;
                default:
                    $expires->addMonth();
            }
            $data['expires_at'] = $expires;
            $data['starts_at'] = $starts;
        }

        $userSubscription->update($data);

        return response()->json([
            'message' => 'Abonament actualizat.',
            'data' => $userSubscription->load('subscription')
        ]);
    }

    /**
     * Delete a specific user subscription instance
     */
    public function destroy(Request $request, $id)
    {
        $userSubscription = UserSubscription::findOrFail($id);
        $user = $request->user();
        $targetUser = User::findOrFail($userSubscription->user_id);

        if ($user->role === 'manager' && $targetUser->club_id !== $user->club_id) {
            return response()->json(['message' => 'Nu aveti permisiunea.'], 403);
        }

        $userSubscription->delete();

        return response()->json(['message' => 'Abonament șters.']);
    }

    /**
     * Update the status of a specific user subscription instance (e.g. mark as Paid or Cancelled)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active_paid,active_pending,expired,cancelled'
        ]);

        $userSubscription = UserSubscription::findOrFail($id);
        $user = $request->user();

        $targetUser = User::findOrFail($userSubscription->user_id);
        if ($user->role === 'manager' && $targetUser->club_id !== $user->club_id) {
            return response()->json(['message' => 'Nu aveti permisiunea.'], 403);
        }

        $userSubscription->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status actualizat.',
            'data' => $userSubscription->load('subscription')
        ]);
    }

    /**
     * Get subscriptions for the authenticated user (athlete/parent).
     */
    public function mySubscriptions(Request $request)
    {
        $user = $request->user();

        $subscriptions = UserSubscription::with('subscription')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $subscriptions
        ]);
    }
}
