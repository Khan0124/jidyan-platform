<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reportable_type' => ['required', 'string', Rule::in(array_keys($this->typeMap()))],
            'reportable_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public function typeMap(): array
    {
        return [
            'player_profile' => \App\Models\PlayerProfile::class,
            'player_media' => \App\Models\PlayerMedia::class,
            'opportunity' => \App\Models\Opportunity::class,
        ];
    }
}
