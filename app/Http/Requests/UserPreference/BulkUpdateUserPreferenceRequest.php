<?php

declare(strict_types=1);

namespace App\Http\Requests\UserPreference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateUserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clear_existing' => 'boolean',
            'preferences' => 'required|array|min:1',
            'preferences.*.category_id' => [
                'nullable',
                'required_without:preferences.*.source_id',
                'exists:categories,id',
            ],
            'preferences.*.source_id' => [
                'nullable',
                'required_without:preferences.*.category_id',
                'exists:sources,id',
            ],
            'preferences.*.preference_type' => [
                'required',
                'string',
                Rule::in(['favorite', 'blocked']),
            ],
            'preferences.*.priority' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'preferences.required' => 'At least one preference must be specified',
            'preferences.*.category_id.required_without' => 'Either category or source must be specified for each preference',
            'preferences.*.source_id.required_without' => 'Either category or source must be specified for each preference',
            'preferences.*.preference_type.in' => 'Preference type must be either favorite or blocked',
        ];
    }
} 