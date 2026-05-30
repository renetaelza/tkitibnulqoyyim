<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_payments')) {
            Schema::table('student_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('student_payments', 'installment_requested')) {
                    $table->boolean('installment_requested')->default(false)->after('paid_at');
                }
                if (!Schema::hasColumn('student_payments', 'installment_count')) {
                    $table->unsignedTinyInteger('installment_count')->nullable()->after('installment_requested');
                }
            });
        }

        if (Schema::hasTable('student_payment_installments')) {
            return;
        }

        Schema::create('student_payment_installments', function (Blueprint $table) {
            $table->id('id_student_payment_installment');

            $table->foreignId('id_student_payment')
                ->constrained('student_payments', 'id_student_payment')
                ->onDelete('cascade');

            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('installment_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();

            $table->unique(['id_student_payment', 'installment_number'], 'spi_payment_installment_unique');
            $table->index(['id_student_payment', 'status']);
            $table->index(['due_date']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('student_payment_installments')) {
            Schema::dropIfExists('student_payment_installments');
        }

        if (Schema::hasTable('student_payments')) {
            Schema::table('student_payments', function (Blueprint $table) {
                if (Schema::hasColumn('student_payments', 'installment_count')) {
                    $table->dropColumn('installment_count');
                }
                if (Schema::hasColumn('student_payments', 'installment_requested')) {
                    $table->dropColumn('installment_requested');
                }
            });
        }
    }
};
