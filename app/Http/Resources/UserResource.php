<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'financial_summary' => [
                'total_assets' => $this->getTotalAssets(),
                'total_liabilities' => $this->getTotalLiabilities(),
                'net_worth' => $this->getNetWorth(),
            ],
        ];
    }
}