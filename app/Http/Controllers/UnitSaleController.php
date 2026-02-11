<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitSale;
use App\Models\Unit;
use Illuminate\Validation\Rule;
use App\Exports\UnitExport;
use Maatwebsite\Excel\Facades\Excel;

class UnitSaleController extends Controller
{
    public function store(Request $request) {

        $validated = $request->validate([
            'unit_id' => ['required', Rule::exists('units', 'id')],
            'buyer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('type', 'buyer'),
            ],
            'marketer_id' => ['nullable', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0', 'lte:total_price'],
            'contract_number' => ['required', 'string', 'unique:unit_sales,contract_number'],
        ]);
    
        $remaining = (float)$validated['total_price'] - (float)$validated['amount_paid'];
    
        // إنشاء عملية البيع في unit_sales
        $unitSale = UnitSale::create([
            'unit_id' => $validated['unit_id'],
            'buyer_id' => $validated['buyer_id'],
            'marketer_id' => $validated['marketer_id'] ?? null,
            'sale_date' => $validated['sale_date'],
            'payment_method' => $validated['payment_method'],
            'total_price' => $validated['total_price'],
            // 'amount_paid' => $validated['amount_paid'],  // مؤقت للتوافق
            'contract_number'=> $validated['contract_number'],
        ]);
    
        // تسجيل الدفعة الأولى في payments
        if ($validated['amount_paid'] > 0) {
            $unitSale->payments()->create([
                'amount_paid' => $validated['amount_paid'],
                'payment_date' => $validated['sale_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => 'دفعة أولى',
            ]);
        }
    
        // تحديث حالة الوحدة
        $unit = Unit::with('unitSale.payments')->findOrFail($validated['unit_id']);

        $totalPaid = $unitSale->payments->sum('amount_paid');

       if ($totalPaid >= $unit->price) {
    $unit->status = 'sold';
        } else {
            $unit->status = 'reserved';
        }
        $unit->save();
    
        return redirect()->route('units')->with('success', 'تم اجراء عملية بيع بنجاح');
    }
    
    public function export()
    {
        return Excel::download(new UnitExport, 'units.xlsx');
    }
}
