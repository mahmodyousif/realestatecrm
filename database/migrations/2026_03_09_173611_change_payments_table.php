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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('unit_sale_customer_id')->nullable()->constrained('unit_sale_customers')->onDelete('cascade');
            $table->dropForeign(['unit_sale_id']);
            $table->dropColumn('unit_sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->foreignId('unit_sale_id')
                  ->constrained('unit_sales')
                  ->cascadeOnDelete();

            $table->dropForeign(['unit_sale_customer_id']);
            $table->dropColumn('unit_sale_customer_id');
        });
    }
};
