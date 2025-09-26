<?php

use App\Http\Controllers\AgentLinkController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContentReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediaAccessController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\OpportunityPublicController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\PlayerSearchController;
use App\Http\Controllers\PlayerStatController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/opportunities', [OpportunityPublicController::class, 'index'])->name('opportunities.index');
Route::get('/opportunities/{opportunity:slug}', [OpportunityPublicController::class, 'show'])->name('opportunities.show');
Route::get('/health', HealthCheckController::class)->name('health');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/player/profile', [PlayerProfileController::class, 'edit'])->name('dashboard.player.profile.edit');
    Route::put('/dashboard/player/profile', [PlayerProfileController::class, 'update'])->name('dashboard.player.profile.update');

    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::post('/dashboard/player/stats', [PlayerStatController::class, 'store'])
        ->name('dashboard.player.stats.store');
    Route::put('/dashboard/player/stats/{stat}', [PlayerStatController::class, 'update'])
        ->name('dashboard.player.stats.update');
    Route::delete('/dashboard/player/stats/{stat}', [PlayerStatController::class, 'destroy'])
        ->name('dashboard.player.stats.destroy');

    Route::post('/player/agent-links', [AgentLinkController::class, 'store'])->name('player.agent-links.store');
    Route::patch('/player/agent-links/{link}', [AgentLinkController::class, 'update'])->name('player.agent-links.update');

    Route::middleware('role:coach')->group(function () {
        Route::get('/dashboard/coach/search', PlayerSearchController::class)->name('dashboard.coach.search');
    });

    Route::middleware('role:player|agent')->group(function () {
        Route::post('/opportunities/{opportunity}/applications', [ApplicationController::class, 'store'])
            ->name('opportunities.apply');
    });

    Route::middleware('role:club_admin')->group(function () {
        Route::resource('dashboard/club/opportunities', OpportunityController::class)->except(['destroy']);
        Route::get('dashboard/club/applications', [ApplicationController::class, 'index'])->name('dashboard.club.applications.index');
    });

    Route::get('/dashboard/messages', [MessageController::class, 'index'])->name('dashboard.messages.index');
    Route::post('/dashboard/messages', [MessageController::class, 'start'])->name('dashboard.messages.start');
    Route::post('/dashboard/messages/{thread}', [MessageController::class, 'store'])->name('dashboard.messages.store');

    Route::post('/dashboard/reports', [ContentReportController::class, 'store'])->name('reports.store');
    Route::get('/dashboard/player/verification', [VerificationController::class, 'create'])->name('verifications.create');
    Route::post('/dashboard/verifications', [VerificationController::class, 'store'])->name('verifications.store');

    Route::middleware('role:verifier|admin')->group(function () {
        Route::get('/dashboard/admin/verifications', [VerificationController::class, 'index'])->name('verifications.index');
        Route::put('/dashboard/admin/verifications/{verification}', [VerificationController::class, 'update'])->name('verifications.update');
        Route::get('/dashboard/admin/verifications/{verification}/download', [VerificationController::class, 'download'])->name('verifications.download');
        Route::get('/dashboard/admin/reports', [ContentReportController::class, 'index'])->name('reports.index');
        Route::patch('/dashboard/admin/reports/{report}', [ContentReportController::class, 'update'])->name('reports.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/admin/feature-flags', [FeatureFlagController::class, 'index'])->name('feature-flags.index');
        Route::put('/dashboard/admin/feature-flags/{featureFlag}', [FeatureFlagController::class, 'update'])->name('feature-flags.update');
    });
});

Route::get('/players/{player}', [PlayerProfileController::class, 'show'])->name('players.show');
Route::get('/media/signed-url/{media}', [MediaAccessController::class, 'show'])->name('media.signed-url');
Route::middleware('signed')->get('/media/hls/{media}/{path}', [MediaAccessController::class, 'stream'])
    ->where('path', '.*')
    ->name('media.hls');

require __DIR__.'/auth.php';
