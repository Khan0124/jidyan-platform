<?php

use App\Jobs\CleanupMediaJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('media:cleanup', function () {
    CleanupMediaJob::dispatch();
    $this->info('Cleanup job dispatched');
})->purpose('Archive processed media older than 30 days');
