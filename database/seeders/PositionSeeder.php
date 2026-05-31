<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Seed posisi/jabatan yang dipakai untuk perhitungan tunjangan honor.
     * Idempotent: firstOrCreate — tidak menimpa data yang sudah diubah admin.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Kepala Sekolah',   'description' => 'Penanggung jawab utama operasional sekolah.'],
            ['name' => 'Bendahara',        'description' => 'Mengelola dana sekolah dan pembayaran honor.'],
            ['name' => 'Wali Kelas A',     'description' => 'Bertanggung jawab atas pembinaan Kelas A.'],
            ['name' => 'Wali Kelas B',     'description' => 'Bertanggung jawab atas pembinaan Kelas B.'],
            ['name' => 'Wali Kelas C',     'description' => 'Bertanggung jawab atas pembinaan Kelas C.'],
            ['name' => 'Guru Pendamping',  'description' => 'Mendampingi proses belajar mengajar di kelas.'],
        ];

        foreach ($positions as $entry) {
            Position::query()->firstOrCreate(
                ['name' => $entry['name']],
                ['description' => $entry['description'], 'is_active' => true]
            );
        }
    }
}
