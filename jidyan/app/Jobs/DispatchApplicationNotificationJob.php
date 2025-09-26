<?php

namespace App\Jobs;

use App\Models\Application;
use App\Notifications\NewApplicationSubmitted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchApplicationNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $applicationId)
    {
    }

    public function handle(): void
    {
        $application = Application::with(['opportunity.club.administrators.user'])->findOrFail($this->applicationId);

        foreach ($application->opportunity->club->administrators as $admin) {
            $admin->user->notify(new NewApplicationSubmitted($application));
        }
    }
}
