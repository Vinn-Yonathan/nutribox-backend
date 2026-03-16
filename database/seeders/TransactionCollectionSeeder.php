<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Transaction::create([
                'user_id' => 1,
                "payment_method" => 'credit_card',
                "status" => "pending",
                "total_price" => 10
            ]);
        }
        for ($i = 1; $i <= 3; $i++) {
            Transaction::create([
                'user_id' => 2,
                "payment_method" => 'debit',
                "status" => "paid",
                "total_price" => 10
            ]);
        }
        for ($i = 1; $i <= 2; $i++) {
            Transaction::create([
                'user_id' => 2,
                "payment_method" => 'gopay',
                "status" => "pending",
                "total_price" => 5
            ]);
        }

        Transaction::create([
            'user_id' => 2,
            "payment_method" => 'gopay',
            "status" => "pending",
            "total_price" => 5,
            "deleted_at" => '2024-12-01T07:23:45.000000Z'
        ]);
    }
}
