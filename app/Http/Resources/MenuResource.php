<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image_src' => $this->image_src,
            'stock' => $this->stock,
            'calories' => $this->calories,
            'price' => $this->price,
            'is_featured' => $this->is_featured,
        ];
    }
}
