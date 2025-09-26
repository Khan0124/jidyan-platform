<?php

namespace App\Http\Requests;

use App\Models\PlayerProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update player profile') ?? false;
    }

    public function rules(): array
    {
        return [
            'dob' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'height_cm' => ['nullable', 'integer', 'between:120,230'],
            'weight_kg' => ['nullable', 'integer', 'between:40,130'],
            'position' => ['nullable', 'string', 'max:120'],
            'preferred_foot' => ['nullable', Rule::in(['left', 'right', 'both'])],
            'current_club' => ['nullable', 'string', 'max:120'],
            'previous_clubs' => ['nullable', 'array', 'max:10'],
            'previous_clubs.*' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'injuries' => ['nullable', 'array', 'max:10'],
            'injuries.*' => ['nullable', 'string', 'max:255'],
            'achievements' => ['nullable', 'array', 'max:10'],
            'achievements.*' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
            'availability' => ['required', Rule::in(PlayerProfile::AVAILABILITY_OPTIONS)],
            'available_from' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_roles' => ['nullable', 'array', 'max:5'],
            'preferred_roles.*' => ['nullable', 'string', 'max:120'],
        ];
    }
}
