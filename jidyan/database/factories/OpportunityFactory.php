<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        $deadline = now()->addDays($this->faker->numberBetween(7, 45));

        return [
            'club_id' => Club::factory(),
            'title' => $this->faker->city().' Talent Trial',
            'description' => $this->faker->paragraphs(2, true),
            'requirements' => [
                ['label' => 'Age', 'value' => (string) $this->faker->numberBetween(16, 23)],
                ['label' => 'Preferred positions', 'value' => 'Midfield / Wing'],
            ],
            'location_city' => $this->faker->city(),
            'location_country' => $this->faker->country(),
            'deadline_at' => $deadline,
            'status' => 'published',
            'visibility' => 'public',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }

    public function privateVisibility(): static
    {
        return $this->state(fn () => ['visibility' => 'private']);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['deadline_at' => now()->subDay()]);
    }
}
