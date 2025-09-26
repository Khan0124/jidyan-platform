<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'media_id' => ['nullable', 'exists:player_media,id'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'note' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', Rule::in(['received', 'shortlisted', 'invited', 'rejected', 'signed'])],
        ];
    }
}
