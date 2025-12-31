<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'subtype' => $this->subtype,
            'balance' => (float) $this->balance,
            'credit_limit' => $this->credit_limit ? (float) $this->credit_limit : null,
            'available_credit' => $this->credit_limit ? $this->getAvailableCredit() : null,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'include_in_total' => $this->include_in_total,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}