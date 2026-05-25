<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fee_items')) {
            return;
        }

        Schema::create('fee_items', function (Blueprint $table) {
            $table->id('id_fee_item');
            $table->foreignId('id_payment_type')
                ->constrained('payment_types', 'id_payment_type')
                ->onDelete('cascade');

            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_items');
    }
};
