<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    /**
     * Create a new transaction with proper balance updates
     */
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            // Create the transaction
            $transaction = Transaction::create($data);

            // Update balances based on transaction type
            $this->updateBalances($transaction);

            return $transaction->load(['account', 'toAccount', 'category', 'contact']);
        });
    }

    /**
     * Update an existing transaction
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Reverse the old transaction effects
            $this->reverseBalances($transaction);

            // Update the transaction
            $transaction->update($data);
            $transaction->refresh();

            // Apply new transaction effects
            $this->updateBalances($transaction);

            return $transaction->load(['account', 'toAccount', 'category', 'contact']);
        });
    }

    /**
     * Delete a transaction and reverse its effects
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            // Reverse balance changes
            $this->reverseBalances($transaction);

            // Soft delete the transaction
            return $transaction->delete();
        });
    }

    /**
     * Update account and contact balances based on transaction type
     */
    private function updateBalances(Transaction $transaction): void
    {
        $amount = $transaction->amount;
        $account = Account::withoutGlobalScope('user')->find($transaction->account_id);

        switch ($transaction->type) {
            case 'income':
                // Money coming in - increase account balance
                $account->updateBalance($amount);
                break;

            case 'expense':
                // Money going out - decrease account balance
                $account->updateBalance(-$amount);
                break;

            case 'transfer':
                // Money moving between accounts
                $toAccount = Account::withoutGlobalScope('user')->find($transaction->to_account_id);
                $account->updateBalance(-$amount); // Decrease source
                $toAccount->updateBalance($amount); // Increase destination
                break;

            case 'lent':
                // You lent money to someone
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance(-$amount); // Money left your account
                $contact->updateBalance($amount);  // They owe you (positive balance)
                break;

            case 'borrowed':
                // You borrowed money from someone
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance($amount);   // Money came to your account
                $contact->updateBalance(-$amount);  // You owe them (negative balance)
                break;

            case 'repayment_in':
                // Someone paid you back
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance($amount);   // Money came in
                $contact->updateBalance(-$amount);  // Reduce their debt to you
                break;

            case 'repayment_out':
                // You paid someone back
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance(-$amount);  // Money went out
                $contact->updateBalance($amount);   // Reduce your debt to them
                break;
        }
    }

    /**
     * Reverse balance changes (for update/delete operations)
     */
    private function reverseBalances(Transaction $transaction): void
    {
        $amount = $transaction->amount;
        $account = Account::withoutGlobalScope('user')->find($transaction->account_id);

        switch ($transaction->type) {
            case 'income':
                $account->updateBalance(-$amount);
                break;

            case 'expense':
                $account->updateBalance($amount);
                break;

            case 'transfer':
                $toAccount = Account::withoutGlobalScope('user')->find($transaction->to_account_id);
                $account->updateBalance($amount);
                $toAccount->updateBalance(-$amount);
                break;

            case 'lent':
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance($amount);
                $contact->updateBalance(-$amount);
                break;

            case 'borrowed':
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance(-$amount);
                $contact->updateBalance($amount);
                break;

            case 'repayment_in':
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance(-$amount);
                $contact->updateBalance($amount);
                break;

            case 'repayment_out':
                $contact = Contact::withoutGlobalScope('user')->find($transaction->contact_id);
                $account->updateBalance($amount);
                $contact->updateBalance(-$amount);
                break;
        }
    }

    /**
     * Get transaction statistics for a user
     */
    public function getStatistics(string $period = 'month'): array
    {
        $query = Transaction::query();

        // Apply date filter based on period
        switch ($period) {
            case 'today':
                $query->whereDate('transaction_date', today());
                break;
            case 'week':
                $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $totalTransfers = (clone $query)->where('type', 'transfer')->sum('amount');

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_savings' => $totalIncome - $totalExpense,
            'total_transfers' => $totalTransfers,
            'period' => $period,
        ];
    }

    /**
     * Get spending by category
     */
    public function getSpendingByCategory(string $period = 'month'): array
    {
        $query = Transaction::with('category')
            ->where('type', 'expense');

        // Apply date filter
        switch ($period) {
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        return $query->get()
            ->groupBy('category_id')
            ->map(function ($transactions) {
                return [
                    'category' => $transactions->first()->category?->name ?? 'Uncategorized',
                    'total' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }
}