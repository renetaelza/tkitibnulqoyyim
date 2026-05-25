<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_payment_items')) {
            return;
        }

        Schema::create('student_payment_items', function (Blueprint $table) {
            $table->id('id_student_payment_item');

            $table->foreignId('id_student_payment')
                ->constrained('student_payments', 'id_student_payment')
                ->onDelete('cascade');

            $table->foreignId('id_fee_item')
                ->nullable()
                ->constrained('fee_items', 'id_fee_item')
                ->nullOnDelete();

            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['id_student_payment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payment_items');
    }
};
