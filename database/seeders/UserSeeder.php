<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Budi',
            'last_name' => 'Gantenk',
            'email' => 'budi@mail.co',
            'password' => bcrypt('budigantenk1.'),
            'address' => 'Jalan Gantenk'
        ]);
        User::create([
            'first_name' => 'Budi',
            'last_name' => 'Baik',
            'email' => 'baik@mail.co',
            'password' => bcrypt('budigantenk1.'),
            'address' => 'Jalan Gantenk'
        ]);
    }
}
