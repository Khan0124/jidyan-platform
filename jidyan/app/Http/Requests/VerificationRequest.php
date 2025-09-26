<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class VerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'type' => ['required', Rule::in(['identity', 'academy'])],
                'document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(10 * 1024)],
            ];
        }

        return [
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
