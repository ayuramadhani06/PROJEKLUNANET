<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Lunanet',
            'email' => 'admin@lunanet.id', // Ini buat login
            'password' => Hash::make('admin123'), // Ini passwordnya
        ]);
    }
}
