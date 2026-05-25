<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserSeeder extends Seeder
{
    /**
     * Seed a default superadmin user (idempotent).
     */
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL', 'superadmin@gmail.com');
        $password = env('SUPERADMIN_PASSWORD', 'password');
        $name = env('SUPERADMIN_NAME', 'Super Admin');
        $phone = env('SUPERADMIN_PHONE', '+628123456789');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone_num' => $phone,
                // Must match the enum defined in the migration
                'role' => 'superadmin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );
    }
}
