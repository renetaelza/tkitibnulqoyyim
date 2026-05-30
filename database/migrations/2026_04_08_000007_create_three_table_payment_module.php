<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Drop any legacy/new payment module tables so the final schema uses 3 tables only.
        Schema::dropIfExists('student_payment_installments');
        Schema::dropIfExists('student_payment_items');
        Schema::dropIfExists('student_payments');
        Schema::dropIfExists('fee_items');
        Schema::dropIfExists('payment_types');

        Schema::dropIfExists('installments');
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->string('name');
            $table->string('jenis_payment');
            $table->string('period_mode')->default('one_time'); // one_time | monthly | school_year
            $table->json('detail_fee_template')->nullable();
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['jenis_payment', 'period_mode']);
            $table->index(['is_active']);
        });

        Schema::create('student_payments', function (Blueprint $table) {
            $table->id('id_student_payment');

            $table->foreignId('id_student')
                ->constrained('students', 'id_student')
                ->cascadeOnDelete();

            $table->foreignId('id_payment')
                ->constrained('payments', 'id_payment')
                ->cascadeOnDelete();

            // Unique per (student + payment + period). For one-time payments we store period = 'ONCE'.
            $table->string('payment_period')->default('ONCE');

            // Snapshot of payment components at the time of billing (JSON array)
            $table->json('detail_fee_snapshot')->nullable();

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);

            $table->enum('payment_method', ['transfer_bank', 'e_wallet', 'cash', 'qris'])->nullable();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');

            $table->string('proof_file')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Deferred installment mechanism (kept for future)
            $table->boolean('installment_requested')->default(false);
            $table->unsignedTinyInteger('installment_count')->nullable();

            $table->timestamps();

            $table->unique(['id_student', 'id_payment', 'payment_period'], 'uq_student_payment_period');
            $table->index(['status']);
        });

        Schema::create('student_payment_installments', function (Blueprint $table) {
            $table->id('id_student_payment_installment');

            $table->foreignId('id_student_payment')
                ->constrained('student_payments', 'id_student_payment')
                ->cascadeOnDelete();

            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('installment_amount', 12, 2);

            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->enum('payment_method', ['transfer_bank', 'e_wallet', 'cash', 'qris'])->nullable();
            $table->string('proof_file')->nullable();

            $table->timestamps();

            $table->unique(['id_student_payment', 'installment_number'], 'uq_student_payment_installment_no');
            $table->index(['id_student_payment', 'status']);
            $table->index(['due_date']);
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('student_payment_installments');
        Schema::dropIfExists('student_payments');
        Schema::dropIfExists('payments');

        Schema::enableForeignKeyConstraints();
    }
};
