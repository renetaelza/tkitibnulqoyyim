<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentMasterSeeder extends Seeder
{
    public function run(): void
    {
        $amount = (float) env('UANG_PENDAFTARAN_AMOUNT', 0);
        $active = (bool) env('UANG_PENDAFTARAN_ACTIVE', false);

        $template = null;
        if ($amount > 0) {
            $template = [
                [
                    'label' => 'Uang Pendaftaran',
                    'amount' => $amount,
                    'qty' => 1,
                ],
            ];
        }

        Payment::query()->updateOrCreate(
            ['jenis_payment' => 'uang_pendaftaran'],
            [
                'name' => 'Uang Pendaftaran',
                'period_mode' => 'one_time',
                'detail_fee_template' => $template,
                'default_amount' => $amount,
                'is_active' => $active,
                'start_date' => null,
                'end_date' => null,
            ]
        );
    }
}
