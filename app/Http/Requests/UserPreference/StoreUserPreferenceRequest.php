<?php

declare(strict_types=1);

namespace App\Http\Requests\UserPreference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'required_without:source_id',
                'exists:categories,id',
            ],
            'source_id' => [
                'nullable',
                'required_without:category_id',
                'exists:sources,id',
            ],
            'preference_type' => [
                'required',
                'string',
                Rule::in(['favorite', 'blocked']),
            ],
            'priority' => [
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
            'category_id.required_without' => 'Either category or source must be specified',
            'source_id.required_without' => 'Either category or source must be specified',
            'preference_type.in' => 'Preference type must be either favorite or blocked',
        ];
    }
} 