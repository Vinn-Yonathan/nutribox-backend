<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Menu;
use App\Models\User;
use Database\Seeders\CartItemSeeder;
use Database\Seeders\CartSeeder;
use Database\Seeders\MenuCollectionSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function testAddCartAndItemSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user)->post('/api/carts/items', [
            'menu_id' => 1,
            'quantity' => 1
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'user_id' => 1,
                'items' => [
                    [
                        'menu_id' => 1,
                        'name' => 'Sunburst Bento',
                        'price' => 10,
                        'quantity' => 1
                    ],
                ],
                'total_price' => 10
            ]
        ]);
    }
    public function testAddItemSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user)->post('/api/carts/items', [
            'menu_id' => 1,
            'quantity' => 1
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'user_id' => 1,
                'items' => [
                    [
                        'menu_id' => 1,
                        'name' => 'Sunburst Bento',
                        'price' => 10,
                        'quantity' => 1
                    ],
                ],
                'total_price' => 10
            ]
        ]);
    }
    public function testAddExistingItemSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->post('/api/carts/items', [
            'menu_id' => 1,
            'quantity' => 1
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'user_id' => 1,
                'items' => [
                    [
                        'menu_id' => 1,
                        'name' => 'Sunburst Bento',
                        'price' => 10,
                        'quantity' => 2
                    ],
                ],
                'total_price' => 20
            ]
        ]);
    }
    public function testAddMultipleItemsSuccess(): void
    {
        $this->seed([MenuCollectionSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->post('/api/carts/items', [
            'items' => [
                [
                    'menu_id' => 1,
                    'quantity' => 1
                ],
                [
                    'menu_id' => 2,
                    'quantity' => 3
                ],
            ]
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'user_id' => 1,
                'items' => [
                    [
                        'menu_id' => 1,
                        'name' => 'Bento1',
                        'price' => 11,
                        'quantity' => 2
                    ],
                    [
                        'menu_id' => 2,
                        'name' => 'Bento2',
                        'price' => 12,
                        'quantity' => 3
                    ],
                ],
                'total_price' => 58
            ]
        ]);
    }

    public function testAddItemFailedValidation(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user)->post('/api/carts/items', [
            'quantity' => 0
        ]);

        $response->assertStatus(400)->assertJson([
            'errors' => [
                'menu_id' => ["The menu id field is required."],
                'quantity' => ["The quantity field must be at least 1."]
            ]
        ]);
    }

    public function testGetItemSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);

        Cart::first()->updateTotalPrice();
        $user = User::first();
        $response = $this->actingAs($user)->get('/api/carts');

        $response->assertStatus(200)->assertJson([
            'data' => [
                'user_id' => 1,
                'items' => [
                    [
                        'menu_id' => 1,
                        'name' => 'Sunburst Bento',
                        'price' => 10,
                        'quantity' => 1
                    ],
                ],
                'total_price' => 10
            ]
        ]);
    }
    public function testGetItemNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class]);

        Cart::first()->updateTotalPrice();
        $user = User::first();
        $response = $this->actingAs($user)->get('/api/carts');

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found'],
            ]
        ]);
    }

    public function testUpdateItemSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->putJson('/api/carts/items/1', [
            'quantity' => 5
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'user_id' => 1,
                'items' => [
                    [
                        'menu_id' => 1,
                        'name' => 'Sunburst Bento',
                        'price' => 10,
                        'quantity' => 5
                    ],
                ],
                'total_price' => 50
            ]
        ]);
    }

    public function testUpdateItemFailedValidation(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->putJson('/api/carts/items/1', [
            'quantity' => -1
        ]);

        $response->assertStatus(400)->assertJson([
            'errors' => [
                'quantity' => ["The quantity field must be at least 1."],
            ]
        ]);
    }
    public function testUpdateItemNotFound(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->putJson('/api/carts/items/2', [
            'quantity' => 5
        ]);

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['not found']
            ]
        ]);
    }

    public function testDeleteItemSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->delete('/api/carts/items/1');

        $response->assertStatus(200)->assertJson([
            'data' => true
        ]);

        $cart = Cart::first();
        $this->assertCount(0, $cart->cartItems);
    }
    public function testDeleteItemNotFound(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->delete('/api/carts/items/2');

        $response->assertStatus(404)->assertJson([
            'errors' => ['message' => ['not found']]
        ]);
    }

    public function testDeleteCartSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->delete('/api/carts');

        $response->assertStatus(200)->assertJson([
            'data' => true
        ]);

        $cart = Cart::first();
        $cartItems = CartItem::first();
        $this->assertNull($cart);
        $this->assertNull($cartItems);
    }
    public function testDeleteCartNotFound(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        // Log::info($user->cart->load('cartItems.menu'));

        $response = $this->actingAs($user)->delete('/api/carts');

        $response->assertStatus(404)->assertJson([
            'errors' => ['message' => ['not found']]
        ]);
    }

}
