<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserCollectionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess(): void
    {
        $response = $this->post('api/users', [
            'first_name' => 'Cevin',
            'last_name' => 'Yonathan',
            'email' => 'Yonathan@mail.co',
            'password' => 'Yonathan1.',
            'address' => 'Jalan Melati'
        ]);

        $response->assertStatus(201)->assertJson(
            [
                'data' => [
                    'first_name' => 'Cevin',
                    'last_name' => 'Yonathan',
                    'email' => 'Yonathan@mail.co',
                    'address' => 'Jalan Melati'
                ]
            ]
        );
    }
    public function testRegisterFailed(): void
    {
        $response = $this->post('api/users', [
            'first_name' => 'Cevin',
            'last_name' => 'Yonathan',
            'password' => 'Yonathan1.',
            'address' => 'Jalan Melati'
        ]);

        $response->assertStatus(400)->assertJson(
            [
                'errors' => [
                    'email' => ['The email field is required.'],
                ]
            ]
        );
    }
    public function testRegisterFailedDuplicateEmail(): void
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('api/users', [
            'first_name' => 'Cevin',
            'last_name' => 'Yonathan',
            'email' => 'budi@mail.co',
            'password' => 'Yonathan1.',
            'address' => 'Jalan Melati'
        ]);

        $response->assertStatus(400)->assertJson(
            [
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ]
            ]
        );
    }

    public function testLoginSuccess(): void
    {
        $this->seed([UserSeeder::class]);

        $response = $this->post('api/users/login', [
            'email' => 'budi@mail.co',
            'password' => 'budigantenk1.'
        ]);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'user' => [
                    'first_name' => 'Budi',
                    'last_name' => 'Gantenk',
                    'email' => 'budi@mail.co',
                    'address' => 'Jalan Gantenk'
                ]
            ]
        ])->assertJsonStructure(['data' => ['access_token']]);
    }
    public function testLoginFailedValidationError(): void
    {
        $this->seed([UserSeeder::class]);

        $response = $this->post('api/users/login', [
            'email' => 'budi@mail.co'
        ]);


        $response->assertStatus(400)->assertJson(
            [
                'errors' => [
                    'password' => ['The password field is required.'],
                ]
            ]
        );
    }
    public function testLoginFailedWrongData(): void
    {
        $this->seed([UserSeeder::class]);

        $response = $this->post('api/users/login', [
            'email' => 'budi@mail.co',
            'password' => 'passwordsalah'
        ]);


        $response->assertStatus(401)->assertJson(
            [
                'errors' => [
                    'message' => ['Email or password wrong']
                ]
            ]
        );
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user, 'sanctum')->patch('api/users/current', [
            'first_name' => 'Cevin',
            'last_name' => 'Yonathan',
            'email' => 'Yonathan@mail.co',
            'password' => 'Yonathan1.',
            'address' => 'Jalan Melati'
        ]);

        $response->assertStatus(200)->assertJson(
            [
                'data' => [
                    'first_name' => 'Cevin',
                    'last_name' => 'Yonathan',
                    'email' => 'Yonathan@mail.co',
                    'address' => 'Jalan Melati',
                    'role' => 'user'
                ]
            ]
        );
    }
    public function testUpdateFailedDuplicateEmail()
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user)->patch('api/users/current', [
            'email' => 'baik@mail.co',
        ]);

        $response->assertStatus(400)->assertJson(
            [
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ]
            ]
        );
    }
    public function testUpdateFailedUnauthorized()
    {
        $response = $this->patchJson('api/users/current', [
            'first_name' => 'Cevin',
            'last_name' => 'Yonathan',
            'email' => 'Yonathan@mail.co',
            'password' => 'Yonathan1.',
            'address' => 'Jalan Melati'
        ]);

        $response->assertStatus(401)->assertJson(
            [
                'message' => 'Unauthenticated.',
            ]
        );
    }

    public function testGetCurrent()
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user)->get('api/users/current');

        $response->assertStatus(200)->assertJson(
            [
                'data' => [
                    'first_name' => 'Budi',
                    'last_name' => 'Gantenk',
                    'email' => 'budi@mail.co',
                    'address' => 'Jalan Gantenk'
                ]
            ]
        );
    }

    public function testGetCurrentUnauthorized()
    {
        $response = $this->getJson('api/users/current');

        $response->assertStatus(401)->assertJson(
            [
                'message' => 'Unauthenticated.'
            ]
        );
    }

    public function testGetByIdSuccess()
    {
        $this->seed([UserSeeder::class, AdminSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin, 'sanctum')->get('api/users/' . $user->id);

        $response->assertStatus(200)->assertJson(
            [
                'data' => [
                    'first_name' => 'Budi',
                    'last_name' => 'Gantenk',
                    'email' => 'budi@mail.co',
                    'address' => 'Jalan Gantenk'
                ]
            ]
        );
    }

    public function testGetByIdNotFound()
    {
        $this->seed([UserSeeder::class, AdminSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin, 'sanctum')->get('api/users/' . $user->id + 100000);

        $response->assertStatus(404)->assertJson(
            [
                'errors' => [
                    'message' => ['User not found'],
                ]
            ]
        );
    }

    public function testLogoutCurrent()
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        Sanctum::actingAs($user, ['*']);
        $response = $this->postJson('api/users/current');

        $response->assertStatus(200)->assertJson(
            [
                'data' => 'true'
            ]
        );
    }
    public function testLogoutCurrentUnauthorized()
    {
        $response = $this->postJson('api/users/current');

        $response->assertStatus(401)->assertJson(
            [
                'message' => 'Unauthenticated.'
            ]
        );
    }

    public function testGetAllUsers()
    {
        $this->seed([UserCollectionSeeder::class, AdminSeeder::class]);
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin)->get('api/users')->assertStatus(200)->json();
        Log::info($response);
        self::assertEquals(10, count($response['data']));
        self::assertEquals(16, $response['meta']['total']);
    }
    public function testGetUsersFilterByName()
    {
        $this->seed([UserCollectionSeeder::class, AdminSeeder::class]);
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin)->get('api/users?name=1')->assertStatus(200)->json();

        self::assertEquals(7, count($response['data']));
    }
    public function testGetUsersFilterByPage()
    {
        $this->seed([UserCollectionSeeder::class, AdminSeeder::class]);
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin)->get('api/users?page=2')->assertStatus(200)->json();

        self::assertEquals(6, count($response['data']));
        self::assertEquals(16, $response['meta']['total']);
    }
    public function testGetUsersFilterBySize()
    {
        $this->seed([UserCollectionSeeder::class, AdminSeeder::class]);
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin)->get('api/users?size=2')->assertStatus(200)->json();

        self::assertEquals(2, count($response['data']));
        self::assertEquals(8, $response['meta']['last_page']);
    }
    public function testGetAllUsersFailedUnauthorized()
    {
        $this->seed([UserCollectionSeeder::class]);

        $response = $this->getJson('api/users');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }
    public function testGetAllUsersFailedForbidden()
    {
        $this->seed([UserCollectionSeeder::class]);
        $user = User::where('last_name', 'Last 1')->first();

        $response = $this->actingAs($user)->getJson('api/users');

        $response->assertStatus(403)
            ->assertJson([
                'errors' => [
                    'message' => "Forbidden"
                ]
            ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, AdminSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin, 'sanctum')->delete('/api/users/' . $user->id)->assertStatus(200)->assertJson(
            ['data' => 'true']
        );
    }
    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, AdminSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();
        $admin = User::where('last_name', 'admin')->first();

        $response = $this->actingAs($admin, 'sanctum')->delete('/api/users/' . $user->id + 1000000)->assertStatus(404)->assertJson(
            ['errors' => ['message' => ['User not found']]]
        );
    }
    public function testDeleteForbidden()
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->actingAs($user, 'sanctum')->delete('/api/users/' . $user->id)->assertStatus(403)->assertJson([
            'errors' => [
                'message' => "Forbidden"
            ]
        ]);
    }
    public function testDeleteUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $user = User::where('last_name', 'Gantenk')->first();

        $response = $this->deleteJson('/api/users/' . $user->id)->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
}
