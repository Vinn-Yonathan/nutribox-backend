<?php

namespace Database\Seeders;

use App\Models\TransactionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionItemCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            TransactionItem::create([
                'transaction_id' => $i,
                'menu_id' => 1,
                'quantity' => 1,
            ]);
        }
        for ($i = 1; $i <= 3; $i++) {
            TransactionItem::create([
                'transaction_id' => $i + 5,
                'menu_id' => 1,
                'quantity' => 1,
            ]);
        }
        for ($i = 1; $i <= 2; $i++) {
            TransactionItem::create([
                'transaction_id' => $i + 8,
                'menu_id' => 1,
                'quantity' => 1,
            ]);
        }
    }
}
