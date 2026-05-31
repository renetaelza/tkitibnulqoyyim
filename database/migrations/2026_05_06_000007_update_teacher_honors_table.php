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
        Schema::table('teacher_honors', function (Blueprint $table) {
            $table->date('period_start')->nullable()->after('year');
            $table->date('period_end')->nullable()->after('period_start');
            $table->decimal('rate_snapshot', 12, 2)->default(0)->after('absence_count');
            $table->decimal('allowance_total', 12, 2)->default(0)->after('rate_snapshot');
            $table->decimal('manual_adjustment', 12, 2)->default(0)->after('allowance_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_honors', function (Blueprint $table) {
            $table->dropColumn('period_start');
            $table->dropColumn('period_end');
            $table->dropColumn('rate_snapshot');
            $table->dropColumn('allowance_total');
            $table->dropColumn('manual_adjustment');
        });
    }
};
