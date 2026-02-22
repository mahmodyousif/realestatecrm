<?php

namespace App\Http\Controllers;

use App\Exports\UnitSalesExport;
use App\Models\Unit;
use App\Models\UnitSale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UnitSaleController extends Controller
{
    public function store(Request $request) {

        $validated = $request->validate([
            'unit_id' => ['required', Rule::exists('units', 'id')],
            'buyer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('type', 'buyer'),
            ],
            'marketer_id' => ['nullable', 'exists:customers,id'],
            'investor_id' => ['nullable',Rule::exists('customers', 'id')->where('type', 'investor'),
],
            'sale_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0', 'lte:total_price'],
            'contract_number' => ['required', 'string', 'unique:unit_sales,contract_number'],
            'commission' => ['nullable', 'numeric', 'min:0'],        ],
        [
            'contract_number.unique' => 'رقم العقد مستخدم مسبقًا، يرجى إدخال رقم عقد آخر',
            'contract_number.required' => 'رقم العقد مطلوب',
        
        ]
    
    );
    

        // إنشاء عملية البيع في unit_sales
        $unitSale = UnitSale::create([
            'unit_id' => $validated['unit_id'],
            'buyer_id' => $validated['buyer_id'],
            'marketer_id' => $validated['marketer_id'] ?? null,
            'investor_id' => $validated['investor_id'] ?? null,
            'sale_date' => $validated['sale_date'],
            'payment_method' => $validated['payment_method'],
            'total_price' => $validated['total_price'],
            'contract_number'=> $validated['contract_number'],
            'commission' => $validated['commission'] ,
            
        ]);
    
        // تسجيل الدفعة الأولى في payments
        if ($validated['amount_paid'] > 0) {
            $unitSale->payments()->create([
                'amount_paid' => $validated['amount_paid'],
                'payment_date' => $validated['sale_date'],
                'payment_method' => $validated['payment_method'],
                 'reference_number' => 0 ,
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
        return Excel::download(new UnitSalesExport, 'units.xlsx');
    }
}