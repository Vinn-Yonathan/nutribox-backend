<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'midtrans_id' => Str::uuid(),
            'user_id' => 1,
            "status" => "pending",
            "total_price" => 10
        ]);
        Transaction::create([
            'midtrans_id' => Str::uuid(),
            'user_id' => 2,
            "status" => "pending",
            "total_price" => 10
        ]);
        Transaction::create([
            'midtrans_id' => Str::uuid(),
            'user_id' => 1,
            "status" => "paid",
            "total_price" => 10
        ]);
    }
}
