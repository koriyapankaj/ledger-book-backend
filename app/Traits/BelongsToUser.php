<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToUser
{
    /**
     * Boot the trait and add global scope for user filtering
     */
    protected static function bootBelongsToUser(): void
    {
        // Automatically set user_id on creating
        static::creating(function (Model $model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });

        // Add global scope to filter by user_id
        static::addGlobalScope('user_scope', function (Builder $builder) {
            // For API routes with Sanctum token authentication
            if (auth('sanctum')->check()) {
                $builder->where($builder->getModel()->getTable() . '.user_id', auth('sanctum')->id());
            }
        });
    }

    /**
     * Relationship to User model
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope to bypass user filtering (use with caution - for admin purposes)
     */
    public function scopeWithoutUserScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('user_scope');
    }

    /**
     * Scope to filter by specific user (useful for admin features)
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->withoutGlobalScope('user_scope')
                    ->where($query->getModel()->getTable() . '.user_id', $userId);
    }
}