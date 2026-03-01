<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'period' => $this->faker->randomElement(['1_saptamana', '2_saptamani', '1_luna', '3_luni', '6_luni', '1_an']),
        ];
    }
}
