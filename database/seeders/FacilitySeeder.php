<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Ruang Kelas',
                'description' => 'Ruang belajar utama untuk kegiatan KB/TK.',
                'quantity' => 3,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'name' => 'Perosotan',
                'description' => 'Fasilitas bermain outdoor.',
                'quantity' => 1,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'name' => 'Ayunan',
                'description' => 'Fasilitas bermain outdoor.',
                'quantity' => 2,
                'condition' => 'Baik',
                'is_active' => true,
            ],
            [
                'name' => 'Alat Tulis',
                'description' => 'Stok alat tulis kelas (pensil, krayon, dll).',
                'quantity' => 50,
                'condition' => 'Baik',
                'is_active' => true,
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::query()->updateOrCreate(
                ['name' => $facility['name']],
                [
                    'description' => $facility['description'],
                    'quantity' => $facility['quantity'],
                    'condition' => $facility['condition'],
                    'image_path' => null,
                    'is_active' => $facility['is_active'],
                ]
            );
        }
    }
}
