<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'type' => 'bank_transfer',
                'label' => 'Transfer BCA',
                'account_number' => '1234567890',
                'account_name' => 'TK Ibnul Qoyyim',
                'description' => 'Transfer ke rekening BCA atas nama TK Ibnul Qoyyim.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type' => 'bank_transfer',
                'label' => 'Transfer BNI',
                'account_number' => '0987654321',
                'account_name' => 'TK Ibnul Qoyyim',
                'description' => 'Transfer ke rekening BNI atas nama TK Ibnul Qoyyim.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type' => 'e_wallet',
                'label' => 'DANA',
                'account_number' => '+628112345678',
                'account_name' => 'TK Ibnul Qoyyim',
                'description' => 'Transfer melalui e-wallet DANA.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'type' => 'qris',
                'label' => 'QRIS',
                'account_number' => null,
                'account_name' => 'TK Ibnul Qoyyim',
                'description' => 'Scan QRIS untuk pembayaran instan semua e-wallet & m-banking.',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['label' => $method['label']],
                [
                    'type' => $method['type'],
                    'account_number' => $method['account_number'],
                    'account_name' => $method['account_name'],
                    'description' => $method['description'],
                    'is_active' => $method['is_active'],
                    'sort_order' => $method['sort_order'],
                    'image_path' => null,
                ]
            );
        }
    }
}
