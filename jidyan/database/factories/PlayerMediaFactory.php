<?php

namespace Database\Factories;

use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PlayerMedia>
 */
class PlayerMediaFactory extends Factory
{
    protected $model = PlayerMedia::class;

    public function definition(): array
    {
        return [
            'player_id' => PlayerProfile::factory(),
            'type' => 'video',
            'provider' => 'local',
            'original_filename' => $this->faker->lexify('clip-????.mp4'),
            'path' => 'media/inbox/'.Str::uuid().'.mp4',
            'hls_path' => 'media/hls/'.Str::uuid().'/index.m3u8',
            'poster_path' => 'media/hls/'.Str::uuid().'/poster.jpg',
            'duration_sec' => 60,
            'quality_label' => '720p',
            'status' => 'ready',
        ];
    }
}
