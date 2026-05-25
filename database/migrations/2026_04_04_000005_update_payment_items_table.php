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
        Schema::table('payment_items', function (Blueprint $table) {
            // Add item_code column if it doesn't exist
            if (!Schema::hasColumn('payment_items', 'item_code')) {
                $table->string('item_code')->after('id_fee_item');
            }
            
            // Add description column if it doesn't exist
            if (!Schema::hasColumn('payment_items', 'description')) {
                $table->text('description')->nullable()->after('item_name');
            }
            
            // Add discount column if it doesn't exist
            if (!Schema::hasColumn('payment_items', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('unit_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            if (Schema::hasColumn('payment_items', 'item_code')) {
                $table->dropColumn('item_code');
            }
            if (Schema::hasColumn('payment_items', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('payment_items', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};
