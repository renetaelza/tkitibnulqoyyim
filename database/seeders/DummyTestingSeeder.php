<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyTestingSeeder extends Seeder
{
    /**
     * Seed dummy/testing data (safe to re-run).
     */
    public function run(): void
    {
        // Always ensure core required data exists first
        $this->call(CoreRequiredSeeder::class);

        $this->call([
            DemoUserSeeder::class,
            ParentGuardianSeeder::class,
            SchoolClassSeeder::class,
            StudentSeeder::class,
            TeacherDetailSeeder::class,
            FacilitySeeder::class,
            RegistrationSeeder::class,
        ]);
    }
}
