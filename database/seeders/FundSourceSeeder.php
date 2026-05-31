<?php

namespace Database\Seeders;

use App\Models\FundSource;
use Illuminate\Database\Seeder;

class FundSourceSeeder extends Seeder
{
    /**
     * Seed 9 sumber dana untuk dashboard Bendahara.
     * - 5 auto-credit: SPP (iuran_bulanan), Gedung/Seragam/ATK/Sarana (komponen iuran_masuk), Lain-lain (komponen iuran_masuk tanpa mapping).
     * - 3 manual input: BOP, BOSDA, HONDA.
     * Idempotent: firstOrCreate.
     */
    public function run(): void
    {
        $sources = [
            ['code' => 'spp',       'name' => 'SPP',       'description' => 'Iuran bulanan siswa (auto dari pembayaran iuran_bulanan).',        'is_auto_credit' => true,  'display_order' => 10],
            ['code' => 'bop',       'name' => 'BOP',       'description' => 'Bantuan Operasional Pendidikan (input manual oleh bendahara).',    'is_auto_credit' => false, 'display_order' => 20],
            ['code' => 'bosda',     'name' => 'BOSDA',     'description' => 'Biaya Operasional Sekolah Daerah (input manual oleh bendahara).',  'is_auto_credit' => false, 'display_order' => 30],
            ['code' => 'honda',     'name' => 'HONDA',     'description' => 'Honor Daerah (input manual oleh bendahara).',                      'is_auto_credit' => false, 'display_order' => 40],
            ['code' => 'gedung',    'name' => 'Gedung',    'description' => 'Auto dari komponen "Uang Pembangunan" pada iuran masuk.',          'is_auto_credit' => true,  'display_order' => 50],
            ['code' => 'atk',       'name' => 'ATK',       'description' => 'Auto dari komponen "ATK" pada iuran masuk.',                       'is_auto_credit' => true,  'display_order' => 60],
            ['code' => 'seragam',   'name' => 'Seragam',   'description' => 'Auto dari komponen "Seragam" pada iuran masuk.',                   'is_auto_credit' => true,  'display_order' => 70],
            ['code' => 'sarana',    'name' => 'Sarana',    'description' => 'Auto dari komponen "Sarana" pada iuran masuk.',                    'is_auto_credit' => true,  'display_order' => 80],
            ['code' => 'lain_lain', 'name' => 'Lain-lain', 'description' => 'Komponen iuran_masuk tanpa mapping eksplisit + deposit manual.',   'is_auto_credit' => true,  'display_order' => 90],
        ];

        foreach ($sources as $entry) {
            FundSource::query()->firstOrCreate(
                ['code' => $entry['code']],
                [
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'is_active' => true,
                    'is_auto_credit' => $entry['is_auto_credit'],
                    'display_order' => $entry['display_order'],
                ]
            );
        }
    }
}
