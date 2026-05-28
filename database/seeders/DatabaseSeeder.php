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
        // Gunakan firstOrCreate agar seeder bersifat idempotent (aman dijalankan berkali-kali di Docker)
        User::firstOrCreate(
            ['email' => 'admin@smartstockpro.id'],
            [
                'name' => 'Admin',
                'password' => bcrypt('#Admin123'), // Pastikan ada password fallback
                'role' => 'admin'
            ]
        );
    }
}
