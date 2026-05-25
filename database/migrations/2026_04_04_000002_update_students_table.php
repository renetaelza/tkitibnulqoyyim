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
        Schema::table('students', function (Blueprint $table) {
            // Add id_registration FK if it doesn't exist
            if (!Schema::hasColumn('students', 'id_registration')) {
                $table->foreignId('id_registration')
                    ->nullable()
                    ->constrained('registrations', 'id_registration')
                    ->onDelete('cascade')
                    ->after('id_parents');
            }

            // Update status enum to support registration payment workflow
            $table->dropColumn('status');
            $table->enum('status', [
                'pending_payment',
                'aktif',
                'non-aktif',
                'lulus',
                'pindah',
                'rejected'
            ])->default('pending_payment')->after('group');

            // Add paid_late flag for audit trail if it doesn't exist
            if (!Schema::hasColumn('students', 'paid_late')) {
                $table->boolean('paid_late')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'id_registration')) {
                $table->dropForeign(['id_registration']);
                $table->dropColumn('id_registration');
            }
            
            $table->dropColumn('status');
            // Restore original status column
            $table->enum('status', ['aktif', 'non-aktif', 'lulus', 'pindah'])->default('aktif')->after('group');
            
            if (Schema::hasColumn('students', 'paid_late')) {
                $table->dropColumn('paid_late');
            }
        });
    }
};
