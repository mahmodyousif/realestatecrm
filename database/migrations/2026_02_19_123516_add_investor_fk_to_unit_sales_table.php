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
            $table->foreignId('investor_id')
            ->nullable()
            ->constrained('customers')
            ->nullOnDelete()
            ->after('marketer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_sale', function (Blueprint $table) {
            //
        });
    }
};
