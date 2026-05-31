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
        Schema::create('teacher_honor_allowances', function (Blueprint $table) {
            $table->id('id_honor_allowance');
            $table->foreignId('id_honors')->constrained('teacher_honors', 'id_honors')->onDelete('cascade');
            $table->string('allowance_label');
            $table->string('source_position')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_honor_allowances');
    }
};
