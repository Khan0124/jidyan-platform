<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('upload media') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:122880', 'mimetypes:video/mp4,video/quicktime,video/x-matroska'],
            'chunk_index' => ['required_with:chunk_total', 'integer', 'min:0', 'lt:chunk_total'],
            'chunk_total' => ['nullable', 'integer', 'between:1,100'],
            'upload_uuid' => ['required_with:chunk_total', 'uuid'],
            'filename' => ['required_with:chunk_total', 'string', 'max:255'],
        ];
    }
}
