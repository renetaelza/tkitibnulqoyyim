<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_payments')) {
            return;
        }

        Schema::create('student_payments', function (Blueprint $table) {
            $table->id('id_student_payment');

            $table->foreignId('id_student')
                ->constrained('students', 'id_student')
                ->onDelete('cascade');

            $table->foreignId('id_payment_type')
                ->constrained('payment_types', 'id_payment_type')
                ->onDelete('cascade');

            $table->string('payment_period')->nullable();

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);

            $table->enum('payment_method', ['transfer_bank', 'e_wallet', 'cash', 'qris'])->nullable();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');

            $table->boolean('is_late')->default(false);
            $table->string('unique_code')->nullable();
            $table->dateTime('unique_code_valid_until')->nullable();
            $table->string('proof_file')->nullable();
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();

            $table->index(['id_student', 'id_payment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payments');
    }
};
