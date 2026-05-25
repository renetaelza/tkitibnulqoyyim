<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fee_items', function (Blueprint $table) {
            // Add code column if it doesn't exist
            if (!Schema::hasColumn('fee_items', 'code')) {
                $table->string('code')->unique()->after('id_payment_type');
            }
            
            // Add is_active column if it doesn't exist
            if (!Schema::hasColumn('fee_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('default_amount');
            }
            
            // Add start_date column if it doesn't exist
            if (!Schema::hasColumn('fee_items', 'start_date')) {
                $table->date('start_date')->nullable()->after('is_active');
            }
            
            // Add end_date column if it doesn't exist
            if (!Schema::hasColumn('fee_items', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_items', function (Blueprint $table) {
            if (Schema::hasColumn('fee_items', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('fee_items', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('fee_items', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('fee_items', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
};
