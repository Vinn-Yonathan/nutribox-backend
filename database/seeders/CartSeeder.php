<?php

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cart::create([
            'user_id' => 1,
            'total_price' => 0
        ]);

        // $this->call([CartItemSeeder::class]);
        // $cart = Cart::first();
        // $cart->update([
        //     'total_price' => $cart->cartItems->sum(function ($item) {
        //         return $item->quantity * $item->menu->price;
        //     })
        // ]);
    }
}
