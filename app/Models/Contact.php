<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Contact extends Model
{
    use HasFactory, SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'balance',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
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

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOwesYou(Builder $query): Builder
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeYouOwe(Builder $query): Builder
    {
        return $query->where('balance', '<', 0);
    }

    public function scopeSettled(Builder $query): Builder
    {
        return $query->where('balance', 0);
    }

    // Helper Methods
    public function updateBalance(float $amount): bool
    {
        $this->balance += $amount;
        return $this->save();
    }

    public function owesYou(): bool
    {
        return $this->balance > 0;
    }

    public function youOwe(): bool
    {
        return $this->balance < 0;
    }

    public function isSettled(): bool
    {
        return $this->balance == 0;
    }

    public function getBalanceStatus(): string
    {
        if ($this->balance > 0) {
            return 'owes_you';
        } elseif ($this->balance < 0) {
            return 'you_owe';
        }
        return 'settled';
    }
}