<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Seed tanggal libur nasional Indonesia yang tanggalnya tetap setiap tahun.
     *
     * Hanya libur fixed-date yang di-seed di sini. Hari raya keagamaan dengan tanggal
     * variable (Idul Fitri, Idul Adha, Maulid, Isra Mikraj, Tahun Baru Hijriah, Imlek,
     * Nyepi, Wafat Isa, Kenaikan Isa, Waisak) HARUS diisi admin via panel berdasarkan
     * SKB 3 Menteri resmi tiap tahun, supaya tidak ada risiko tanggal salah.
     *
     * Idempotent: firstOrCreate, tidak menimpa entri yang sudah diubah admin.
     */
    public function run(): void
    {
        $years = [now()->year, now()->year + 1];

        $fixed = [
            ['month' => 1,  'day' => 1,  'name' => 'Tahun Baru Masehi'],
            ['month' => 5,  'day' => 1,  'name' => 'Hari Buruh Internasional'],
            ['month' => 6,  'day' => 1,  'name' => 'Hari Lahir Pancasila'],
            ['month' => 8,  'day' => 17, 'name' => 'Hari Kemerdekaan Republik Indonesia'],
            ['month' => 12, 'day' => 25, 'name' => 'Hari Raya Natal'],
        ];

        foreach ($years as $year) {
            foreach ($fixed as $entry) {
                $date = sprintf('%04d-%02d-%02d', $year, $entry['month'], $entry['day']);

                Holiday::query()->firstOrCreate(
                    ['date' => $date],
                    [
                        'name' => $entry['name'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
