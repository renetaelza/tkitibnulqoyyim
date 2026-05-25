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
        Schema::create('installments', function (Blueprint $table) {
            $table->id('id_installment');
            $table->foreignId('id_payment')->constrained('payments', 'id_payment')->onDelete('cascade');
            $table->integer('installment_number');
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->decimal('installment_amount', 12, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('file_joc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
