<?php

namespace Database\Factories;

use App\Models\PlayerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerProfileFactory extends Factory
{
    protected $model = PlayerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'dob' => $this->faker->dateTimeBetween('-24 years', '-16 years'),
            'nationality' => $this->faker->country(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'height_cm' => $this->faker->numberBetween(160, 195),
            'weight_kg' => $this->faker->numberBetween(55, 90),
            'position' => $this->faker->randomElement(['GK', 'LB', 'RB', 'CB', 'CM', 'LW', 'RW', 'ST']),
            'preferred_foot' => $this->faker->randomElement(['left', 'right', 'both']),
            'current_club' => $this->faker->company().' FC',
            'previous_clubs' => [$this->faker->company().' Academy'],
            'bio' => $this->faker->paragraph(),
            'visibility' => 'public',
            'availability' => $this->faker->randomElement(PlayerProfile::AVAILABILITY_OPTIONS),
            'last_active_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
