<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('unit_sale_id')->constrained('unit_sales')->onDelete('cascade');

            $table->decimal('amount_paid', 15, 2); 
            $table->date('payment_date');        
            $table->string('payment_method');
            $table->string('reference_number'); 
            $table->text('notes')->nullable();     // ملاحظات
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
