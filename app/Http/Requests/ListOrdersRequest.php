<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'affiliate_id' => ['nullable', 'integer', 'exists:affiliates,id'],

            'status' => [
                'nullable',
                'string',
                Rule::in(['pending', 'approved', 'cancelled', 'refunded']),
            ],

            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],

            'min_value' => ['nullable', 'numeric', 'min:0'],
            'max_value' => ['nullable', 'numeric', 'min:0'],

            'sort_by' => [
                'nullable',
                'string',
                Rule::in(['id', 'total', 'status', 'created_at', 'ordered_at']),
            ],

            'sort_dir' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],

            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $min = $this->input('min_value');
            $max = $this->input('max_value');

            if (
                $min !== null &&
                $min !== '' &&
                $max !== null &&
                $max !== '' &&
                (float) $max < (float) $min
            ) {
                $validator->errors()->add(
                    'max_value',
                    'O valor máximo deve ser maior ou igual ao valor mínimo.'
                );
            }
        });
    }
}
