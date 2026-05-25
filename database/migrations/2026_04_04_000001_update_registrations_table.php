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
        Schema::table('registrations', function (Blueprint $table) {
            // Drop the old status enum and create new one with additional values
            $table->dropColumn('status');
            $table->enum('status', [
                'pending',
                'approved_awaiting_payment',
                'pending_due',
                'active',
                'rejected'
            ])->default('pending')->after('group');

            // Add payment deadline tracking
            $table->date('payment_deadline')->nullable()->after('status');
            $table->date('grace_period_until')->nullable()->after('payment_deadline');

            // Add late payment tracking
            $table->boolean('paid_late')->default(false)->after('grace_period_until');
            
            // Add rejection reason
            $table->string('reject_reason')->nullable()->after('paid_late');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_deadline', 'grace_period_until', 'paid_late', 'reject_reason']);
            // Restore original status column
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('group');
        });
    }
};
