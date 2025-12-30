<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'account_id',
        'to_account_id',
        'category_id',
        'contact_id',
        'transaction_date',
        'title',
        'description',
        'reference_number',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'metadata' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    // Scopes
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeTransfer(Builder $query): Builder
    {
        return $query->where('type', 'transfer');
    }

    public function scopeDateRange(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    public function scopeThisYear(Builder $query): Builder
    {
        return $query->whereYear('transaction_date', now()->year);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('transaction_date', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isDebtRelated(): bool
    {
        return in_array($this->type, ['lent', 'borrowed', 'repayment_in', 'repayment_out']);
    }
}