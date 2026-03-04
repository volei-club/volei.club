<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Get defined subscriptions with role-based filtering.
     */
    public function listDefinitions(Request $request)
    {
        $user = $request->user();
        $query = Subscription::with('club');

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role === 'administrator' && $request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        return $query->get();
    }

    /**
     * Create a new subscription definition.
     */
    public function createDefinition(array $data)
    {
        return Subscription::create($data);
    }

    /**
     * Update a subscription definition.
     */
    public function updateDefinition(Subscription $subscription, array $data)
    {
        $subscription->update($data);
        return $subscription;
    }

    /**
     * Assign a subscription to a user.
     */
    public function assignToUser(array $data)
    {
        $starts = Carbon::parse($data['starts_at']);
        $subDef = Subscription::findOrFail($data['subscription_id']);
        $expires = $this->calculateExpiry($starts, $subDef->period);

        return UserSubscription::create([
            'user_id' => $data['user_id'],
            'subscription_id' => $subDef->id,
            'starts_at' => $starts,
            'expires_at' => $expires,
            'status' => $data['status'] ?? 'active_pending',
        ]);
    }

    /**
     * Update an existing user subscription.
     */
    public function updateUserSubscription(UserSubscription $userSubscription, array $data)
    {
        if (isset($data['subscription_id']) || isset($data['starts_at'])) {
            $subDef = isset($data['subscription_id'])
                ?Subscription::findOrFail($data['subscription_id'])
                : Subscription::findOrFail($userSubscription->subscription_id);

            $starts = isset($data['starts_at'])
                ?Carbon::parse($data['starts_at'])
                : Carbon::parse($userSubscription->starts_at);

            $data['starts_at'] = $starts;
            $data['expires_at'] = $this->calculateExpiry($starts, $subDef->period);
        }

        $userSubscription->update($data);
        return $userSubscription;
    }

    /**
     * Get subscriptions for a specific athlete.
     */
    public function getAthleteSubscriptions(string $userId)
    {
        return UserSubscription::with('subscription')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if a caller can manage a target user's subscription.
     */
    public function canManageSubscription(User $caller, string $targetUserId): bool
    {
        if ($caller->role === 'administrator')
            return true;

        $targetUser = User::findOrFail($targetUserId);
        if ($caller->role === 'manager') {
            return $targetUser->club_id === $caller->club_id;
        }

        return false;
    }

    /**
     * Check if a caller can view a target user's subscription.
     */
    public function canViewSubscription(User $caller, string $targetUserId): bool
    {
        if ($targetUserId == $caller->id)
            return true;
        if (in_array($caller->role, ['administrator', 'manager']))
            return $this->canManageSubscription($caller, $targetUserId);

        if ($caller->role === 'parinte') {
            return $caller->children()->where('users.id', $targetUserId)->exists();
        }

        return false;
    }

    /**
     * Calculate expiry date based on period string.
     */
    protected function calculateExpiry(Carbon $starts, string $period): Carbon
    {
        $expires = $starts->copy();
        switch ($period) {
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
                break;
        }
        return $expires;
    }

    /**
     * Get a subscription definition by ID.
     */
    public function getDefinitionById(string $id): Subscription
    {
        return Subscription::findOrFail($id);
    }

    /**
     * Get a user subscription instance by ID.
     */
    public function getUserSubscriptionById(string $id, array $relations = []): UserSubscription
    {
        $query = UserSubscription::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        return $query->findOrFail($id);
    }
}
