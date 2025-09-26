<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage opportunities') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'array'],
            'requirements.*.label' => ['required_with:requirements', 'string', 'max:120'],
            'requirements.*.value' => ['nullable', 'string', 'max:255'],
            'requirements_json' => ['nullable', 'string'],
            'location_city' => ['required', 'string', 'max:120'],
            'location_country' => ['required', 'string', 'max:120'],
            'deadline_at' => ['required', 'date', 'after:today'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'visibility' => ['required', Rule::in(['public', 'private'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('requirements_json') && blank($this->input('requirements'))) {
            $decoded = json_decode($this->input('requirements_json'), true) ?: [];
            $this->merge(['requirements' => $decoded]);
        }
    }
}
