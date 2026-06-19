<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                Rule::exists('categories', 'id')->where('user_id', $this->user()?->id),
            ],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'period' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'include_subcategories' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Selected category does not exist',
            'amount.min' => 'Amount must be greater than 0',
            'period.in' => 'Period must be daily, weekly, monthly or yearly',
            'end_date.after' => 'End date must be after the start date',
        ];
    }
}
