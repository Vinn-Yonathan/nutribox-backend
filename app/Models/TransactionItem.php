<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
     protected $fillable = [
        'transaction_id',
        'menu_id',
        'quantity'
    ];
    protected $table = "transaction_items";
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    public $timestamps = true;
    public $incrementing = true;

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function menu(): BelongsTo{
        return $this->belongsTo(Menu::class, "menu_id", "id");
    }

}
