<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'agent_id' => ['required', 'exists:agents,id'],
            ];
        }

        return [
            'status' => ['required', Rule::in(['pending', 'active', 'revoked'])],
        ];
    }
}
