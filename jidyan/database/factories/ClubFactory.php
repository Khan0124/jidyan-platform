<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClubFactory extends Factory
{
    protected $model = Club::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company().' FC',
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verified_at' => now(),
        ]);
    }
}
