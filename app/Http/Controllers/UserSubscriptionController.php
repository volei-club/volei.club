<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class UserSubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

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

        if (!$this->subscriptionService->canManageSubscription($request->user(), $request->user_id)) {
            return response()->json(['message' => __('api_user_subscriptions.cannot_assign')], 403);
        }

        $userSubscription = $this->subscriptionService->assignToUser($request->all());

        return response()->json([
            'message' => __('api_user_subscriptions.assigned_success'),
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

        $userSubscription = $this->subscriptionService->getUserSubscriptionById($id);

        if (!$this->subscriptionService->canManageSubscription($request->user(), $userSubscription->user_id)) {
            return response()->json(['message' => __('api_user_subscriptions.no_permission')], 403);
        }

        $this->subscriptionService->updateUserSubscription($userSubscription, $request->only(['status', 'starts_at', 'subscription_id']));

        return response()->json([
            'message' => __('api_user_subscriptions.updated_success'),
            'data' => $userSubscription->load('subscription')
        ]);
    }

    /**
     * Delete a specific user subscription instance
     */
    public function destroy(Request $request, $id)
    {
        $userSubscription = $this->subscriptionService->getUserSubscriptionById($id);

        if (!$this->subscriptionService->canManageSubscription($request->user(), $userSubscription->user_id)) {
            return response()->json(['message' => __('api_user_subscriptions.no_permission')], 403);
        }

        $userSubscription->delete();

        return response()->json(['message' => __('api_user_subscriptions.deleted_success')]);
    }

    /**
     * Update the status of a specific user subscription instance (e.g. mark as Paid or Cancelled)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active_paid,active_pending,expired,cancelled'
        ]);

        $userSubscription = $this->subscriptionService->getUserSubscriptionById($id);

        if (!$this->subscriptionService->canManageSubscription($request->user(), $userSubscription->user_id)) {
            return response()->json(['message' => __('api_user_subscriptions.no_permission')], 403);
        }

        $userSubscription->update(['status' => $request->status]);

        return response()->json([
            'message' => __('api_user_subscriptions.status_updated'),
            'data' => $userSubscription->load('subscription')
        ]);
    }

    /**
     * Get subscriptions for the authenticated user (athlete/parent).
     */
    public function mySubscriptions(Request $request)
    {
        $user = $request->user();
        $targetUserId = $request->query('user_id', $user->id);

        if (!$this->subscriptionService->canViewSubscription($user, $targetUserId)) {
            return response()->json(['message' => __('api_user_subscriptions.forbidden')], 403);
        }

        $subscriptions = $this->subscriptionService->getAthleteSubscriptions($targetUserId);

        return response()->json([
            'status' => 'success',
            'data' => $subscriptions
        ]);
    }
}
