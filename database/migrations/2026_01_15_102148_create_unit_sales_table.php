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
        Schema::create('unit_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')    ->constrained('units')   ->cascadeOnDelete();
            $table->foreignId('buyer_id')
            ->nullable()
            ->constrained('customers')
            ->cascadeOnDelete();
                
            $table->foreignId('marketer_id')
            ->nullable()
            ->constrained('customers')
            ->nullOnDelete();

            $table->date('sale_date');
    
            $table->string('payment_method');
            $table->unsignedBigInteger('total_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_sales');
    }
};
