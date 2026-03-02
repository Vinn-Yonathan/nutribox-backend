<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'total_price'
    ];
    protected $table = "carts";
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function updateTotalPrice()
    {
        $this->load('cartItems.menu');
        $this->update([
            'total_price' => $this->cartItems->sum(function ($item) {
                return $item->quantity * $item->menu->price;
            })
        ]);
    }
}
