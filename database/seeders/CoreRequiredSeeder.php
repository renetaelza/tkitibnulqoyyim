<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CoreRequiredSeeder extends Seeder
{
    /**
     * Seed data that must always exist (idempotent).
     */
    public function run(): void
    {
        $this->call([
            SuperAdminUserSeeder::class,
            PaymentMasterSeeder::class,
        ]);
    }
}
