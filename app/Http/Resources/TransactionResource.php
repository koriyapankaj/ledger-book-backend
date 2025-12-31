<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'account' => new AccountResource($this->whenLoaded('account')),
            'to_account' => new AccountResource($this->whenLoaded('toAccount')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'transaction_date' => $this->transaction_date->format('Y-m-d'),
            'title' => $this->title,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}