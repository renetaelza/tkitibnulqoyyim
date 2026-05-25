<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Seed deterministic demo users for local/testing.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Staff',
                'email' => 'admin@example.com',
                'phone_num' => '+62812345679',
                'role' => 'administration',
            ],
            [
                'name' => 'Teacher Name',
                'email' => 'teacher@example.com',
                'phone_num' => '+62812345680',
                'role' => 'teacher',
            ],
            [
                'name' => 'Kepala Sekolah',
                'email' => 'headmaster@example.com',
                'phone_num' => '+62812345681',
                'role' => 'headmaster',
            ],
            [
                'name' => 'Guest User',
                'email' => 'guest@example.com',
                'phone_num' => '+62812345682',
                'role' => 'guest',
            ],
        ];

        foreach ($users as $userData) {
            User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'phone_num' => $userData['phone_num'],
                    'role' => $userData['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
