<?php

namespace App\Http\Controllers;

use PDO;
use App\Models\Unit;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\UnitSale;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentCustomerExport;

class PaymentsController extends Controller
{
    // صفحة عرض الدفعات والتقارير المالية
    public function index() {
        $unitSales = UnitSale::with(['buyer', 'unit.project', 'payments'])
                             ->latest()
                             ->get();
        $remainingUnits = Unit::with(['unitSale.buyer'])->where('status','reserved')->has('unitSale')->get() ; 

        // إجمالي المبيعات
        $totalPrice = UnitSale::sum('total_price');

        // إجمالي المدفوعات من جدول payments
        $totalPaid = Payment::sum('amount_paid');

        // المتبقي
        $remaining = $totalPrice - $totalPaid;

        return view('payments.index', compact('unitSales' , 'remainingUnits', 'totalPrice', 'totalPaid', 'remaining'));
    }

    // تسجيل دفعة جديدة
    public function store(Request $request) {
        $validated = $request->validate([
            'unit_sale_id'    => 'required|exists:unit_sales,id',
            'amount_paid'     => 'required|numeric|min:1',
            'payment_date'    => 'required|date',
            'payment_method'  => 'required|string',
            'reference_number' => 'required|numeric|min:1' ,
            'notes'           => 'nullable|string',
        ]);

        // إنشاء الدفعة في جدول payments
        $payment = Payment::create([
            'unit_sale_id'    => $validated['unit_sale_id'],
            'amount_paid'     => $validated['amount_paid'],
            'payment_date'    => $validated['payment_date'],
            'payment_method'  => $validated['payment_method'],
            'reference_number'=> $validated['reference_number'] ,
            'notes'           => $validated['notes'] ?? null,
        ]);

        // تحديث حالة الوحدة بعد الدفعة
        $unitSale = UnitSale::find($validated['unit_sale_id']);
        $totalPaid = $unitSale->payments()->sum('amount_paid');

        $unit = $unitSale->unit;
        if ($totalPaid >= $unitSale->total_price) {
            $unit->status = 'sold';
        } else {
            $unit->status = 'reserved';
        }
        $unit->save();

        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    public function show($id) {
        $payments = Payment::where('unit_sale_id', $id)
        ->with(['unitSale.buyer'])
        ->get();

    $unitSale = $payments->first()?->unitSale;
        return view('payments.show' ,compact('payments', 'unitSale')) ;
    }

    public function exportCustomerPayments($id)
{

    $unitSale = UnitSale::with(['unit','buyer'])->findOrFail($id);

    $customerName = $unitSale->buyer->name;
    $unitName = $unitSale->unit->unit_number . " " . $unitSale->unit->type ;
    return Excel::download(
        new PaymentCustomerExport($id , $customerName , $unitName) ,
        'customer_payments.xlsx'
    );
}
}
