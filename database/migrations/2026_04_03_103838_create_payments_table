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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->foreignId('id_student')->constrained('students', 'id_student')->onDelete('cascade');
            $table->string('invoice_name')->nullable();
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('total_bill', 12, 2);
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->string('file_joc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
