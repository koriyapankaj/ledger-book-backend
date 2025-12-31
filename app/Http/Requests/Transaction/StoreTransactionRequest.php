<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense', 'transfer', 'lent', 'borrowed', 'repayment_in', 'repayment_out'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'account_id' => ['required', 'exists:accounts,id'],
            'to_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'transaction_date' => ['required', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Transaction type is required',
            'type.in' => 'Invalid transaction type',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than 0',
            'account_id.required' => 'Please select an account',
            'account_id.exists' => 'Selected account does not exist',
            'to_account_id.different' => 'Source and destination accounts must be different',
            'transaction_date.required' => 'Transaction date is required',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate transfer requires to_account_id
            if ($this->type === 'transfer' && empty($this->to_account_id)) {
                $validator->errors()->add('to_account_id', 'Destination account is required for transfers');
            }

            // Validate debt transactions require contact_id
            if (in_array($this->type, ['lent', 'borrowed', 'repayment_in', 'repayment_out']) && empty($this->contact_id)) {
                $validator->errors()->add('contact_id', 'Contact is required for debt transactions');
            }

            // Validate income/expense requires category_id
            if (in_array($this->type, ['income', 'expense']) && empty($this->category_id)) {
                $validator->errors()->add('category_id', 'Category is required for income and expense transactions');
            }
        });
    }
}