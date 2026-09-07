<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email_hash' => hash('sha256', strtolower('torerusinee@gmail.com'))],
            [
                'name' => 'Head Admin',
                'email' => 'torerusinee@gmail.com',
                'password' => Hash::make('$Myluis99'),
                'is_admin' => true,
                'is_super_admin' => true,
                'two_fa_enabled' => true,
            ]
        );

        User::updateOrCreate(
            ['email_hash' => hash('sha256', strtolower('test@example.com'))],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('Test123!'),
                'two_fa_enabled' => true,
            ]
        );

        User::updateOrCreate(
            ['email_hash' => hash('sha256', strtolower('admin@example.com'))],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('Admin123!'),
            ]
        );
    }
}


