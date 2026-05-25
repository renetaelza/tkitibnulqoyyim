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
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_class')->constrained('classes', 'id_class')->onDelete('cascade');
            $table->foreignId('id_student')->constrained('students', 'id_student')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['id_class', 'id_student']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};
