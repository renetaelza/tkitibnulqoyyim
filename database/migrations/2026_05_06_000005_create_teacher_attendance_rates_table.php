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
        Schema::create('teacher_attendance_rates', function (Blueprint $table) {
            $table->id('id_teacher_attendance_rate');
            $table->foreignId('id_teacher')->constrained('teacher_details', 'id_teacher')->onDelete('cascade');
            $table->decimal('amount_per_attendance', 12, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance_rates');
    }
};
