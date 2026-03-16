<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CartItemSeeder;
use Database\Seeders\CartSeeder;
use Database\Seeders\MenuCollectionSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TransactionCollectionSeeder;
use Database\Seeders\TransactionItemCollectionSeeder;
use Database\Seeders\TransactionItemSeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    public function testAddTransactionSuccess(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);

        Cart::first()->updateTotalPrice();
        $user = User::first();
        $cartItems = $user->cart->cartItems->map(function ($item) {
            return [
                'menu_id' => $item->menu_id,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $response = $this->actingAs($user)->post('/api/users/current/transactions', [
            'menus' => $cartItems,
            'payment_method' => 'debit',
            'total_price' => $user->cart->total_price
        ]);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'user_id' => $user->id,
                'menus' => $cartItems,
                'status' => 'pending',
                'payment_method' => 'debit',
                'total_price' => $user->cart->total_price
            ]
        ]);

        $this->assertEquals(9, Menu::first()->stock);
    }
    public function testAddTransactionFailedStock(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);

        Cart::first()->updateTotalPrice();
        $user = User::first();
        $cartItems = $user->cart->cartItems->map(function ($item) {
            return [
                'menu_id' => $item->menu_id,
                'quantity' => 1000000,
            ];
        })->toArray();

        $response = $this->actingAs($user)->post('/api/users/current/transactions', [
            'menus' => $cartItems,
            'payment_method' => 'debit',
            'total_price' => $user->cart->total_price
        ]);

        $response->assertStatus(409)->assertJson([
            'errors' => [
                'message' => ["Menu's stock is unavailable"]
            ]
        ]);
    }
    public function testAddTransactionFailedUnauthorized(): void
    {
        $this->seed([MenuSeeder::class]);

        $response = $this->postJson('/api/users/current/transactions', [
            'menus' => ['menu_id' => 1, 'quantity' => 1],
            'payment_method' => 'debit',
            'total_price' => Menu::first()->price
        ]);


        $response->assertStatus(401)->assertJson([
            'message' => "Unauthenticated."
        ]);

        $this->assertEquals(10, Menu::first()->stock);
    }

    public function testAddTransactionFailedValidationError(): void
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, CartSeeder::class, CartItemSeeder::class]);

        Cart::first()->updateTotalPrice();
        $user = User::first();
        $cartItems = $user->cart->cartItems->map(function ($item) {
            return [
                'menu_id' => $item->menu_id,
                'quantity' => 0,
            ];
        })->toArray();

        $response = $this->actingAs($user)->post('/api/users/current/transactions', [
            'menus' => $cartItems,
            'payment_method' => 'debit',
            'total_price' => $user->cart->total_price
        ]);

        $response->assertStatus(402)->assertJson([
            'errors' => [
                'message' => ["menus.0.quantity" => ["The menus.0.quantity field must be at least 1."]]
            ]
        ]);
    }

    public function testGetCurrentUserTransactionSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->get("/api/users/current/transactions/" . $user->transactions->first()->id);
        Log::info($user->transactions->first()->id);
        $response->assertStatus(200)->assertJson([
            'data' => [
                'user_id' => $user->id,
                'menus' => [['menu_id' => 1, "quantity" => 1]],
                'status' => 'pending',
                'payment_method' => 'credit_card',
                'total_price' => 10
            ]
        ]);
    }
    public function testGetCurrentUserTransactionFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->get("/api/users/current/transactions/" . ($user->transactions->first()->id + 1000));
        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['Data not found'],
            ]
        ]);
    }
    public function testGetCurrentUserTransactionFailedOtherUser()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->get("/api/users/current/transactions/2");
        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['Data not found'],
            ]
        ]);

        $this->assertNotEquals($user->id, Transaction::find(2)->user_id);
    }
    public function testGetCurrentUserTransactionFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $response = $this->getJson("/api/users/current/transactions/1");

        $response->assertStatus(401)->assertJson([
            'message' => "Unauthenticated."
        ]);
    }
    public function testGetCurrentUserTransactionsSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->get("/api/users/current/transactions")->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));
        self::assertEquals(5, $response['meta']['total']);
    }
    public function testGetCurrentUserTransactionsFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class]);
        $user = User::first();
        $this->actingAs($user)->get("/api/users/current/transactions")->assertStatus(404)->assertJson([
            'errors' => ['message' => ['Data not found']]
        ]);
    }
    public function testGetCurrentUserTransactionsFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $this->getJson("/api/users/current/transactions")->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
    public function testUpdateCurrentUserTransactionSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->patch("/api/users/current/transactions/" . $user->transactions->first()->id);
        $response->assertStatus(200)->assertJson([
            'data' => [
                'user_id' => $user->id,
                'menus' => [['menu_id' => 1, "quantity" => 1]],
                'status' => 'paid',
                'payment_method' => 'credit_card',
                'total_price' => 10
            ]
        ]);
    }
    public function testUpdateCurrentUserTransactionFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $response = $this->patchJson("/api/users/current/transactions/1");
        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
    public function testUpdateCurrentUserTransactionFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->patchJson("/api/users/current/transactions/100000");
        $response->assertStatus(404)->assertJson([
            'errors' => ['message' => ['Data not found']]
        ]);
    }
    public function testUpdateCurrentUserTransactionFailedOtherUser()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->patchJson("/api/users/current/transactions/2");
        $response->assertStatus(404)->assertJson([
            'errors' => ['message' => ['Data not found']]
        ]);

        $this->assertNotEquals($user->id, Transaction::find(2)->user_id);
    }

    public function testDeleteCurrentUserTransactionSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->delete("/api/users/current/transactions/" . $user->transactions->first()->id);
        $response->assertStatus(200)->assertJson([
            'data' => 'true'
        ]);

        $this->assertEquals(11, Menu::find(1)->stock);
    }
    public function testDeleteCurrentUserTransactionFailedStatus()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->delete("/api/users/current/transactions/3");
        $response->assertStatus(409)->assertJson([
            'errors' => ['message' => ["Paid transactions cannot be cancelled"]]
        ]);
        $this->assertEquals(10, Menu::find(1)->stock);
    }
    public function testDeleteCurrentUserTransactionFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::first();
        $response = $this->actingAs($user)->delete("/api/users/current/transactions/1000");
        $response->assertStatus(404)->assertJson([
            'errors' => ['message' => ['Data not found']]
        ]);
    }
    public function testDeleteCurrentUserTransactionFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $response = $this->deleteJson("/api/users/current/transactions/1000");
        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testGetTransactionSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();
        $response = $this->actingAs($admin)->getJson("/api/transactions/{$user->transactions->first()->id}");
        $response->assertStatus(200)->assertJson([
            'data' => [
                'user_id' => $user->id,
                'menus' => [['menu_id' => 1, "quantity" => 1]],
                'status' => 'pending',
                'payment_method' => 'credit_card',
                'total_price' => 10
            ]
        ]);
    }

    public function testGetTransactionFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->getJson("/api/transactions/1000000");
        $response->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['Data not found']
            ]
        ]);
    }

    public function testGetTransactionFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $response = $this->getJson("/api/transactions/1");
        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
    public function testGetTransactionFailedForbidden()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::where('role', 'user')->first();
        $response = $this->actingAs($user)->getJson("/api/transactions/2");
        $response->assertStatus(403)->assertJson([
            'errors' => [
                'message' => ['Forbidden']
            ]
        ]);
    }

    public function testDeleteTransactionSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::where('role', 'user')->first();
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->delete("/api/transactions/" . $user->transactions->first()->id);
        $response->assertStatus(200)->assertJson([
            'data' => 'true'
        ]);

        $this->assertEquals(11, Menu::find(1)->stock);
    }

    public function testDeleteTransactionFailedStatus()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->delete("/api/transactions/3");
        $response->assertStatus(409)->assertJson([
            'errors' => ['message' => ["Paid transactions cannot be cancelled"]]
        ]);
        $this->assertEquals(10, Menu::find(1)->stock);
    }
    public function testDeleteTransactionFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->delete("/api/transactions/1000");
        $response->assertStatus(404)->assertJson([
            'errors' => ['message' => ['Data not found']]
        ]);
    }

    public function testDeleteTransactionFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $response = $this->deleteJson("/api/transactions/1000");
        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
    public function testDeleteTransactionFailedForbidden()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionSeeder::class, TransactionItemSeeder::class]);
        $user = User::where('role', 'user')->first();
        $response = $this->actingAs($user)->deleteJson("/api/transactions/1");
        $response->assertStatus(403)->assertJson([
            'errors' => [
                'message' => ['Forbidden']
            ]
        ]);
    }

    public function testGetTransactionsSuccess()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions")->assertStatus(200)->json();
        self::assertEquals(10, count($response['data']));
        self::assertEquals(10, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByUserId()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?user_id=2")->assertStatus(200)->json();
        self::assertEquals(5, count($response['data']));
        self::assertEquals(5, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByMinPrice()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?min_price=7")->assertStatus(200)->json();
        self::assertEquals(8, count($response['data']));
        self::assertEquals(8, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByMaxPrice()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?max_price=5")->assertStatus(200)->json();
        self::assertEquals(2, count($response['data']));
        self::assertEquals(2, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByPaymentMethod()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?payment_method=gopay")->assertStatus(200)->json();
        self::assertEquals(2, count($response['data']));
        self::assertEquals(2, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByStatus()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?status=paid")->assertStatus(200)->json();
        self::assertEquals(3, count($response['data']));
        self::assertEquals(3, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByPerPage()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?size=7")->assertStatus(200)->json();
        self::assertEquals(7, count($response['data']));
        self::assertEquals(10, $response['meta']['total']);
    }
    public function testGetTransactionsSuccessByPage()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?page=2&size=2")->assertStatus(200)->json();
        self::assertEquals(2, count($response['data']));
        self::assertEquals(10, $response['meta']['total']);
    }

    public function testGetTransactionsSuccessByIncludeDeleted()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        Log::info(Transaction::withTrashed()->get());
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get("/api/transactions?include_deleted=true")->assertStatus(200)->json();
        self::assertEquals(10, count($response['data']));
        self::assertEquals(11, $response['meta']['total']);
    }

    public function testGetTransactionsFailedUnauthorized()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $this->getJson("/api/transactions")->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
    public function testGetTransactionsFailedForbidden()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, TransactionCollectionSeeder::class, TransactionItemCollectionSeeder::class]);
        $user = User::first();
        $this->actingAs($user)->getJson("/api/transactions")->assertStatus(403)->assertJson([
            'errors' => [
                'message' => ['Forbidden']
            ]
        ]);
    }
    public function testGetTransactionsFailedNotFound()
    {
        $this->seed([MenuSeeder::class, UserSeeder::class, AdminSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)->get("/api/transactions")->assertStatus(404)->assertJson([
            'errors' => ['message' => ['Data not found']]
        ]);
    }
}
