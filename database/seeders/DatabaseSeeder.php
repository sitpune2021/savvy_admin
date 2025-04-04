<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\DriverSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',  // Name of the user
            'email' => 'admin@gmail.com',  // Email of the user
            'role' => 'admin',  // Email of the user
            'password' => Hash::make('Test@123'),  // Password, hashed
        ]);
        
        $this->call(CustomerSeeder::class);
        $this->call(DriverSeeder::class);
    }
}
