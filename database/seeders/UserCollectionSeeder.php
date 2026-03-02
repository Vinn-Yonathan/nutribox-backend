<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'first_name' => "User {$i}",
                'last_name' => "Last {$i}",
                'email' => "{$i}@mail.com",
                'password' => bcrypt('secret1.'),
            ]);
        }
    }
}
