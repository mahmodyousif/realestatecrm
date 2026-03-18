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

         $table->dropForeign(['buyer_id']); 
         $table->dropForeign(['investor_id']);
            $table->dropColumn('buyer_id');
            $table->dropColumn('investor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_sales', function (Blueprint $table) {
            //
        });
    }
};
