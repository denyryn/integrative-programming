<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->createMany([
            [
                "name" => "Haafiz",
                "email" => "kaasib@gmail.com",
                "password" => bcrypt('$2y$10$aComplexStringOfAtleaeeeoYTF1Nrkf8VohijM26vuoPJxTwbSK')
            ],
            [
                "name" => "Ali",
                "email" => "abc@email.com",
                "password" => bcrypt('$2y$10$aComplexStringOfAtleaeeeoYTF1Nrkf8VohijM26vuoPJxTwbSK')
            ]
        ]);
    }
}
