<?php

namespace App\Http\Requests;

use App\Models\PlayerProfile;
use App\Models\PlayerStat;
use Illuminate\Foundation\Http\FormRequest;

class PlayerStatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $stat = $this->route('stat');

        if ($stat instanceof PlayerStat) {
            return $user->can('update', $stat);
        }

        return $user->hasRole('player') || $user->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'season' => ['required', 'string', 'max:20'],
            'matches' => ['required', 'integer', 'min:0', 'max:200'],
            'goals' => ['required', 'integer', 'min:0', 'max:200'],
            'assists' => ['required', 'integer', 'min:0', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function resolvePlayerProfile(): PlayerProfile
    {
        $user = $this->user();

        return $user->playerProfile ?? $user->playerProfile()->create();
    }
}
