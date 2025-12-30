<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Account extends Model
{
    use HasFactory, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'subtype',
        'balance',
        'credit_limit',
        'account_number',
        'bank_name',
        'color',
        'icon',
        'is_active',
        'include_in_total',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'include_in_total' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAsset(Builder $query): Builder
    {
        return $query->where('type', 'asset');
    }

    public function scopeLiability(Builder $query): Builder
    {
        return $query->where('type', 'liability');
    }

    public function scopeOfSubtype(Builder $query, string $subtype): Builder
    {
        return $query->where('subtype', $subtype);
    }

    // Helper Methods
    public function updateBalance(float $amount): bool
    {
        $this->balance += $amount;
        return $this->save();
    }

    public function getAvailableCredit(): float
    {
        if ($this->credit_limit === null) {
            return 0;
        }
        
        return $this->credit_limit - abs($this->balance);
    }

    public function isOverLimit(): bool
    {
        if ($this->credit_limit === null) {
            return false;
        }
        
        return abs($this->balance) > $this->credit_limit;
    }
}