<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('installments');
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payments');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally left blank.
        // Restoring legacy tables is not supported.
    }
};
