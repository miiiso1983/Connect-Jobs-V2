<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'mustafa@teamiapps.com'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('Kent@1983'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}

