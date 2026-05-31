<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            // bank | ewallet | qris | other
            $table->string('type', 20);

            // Display label, e.g. "BCA", "BNI", "DANA", "QRIS"
            $table->string('label', 100);

            // Generic account identifier: rekening / nomor / username / ID
            $table->string('account_number', 80)->nullable();

            // Generic account holder / owner name
            $table->string('account_name', 120)->nullable();

            // Used for QRIS image (public path: storage/...)
            $table->string('image_path')->nullable();

            // Optional helper text/instructions
            $table->string('description', 500)->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        // One-time migration from legacy payment_settings (single row) into payment_methods.
        if (Schema::hasTable('payment_settings')) {
            $legacy = DB::table('payment_settings')->orderBy('id')->first();

            if ($legacy) {
                $hasAny = DB::table('payment_methods')->count() > 0;
                $now = now();

                if (!$hasAny) {
                    $bankName = trim((string)($legacy->bank_name ?? ''));
                    $bankAcc = trim((string)($legacy->bank_account_number ?? ''));
                    $bankHolder = trim((string)($legacy->bank_account_holder ?? ''));

                    if ($bankName !== '' || $bankAcc !== '' || $bankHolder !== '') {
                        DB::table('payment_methods')->insert([
                            'type' => 'bank',
                            'label' => $bankName !== '' ? $bankName : 'Transfer Bank',
                            'account_number' => $bankAcc !== '' ? $bankAcc : null,
                            'account_name' => $bankHolder !== '' ? $bankHolder : null,
                            'image_path' => null,
                            'description' => null,
                            'is_active' => true,
                            'sort_order' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $qrisPath = trim((string)($legacy->qris_image_path ?? ''));
                    if ($qrisPath !== '') {
                        DB::table('payment_methods')->insert([
                            'type' => 'qris',
                            'label' => 'QRIS',
                            'account_number' => null,
                            'account_name' => null,
                            'image_path' => $qrisPath,
                            'description' => null,
                            'is_active' => true,
                            'sort_order' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
