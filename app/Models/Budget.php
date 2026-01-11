<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Budget extends Model
{
    use HasFactory, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'period',
        'start_date',
        'end_date',
        'is_active',
        'include_subcategories',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'include_subcategories' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                    ->where(function ($q) use ($today) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $today);
                    });
    }

    // Helper Methods
    public function getSpentAmount(): float
    {
        $query = Transaction::withoutUserScope()
            ->where('user_id', $this->user_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [
                $this->start_date,
                $this->end_date ?? now()
            ]);

        // If include_subcategories is true and category has children
        if ($this->include_subcategories) {
            $category = $this->category;

            // Get all child category IDs
            $categoryIds = [$this->category_id];

            if ($category && $category->children) {
                $childIds = $category->children()->pluck('id')->toArray();
                $categoryIds = array_merge($categoryIds, $childIds);
            }

            // Sum expenses from parent and all children
            $query->whereIn('category_id', $categoryIds);
        } else {
            // Only count expenses from the specific category
            $query->where('category_id', $this->category_id);
        }

        return (float) $query->sum('amount');
    }

    public function getRemainingAmount(): float
    {
        return $this->amount - $this->getSpentAmount();
    }

    public function getPercentageUsed(): float
    {
        if ($this->amount == 0) {
            return 0;
        }

        return ($this->getSpentAmount() / $this->amount) * 100;
    }

    public function isOverBudget(): bool
    {
        return $this->getSpentAmount() > $this->amount;
    }
}
