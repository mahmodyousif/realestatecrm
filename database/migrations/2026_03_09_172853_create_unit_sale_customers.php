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
        Schema::create('unit_sale_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_sale_id')->constrained('unit_sales')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->decimal('share_percentage', 15, 2);
            $table->unsignedBigInteger('share_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_sale_customers');
    }
};
