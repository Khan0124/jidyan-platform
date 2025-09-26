<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Application;
use App\Models\Club;
use App\Models\ClubAdmin;
use App\Models\Coach;
use App\Models\ContentReport;
use App\Models\FeatureFlag;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\Opportunity;
use App\Models\PlayerMedia;
use App\Models\PlayerProfile;
use App\Models\PlayerStat;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }

        $roles = ['player', 'coach', 'club_admin', 'agent', 'verifier', 'admin'];
        $users = collect($roles)->mapWithKeys(function ($role) {
            $user = User::factory()->create([
                'name' => Str::headline($role).' User',
                'email' => $role.'@jidyan.test',
                'password' => bcrypt('password'),
            ]);
            $user->assignRole($role);
            return [$role => $user];
        });

        collect([
            ['key' => 'messaging.inbox', 'description' => 'Enable internal messaging inbox for all roles.'],
            ['key' => 'applications.pipeline', 'description' => 'Enable opportunity application pipeline stages.'],
            ['key' => 'media.chunked_uploads', 'description' => 'Enable resumable media uploads for players.'],
        ])->each(function (array $flag) {
            FeatureFlag::firstOrCreate(
                ['key' => $flag['key']],
                ['description' => $flag['description'], 'enabled' => true]
            );
        });

        $club = Club::create([
            'name' => 'Jidyan FC',
            'country' => 'UAE',
            'city' => 'Abu Dhabi',
            'verified_at' => now(),
        ]);

        ClubAdmin::create([
            'user_id' => $users['club_admin']->id,
            'club_id' => $club->id,
            'role_title' => 'Head of Recruitment',
        ]);

        Coach::create([
            'user_id' => $users['coach']->id,
            'club_id' => $club->id,
            'license_level' => 'UEFA A',
            'bio' => 'Experienced youth development coach.',
        ]);

        Agent::create([
            'user_id' => $users['agent']->id,
            'license_no' => 'AG-123456',
            'agency_name' => 'Jidyan Agency',
            'verified_at' => now(),
        ]);

        $players = PlayerProfile::factory(10)->create();

        $players->each(function (PlayerProfile $profile) use ($users) {
            $profile->user->assignRole('player');
            $profile->update([
                'visibility' => 'public',
                'verified_identity_at' => now(),
                'availability' => collect(PlayerProfile::AVAILABILITY_OPTIONS)->random(),
                'last_active_at' => now()->subDays(rand(0, 14)),
            ]);

            PlayerStat::create([
                'player_id' => $profile->id,
                'season' => '2023/2024',
                'matches' => rand(10, 30),
                'goals' => rand(0, 15),
                'assists' => rand(0, 12),
                'notes' => 'Generated sample data',
                'verified_by_user_id' => $users['verifier']->id,
            ]);

            PlayerMedia::create([
                'player_id' => $profile->id,
                'type' => 'video',
                'provider' => 'local',
                'original_filename' => 'sample.mp4',
                'path' => 'media/hls/sample.mp4',
                'hls_path' => 'media/hls/sample/index.m3u8',
                'poster_path' => 'media/hls/sample/poster.jpg',
                'status' => 'ready',
                'quality_label' => '720p',
                'meta' => ['source' => 'seed'],
            ]);
        });

        $samplePlayer = PlayerProfile::query()->with('user')->first();

        if ($samplePlayer) {
            $thread = MessageThread::create([
                'subject' => 'Welcome to Jidyan',
            ]);

            MessageThreadParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $users['coach']->id,
                'last_read_at' => now(),
            ]);

            MessageThreadParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $samplePlayer->user_id,
                'last_read_at' => now(),
            ]);

            Message::create([
                'thread_id' => $thread->id,
                'sender_user_id' => $users['coach']->id,
                'body' => 'مرحبا! شاهدنا مهاراتك ووددنا التحدث معك.',
                'read_at' => now(),
            ]);

            Message::create([
                'thread_id' => $thread->id,
                'sender_user_id' => $samplePlayer->user_id,
                'body' => 'شكراً للتواصل! يسعدني الانضمام إلى التجارب.',
                'read_at' => now(),
            ]);
        }

        $opportunity = Opportunity::create([
            'club_id' => $club->id,
            'title' => 'U18 Forward Tryout',
            'slug' => 'u18-forward-tryout',
            'description' => 'Looking for a pacey forward for the upcoming season.',
            'requirements' => [
                ['label' => 'Age', 'value' => '16-18'],
                ['label' => 'Position', 'value' => 'Forward'],
            ],
            'location_city' => 'Abu Dhabi',
            'location_country' => 'UAE',
            'deadline_at' => now()->addWeeks(3),
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $pipelineStatuses = ['received', 'shortlisted', 'invited'];

        $players->take(count($pipelineStatuses))->values()->each(function (PlayerProfile $profile, int $index) use ($opportunity, $users, $pipelineStatuses) {
            Application::create([
                'opportunity_id' => $opportunity->id,
                'player_id' => $profile->id,
                'note' => 'Sample application '.$pipelineStatuses[$index],
                'status' => $pipelineStatuses[$index],
                'reviewed_by_user_id' => $index === 0 ? null : $users['club_admin']->id,
            ]);
        });

        if ($sampleMedia = PlayerMedia::query()->first()) {
            ContentReport::create([
                'reportable_type' => PlayerMedia::class,
                'reportable_id' => $sampleMedia->id,
                'reporter_user_id' => $users['coach']->id,
                'reason' => 'Blurry footage',
                'description' => 'Video quality is too low for review.',
                'status' => ContentReport::STATUS_PENDING,
            ]);
        }

        Verification::create([
            'user_id' => $users['player']->id,
            'type' => 'identity',
            'document_path' => 'verifications/'.$users['player']->id.'/sample-passport.pdf',
            'document_name' => 'sample-passport.pdf',
            'status' => 'approved',
            'reviewed_by' => $users['verifier']->id,
            'reviewed_at' => now()->subDays(2),
        ]);
    }
}
