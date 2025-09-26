<?php

use App\Http\Controllers\AgentLinkController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContentReportController;
use App\Http\Controllers\FeatureFlagApiController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\MediaAccessController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\PlayerSearchController;
use App\Http\Controllers\PlayerStatController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthCheckController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/players/me', fn () => auth()->user()->playerProfile);
        Route::post('/media', [MediaController::class, 'store']);
        Route::delete('/media/{media}', [MediaController::class, 'destroy']);
        Route::post('/players/me/stats', [PlayerStatController::class, 'store']);
        Route::put('/players/me/stats/{stat}', [PlayerStatController::class, 'update']);
        Route::delete('/players/me/stats/{stat}', [PlayerStatController::class, 'destroy']);
        Route::post('/player/agent-links', [AgentLinkController::class, 'store']);
        Route::patch('/player/agent-links/{link}', [AgentLinkController::class, 'update']);
        Route::post('/reports', [ContentReportController::class, 'store']);
        Route::post('/opportunities/{opportunity}/applications', [ApplicationController::class, 'store']);
        Route::patch('/applications/{application}', [ApplicationController::class, 'updateStatus']);
        Route::post('/verifications', [VerificationController::class, 'store']);
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'start']);
        Route::get('/media/{media}/signed-url', [MediaAccessController::class, 'show']);

        Route::middleware('role:verifier|admin')->group(function () {
            Route::get('/reports', [ContentReportController::class, 'index']);
            Route::patch('/reports/{report}', [ContentReportController::class, 'update']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/feature-flags', [FeatureFlagApiController::class, 'index']);
            Route::patch('/admin/feature-flags/{featureFlag}', [FeatureFlagApiController::class, 'update']);
        });
    });

    Route::get('/players', PlayerSearchController::class);
    Route::get('/players/{player}', [PlayerProfileController::class, 'show']);
    Route::get('/opportunities', [OpportunityController::class, 'index']);
});
