<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_proofs', 'payment_method_label')) {
                $table->string('payment_method_label', 120)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payment_proofs', 'payment_method_account_number')) {
                $table->string('payment_method_account_number', 80)->nullable()->after('payment_method_label');
            }
            if (!Schema::hasColumn('payment_proofs', 'payment_method_account_name')) {
                $table->string('payment_method_account_name', 120)->nullable()->after('payment_method_account_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            if (Schema::hasColumn('payment_proofs', 'payment_method_account_name')) {
                $table->dropColumn('payment_method_account_name');
            }
            if (Schema::hasColumn('payment_proofs', 'payment_method_account_number')) {
                $table->dropColumn('payment_method_account_number');
            }
            if (Schema::hasColumn('payment_proofs', 'payment_method_label')) {
                $table->dropColumn('payment_method_label');
            }
        });
    }
};
