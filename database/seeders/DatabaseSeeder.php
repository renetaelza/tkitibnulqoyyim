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
        // Required data only. For dummy data run:
        // php artisan db:seed --class=Database\\Seeders\\DummyTestingSeeder
        $this->call(CoreRequiredSeeder::class);
    }
}
