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
    Schema::table('unit_sale_customers', function (Blueprint $table) {

        $table->foreignId('marketer_id')
            ->nullable()
            ->constrained('customers')
            ->cascadeOnDelete();

        $table->decimal('commission_amount', 15, 2)
            ->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_sale_customers', function (Blueprint $table) {
            //
        });
    }
};
