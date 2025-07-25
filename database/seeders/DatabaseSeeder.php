<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
<<<<<<< HEAD
        // Create test user
        Users::create([
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        echo "Test user created: username=admin / password=password123\n";
=======
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
    }
}
