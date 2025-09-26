<?php

namespace App\Services\Verification;

use App\Models\Verification;

class VerificationStatusSynchronizer
{
    public function sync(Verification $verification): void
    {
        $user = $verification->user()->with(['playerProfile', 'agent'])->first();

        if (! $user) {
            return;
        }

        $timestamp = $verification->status === 'approved' ? now() : null;

        $playerProfile = $user->playerProfile;
        if ($playerProfile) {
            if ($verification->type === 'identity') {
                $playerProfile->forceFill(['verified_identity_at' => $timestamp])->saveQuietly();
            }

            if ($verification->type === 'academy') {
                $playerProfile->forceFill(['verified_academy_at' => $timestamp])->saveQuietly();
            }
        }

        if ($verification->type === 'identity' && $user->agent) {
            $user->agent->forceFill(['verified_at' => $timestamp])->saveQuietly();
        }
    }
}
