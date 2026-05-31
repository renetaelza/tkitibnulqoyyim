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
        Schema::create('position_allowances', function (Blueprint $table) {
            $table->id('id_position_allowance');
            $table->foreignId('id_position')->constrained('positions', 'id_position')->onDelete('cascade');
            $table->foreignId('id_allowance_type')->constrained('allowance_types', 'id_allowance_type')->onDelete('cascade');
            $table->decimal('amount', 12, 2)->default(0);
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
        Schema::dropIfExists('position_allowances');
    }
};
