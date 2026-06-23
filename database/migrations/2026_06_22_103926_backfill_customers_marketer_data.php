<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {

            $sales = DB::table('unit_sales')->get();

            foreach ($sales as $sale) {

                // جلب المشترين المرتبطين بعملية البيع
                $customers = DB::table('unit_sale_customers')
                    ->where('unit_sale_id', $sale->id)
                    ->get();

                foreach ($customers as $customer) {

                    // تحديث فقط إذا البيانات فاضية (مهم جدًا)
                    DB::table('unit_sale_customers')
                        ->where('id', $customer->id)
                        ->update([
                            'marketer_id' => $sale->marketer_id,
                            'commission_amount'  => $sale->commission,
                            'sale_date'                 => $sale->sale_date,
                            'updated_at'  => now(),
                        ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
