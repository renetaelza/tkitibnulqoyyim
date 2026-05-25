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
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_class')->constrained('classes', 'id_class')->onDelete('cascade');
            $table->foreignId('id_teacher')->constrained('teacher_details', 'id_teacher')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['id_class', 'id_teacher']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
    }
};
