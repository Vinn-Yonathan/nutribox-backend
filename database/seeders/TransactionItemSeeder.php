<?php

namespace Database\Seeders;

use App\Models\TransactionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionItem::create([
            'transaction_id' => 1,
            'menu_id' => 1,
            'quantity' => 1,
        ]);
        TransactionItem::create([
            'transaction_id' => 2,
            'menu_id' => 1,
            'quantity' => 1,
        ]);
        TransactionItem::create([
            'transaction_id' => 3,
            'menu_id' => 1,
            'quantity' => 1,
        ]);
    }
}
