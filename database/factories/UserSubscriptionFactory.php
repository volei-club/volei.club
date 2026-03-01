<?php

namespace Database\Factories;

use App\Models\UserSubscription;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class UserSubscriptionFactory extends Factory
{
    protected $model = UserSubscription::class;

    public function definition(): array
    {
        $startsAt = Carbon::now();
        return [
            'user_id' => User::factory(),
            'subscription_id' => Subscription::factory(),
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addMonth(),
            'status' => 'active_paid',
        ];
    }
}
