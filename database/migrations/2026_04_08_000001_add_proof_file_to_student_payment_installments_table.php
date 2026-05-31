<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_payment_installments')) {
            return;
        }

        Schema::table('student_payment_installments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_payment_installments', 'payment_method')) {
                $table->enum('payment_method', ['transfer_bank', 'e_wallet', 'cash', 'qris'])->nullable()->after('installment_amount');
            }
            if (!Schema::hasColumn('student_payment_installments', 'proof_file')) {
                $table->string('proof_file')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_payment_installments')) {
            return;
        }

        Schema::table('student_payment_installments', function (Blueprint $table) {
            if (Schema::hasColumn('student_payment_installments', 'proof_file')) {
                $table->dropColumn('proof_file');
            }
            if (Schema::hasColumn('student_payment_installments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
