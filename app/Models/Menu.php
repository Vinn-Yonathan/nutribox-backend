<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;
    protected $casts = ['is_featured' => 'boolean'];

    protected $fillable = [
        'name',
        'description',
        'image_src',
        'stock',
        'calories',
        'price',
        'is_featured',
    ];

    protected $table = 'menus';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    public function cartItem(): HasMany
    {
        return $this->hasMany(CartItem::class, 'menu_id', "id");
    }
}
