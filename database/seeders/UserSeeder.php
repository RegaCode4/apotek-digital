<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@apotek.com'],
            [
                'name' => 'Admin Apotek',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'apoteker@apotek.com'],
            [
                'name' => 'Apoteker Apotek',
                'password' => Hash::make('password'),
                'role' => 'pharmacist',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@apotek.com'],
            [
                'name' => 'Kasir Apotek',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'is_active' => true,
            ]
        );
    }
}
