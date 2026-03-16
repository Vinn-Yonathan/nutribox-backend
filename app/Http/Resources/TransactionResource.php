<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'menus' => $this->transactionItems->map(function ($item) {
                return [
                    'menu_id' => $item->menu_id,
                    'quantity' => $item->quantity
                ];
            }),
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_price' => $this->total_price
        ];
    }
}
