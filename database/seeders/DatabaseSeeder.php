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
        User::create(['name' => 'Admin User', 'email' => 'admin@damstudio.com', 'password' => bcrypt('password')]);
        // $this->call(AssetSeeder::class);
    }
}
