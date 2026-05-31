<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use App\Models\Position;
use App\Models\PositionAllowance;
use Illuminate\Database\Seeder;

class PositionAllowanceSeeder extends Seeder
{
    /**
     * Seed tunjangan per posisi. Nominal bersifat contoh dan bisa diubah admin via UI.
     * Menggunakan firstOrCreate agar tidak menimpa nilai yang sudah diedit admin.
     */
    public function run(): void
    {
        // [position_name => [allowance_type_name => amount]]
        $matrix = [
            'Kepala Sekolah' => [
                'Tunjangan Jabatan'   => 1_500_000,
                'Tunjangan Transport' => 300_000,
            ],
            'Bendahara' => [
                'Tunjangan Jabatan'   => 750_000,
                'Tunjangan Transport' => 200_000,
            ],
            'Wali Kelas A' => [
                'Tunjangan Jabatan'   => 500_000,
                'Tunjangan Transport' => 150_000,
            ],
            'Wali Kelas B' => [
                'Tunjangan Jabatan'   => 500_000,
                'Tunjangan Transport' => 150_000,
            ],
            'Wali Kelas C' => [
                'Tunjangan Jabatan'   => 500_000,
                'Tunjangan Transport' => 150_000,
            ],
            'Guru Pendamping' => [
                'Tunjangan Jabatan'   => 300_000,
                'Tunjangan Transport' => 100_000,
            ],
        ];

        $effectiveFrom = '2024-07-01';

        foreach ($matrix as $positionName => $allowances) {
            $position = Position::query()->where('name', $positionName)->first();
            if (! $position) {
                continue;
            }

            foreach ($allowances as $typeName => $amount) {
                $type = AllowanceType::query()->where('name', $typeName)->first();
                if (! $type) {
                    continue;
                }

                PositionAllowance::query()->firstOrCreate(
                    [
                        'id_position'      => $position->id_position,
                        'id_allowance_type' => $type->id_allowance_type,
                        'effective_to'     => null,
                    ],
                    [
                        'amount'         => $amount,
                        'effective_from' => $effectiveFrom,
                    ]
                );
            }
        }
    }
}
