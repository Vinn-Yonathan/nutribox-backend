<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\MenuCollectionSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function testAddMenuSuccess(): void
    {
        $this->seed([AdminSeeder::class]);
        $admin = User::where('role', 'admin')->first();

        Storage::fake('public');
        $response = $this->actingAs($admin)->post('/api/menus', [
            "name" => "Sunburst Bento",
            "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
            "image" => UploadedFile::fake()->create('Sunburst.jpg', 100, 'image/jpeg'),
            "stock" => 10,
            "calories" => 350,
            "price" => 10,
            "is_featured" => true
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    "name" => "Sunburst Bento",
                    "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
                    "stock" => 10,
                    "calories" => 350,
                    "price" => 10,
                    "is_featured" => true
                ]
            ]);
    }
    public function testAddMenuFailedValidation(): void
    {
        $this->seed([AdminSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->post('/api/menus', [
            "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
            "image_src" => "src/assets/img/sunburst.jpg",
            "stock" => 10,
            "calories" => 350,
            "price" => 10,
            "is_featured" => true
        ]);

        $response->assertStatus(400)->assertJson([
            'errors' => ['name' => ['The name field is required.']]
        ]);
    }
    public function testAddMenuFailedForbidden(): void
    {
        $this->seed([UserSeeder::class]);
        $admin = User::where('role', 'user')->first();
        $response = $this->actingAs($admin)->post('/api/menus', [
            "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
            "image_src" => "src/assets/img/sunburst.jpg",
            "stock" => 10,
            "calories" => 350,
            "price" => 10,
            "is_featured" => true
        ]);

        $response->assertStatus(403)->assertJson([
            'errors' => ['message' => 'Forbidden']
        ]);
    }
    public function testAddMenuFailedUnauthenticated(): void
    {
        $response = $this->postJson('/api/menus', [
            "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
            "image_src" => "src/assets/img/sunburst.jpg",
            "stock" => 10,
            "calories" => 350,
            "price" => 10,
            "is_featured" => true
        ]);

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testGetMenuSuccess(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();
        $response = $this->actingAs($admin)->get('/api/menus/' . $menu->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    "name" => "Sunburst Bento",
                    "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
                    "image_src" => "src/assets/img/sunburst.jpg",
                    "stock" => 10,
                    "calories" => 350,
                    "price" => 10,
                    "is_featured" => true
                ]
            ]);
    }
    public function testGetMenuNotFound(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();
        $response = $this->actingAs($admin)->get('/api/menus/' . $menu->id + 1);

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    "message" => ["not found"],
                ]
            ]);
    }

    public function testUpdateMenuSuccess(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->actingAs($admin)->patch('/api/menus/' . $menu->id, [
            "name" => "Sunburst Bento EX",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    "name" => "Sunburst Bento EX",
                    "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
                    "image_src" => "src/assets/img/sunburst.jpg",
                    "stock" => 10,
                    "calories" => 350,
                    "price" => 10,
                    "is_featured" => true
                ]
            ]);
    }
    public function testUpdateMenuFailedValidation(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->actingAs($admin)->patch('/api/menus/' . $menu->id, [
            "name" => "",
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'errors' => [
                    "name" => ["The name field is required."]
                ]
            ]);
    }
    public function testUpdateMenuFailedNotFound(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->actingAs($admin)->patch('/api/menus/' . $menu->id + 1, [
            "name" => "Sunburst Bento EX"
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    "message" => ['not found']
                ]
            ]);
    }
    public function testDeleteMenuSuccess(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->actingAs($admin)->delete('/api/menus/' . $menu->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => 'true'
            ]);
    }

    public function testDeleteMenuFailedNotFound(): void
    {
        $this->seed([AdminSeeder::class, MenuSeeder::class]);
        $admin = User::where('role', 'admin')->first();
        $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->actingAs($admin)->delete('/api/menus/' . $menu->id + 1);

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    "message" => ['not found']
                ]
            ]);
    }

    public function testSearchMenuByName(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?name=bento1')->assertStatus(200)->json();

        self::assertEquals(10, count($response['data']));
        self::assertEquals(11, $response['meta']['total']);
    }
    public function testSearchMenuByPage(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?name=bento1&page=2')->assertStatus(200)->json();

        self::assertEquals(1, count($response['data']));
        self::assertEquals(11, $response['meta']['total']);
    }
    public function testSearchMenuBySize(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?name=bento1&size=2')->assertStatus(200)->json();

        self::assertEquals(2, count($response['data']));
        self::assertEquals(11, $response['meta']['total']);
    }
    public function testSearchMenuByFeatured(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?is_featured=true')->assertStatus(200)->json();

        self::assertEquals(1, count($response['data']));
        self::assertEquals(1, $response['meta']['total']);
    }
    public function testSearchMenuByMinCalories(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?min_calories=360')->assertStatus(200)->json();

        self::assertEquals(10, count($response['data']));
        self::assertEquals(11, $response['meta']['total']);
    }
    public function testSearchMenuByMaxCalories(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?max_calories=360')->assertStatus(200)->json();

        self::assertEquals(10, count($response['data']));
        self::assertEquals(11, $response['meta']['total']);
    }
    public function testSearchMenuByMaxPrice(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?max_price=10')->assertStatus(200)->json();

        self::assertEquals(1, count($response['data']));
        self::assertEquals(1, $response['meta']['total']);
    }
    public function testSearchMenuByMinPrice(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?min_price=10')->assertStatus(200)->json();

        self::assertEquals(10, count($response['data']));
        self::assertEquals(21, $response['meta']['total']);
    }

    public function testSearchMenuByAvailability(): void
    {
        $this->seed([MenuCollectionSeeder::class]);
        // $menu = Menu::where('name', 'Sunburst Bento')->first();

        $response = $this->get('/api/menus?available=0')->assertStatus(200)->json();

        self::assertEquals(1, count($response['data']));
        self::assertEquals(1, $response['meta']['total']);
    }
}
