<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Grupa ' . $this->faker->word() . ' ' . $this->faker->year(),
            'club_id' => Club::factory(), // If not explicitly provided, it generates a parent club
        ];
    }
}
