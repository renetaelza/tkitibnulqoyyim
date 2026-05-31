<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use Illuminate\Database\Seeder;

class AllowanceTypeSeeder extends Seeder
{
    /**
     * Seed jenis tunjangan dasar. Nominal per posisi diisi admin via
     * position_allowances (lewat halaman Tunjangan Posisi).
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Tunjangan Jabatan',   'description' => 'Tunjangan tetap berdasarkan posisi/jabatan yang dipegang guru.'],
            ['name' => 'Tunjangan Kehadiran', 'description' => 'Tunjangan tambahan berdasarkan persentase kehadiran bulanan.'],
            ['name' => 'Tunjangan Transport', 'description' => 'Tunjangan biaya transportasi harian.'],
        ];

        foreach ($types as $entry) {
            AllowanceType::query()->firstOrCreate(
                ['name' => $entry['name']],
                [
                    'description' => $entry['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
