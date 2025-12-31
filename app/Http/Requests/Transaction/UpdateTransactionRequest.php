<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::in(['income', 'expense', 'transfer', 'lent', 'borrowed', 'repayment_in', 'repayment_out'])],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'account_id' => ['sometimes', 'exists:accounts,id'],
            'to_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'transaction_date' => ['sometimes', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}