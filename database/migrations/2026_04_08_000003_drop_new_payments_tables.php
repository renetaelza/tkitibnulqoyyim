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
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('student_payment_installments');
        Schema::dropIfExists('student_payment_items');
        Schema::dropIfExists('student_payments');
        Schema::dropIfExists('fee_items');
        Schema::dropIfExists('payment_types');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank: payment schema is being rebuilt from scratch.
    }
};
