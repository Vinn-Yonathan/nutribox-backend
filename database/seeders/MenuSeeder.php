<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::create([
            "name" => "Sunburst Bento",
            "description" => "Golden turmeric rice with miso-glazed salmon, roasted veggies, and microgreens — a colorful, premium NutriBox signature.",
            "image_src" => "src/assets/img/sunburst.jpg",
            "stock" => 10,
            "calories" => 350,
            "price" => 10,
            "is_featured" => true
        ]);
    }
}
