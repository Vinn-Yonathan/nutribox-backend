<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'user_id' => 1,
            "payment_method" => 'credit_card',
            "status" => "pending",
            "total_price" => 10
        ]);
        Transaction::create([
            'user_id' => 2,
            "payment_method" => 'credit_card',
            "status" => "pending",
            "total_price" => 10
        ]);
        Transaction::create([
            'user_id' => 1,
            "payment_method" => 'credit_card',
            "status" => "paid",
            "total_price" => 10
        ]);
    }
}
