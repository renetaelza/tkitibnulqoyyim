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
        Schema::create('teacher_honors', function (Blueprint $table) {
            $table->id('id_honors');
            $table->foreignId('id_teacher')->constrained('teacher_details', 'id_teacher')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->integer('attendance_count')->default(0);
            $table->integer('permission_count')->default(0);
            $table->integer('absence_count')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_honors');
    }
};
