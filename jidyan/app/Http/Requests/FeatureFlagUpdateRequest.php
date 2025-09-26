<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeatureFlagUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) optional($this->user())->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
