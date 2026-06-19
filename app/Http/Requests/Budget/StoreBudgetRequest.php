<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $this->user()?->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'include_subcategories' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            'amount.required' => 'Budget amount is required',
            'amount.min' => 'Amount must be greater than 0',
            'period.required' => 'Period is required',
            'period.in' => 'Period must be daily, weekly, monthly or yearly',
            'start_date.required' => 'Start date is required',
            'end_date.after' => 'End date must be after the start date',
        ];
    }
}
