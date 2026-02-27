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
        Schema::table('unit_sales', function (Blueprint $table) {
            $table->decimal('discount', 8, 2)->default(0)->after('unit_price');
            $table->decimal('total_price', 8, 2)->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unitSale', function (Blueprint $table) {
            //
        });
    }
};
