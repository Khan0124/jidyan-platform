<?php

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        return [
            'key' => 'feature_'.str_replace('-', '_', $this->faker->unique()->slug(2)),
            'enabled' => $this->faker->boolean(),
            'description' => $this->faker->sentence(),
        ];
    }
}
