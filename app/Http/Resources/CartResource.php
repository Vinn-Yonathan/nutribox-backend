<?php

namespace App\Http\Resources;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'items' => $this->cartItems->map(function ($item) {
                return [
                    'menu_id' => $item->menu->id,
                    'name' => $item->menu->name,
                    'image_src' => $item->menu->image_src,
                    'price' => $item->menu->price,
                    'quantity' => $item->quantity,
                ];
            }),
            'total_price' => $this->total_price
        ];
    }
}
