<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Menu::create([
                "name" => "Bento" . $i,
                "description" => "",
                "image_src" => "src/assets/img/bento{$i}.jpg",
                "stock" => 10 + $i,
                "calories" => 350 + $i,
                "price" => 10 + $i,
                "is_featured" => false
            ]);
        }

        Menu::create([
            "name" => "Bento",
            "description" => "",
            "image_src" => "src/assets/img/bento.jpg",
            "stock" => 0,
            "calories" => 350,
            "price" => 10,
            "is_featured" => true
        ]);
    }
}
