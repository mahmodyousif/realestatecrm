<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\UnitSale;
use App\Models\UnitSaleCustomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentCustomerExport;

class PaymentsController extends Controller
{
    // ───────────────────────────────────────────────
    // صفحة عرض الدفعات والتقارير المالية
    // ───────────────────────────────────────────────
    public function index(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : null;

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : null;

        // ── جلب عمليات البيع مع البيانات المرتبطة ──
        $unitSales = UnitSale::with([
            'saleCustomers.customer',
            'saleCustomers.payments',
            'marketer',
            'unit.project',
        ])
        // ✅ إصلاح: استخدام sale_date بدلاً من created_at ليتطابق مع فلتر الإجماليات
        ->when($from, fn($q) => $q->where('sale_date', '>=', $from))
        ->when($to,   fn($q) => $q->where('sale_date', '<=', $to))
        ->latest('sale_date')
        ->get();

        // ✅ إصلاح: استبدال foreach اليدوي بـ flatMap — أبسط وبدون queries إضافية
        $saleCustomers = $unitSales->flatMap->saleCustomers;

        // ── الوحدات المحجوزة التي لها بيع (للمتابعة) ──
        $remainingUnits = Unit::with([
            'unitSale' => fn($q) => $q->with(['saleCustomers.customer', 'saleCustomers.payments']),
        ])
        ->where('status', 'reserved')
        ->orWhere('status', 'partially_paid')   // ✅ إضافة: تشمل partially_paid أيضاً
        ->has('unitSale')
        ->get();

        // ── الإجماليات (نفس فلتر sale_date) ──
        $totalPrice = UnitSale::when($from, fn($q) => $q->where('sale_date', '>=', $from))
            ->when($to,   fn($q) => $q->where('sale_date', '<=', $to))
            ->sum('total_price');

        $totalPaid = Payment::when($from, fn($q) => $q->where('payment_date', '>=', $from))
            ->when($to,   fn($q) => $q->where('payment_date', '<=', $to))
            ->sum('amount_paid');

        $remaining = $totalPrice - $totalPaid;

        return view('payments.index', compact(
            'unitSales',
            'saleCustomers',
            'remainingUnits',
            'totalPrice',
            'totalPaid',
            'remaining'
        ));
    }

    // ───────────────────────────────────────────────
    // تسجيل دفعة جديدة
    // ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sale_customer_id' => 'required|exists:unit_sale_customers,id',
            'amount_paid'           => 'required|numeric|min:1',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|string',
            'reference_number'      => 'nullable|string',
            'notes'                 => 'nullable|string',
        ]);

        $saleCustomer = UnitSaleCustomer::findOrFail($validated['unit_sale_customer_id']);

        // ✅ إصلاح: التحقق من أن الدفعة لا تتجاوز حصة الشريك
        $alreadyPaid = $saleCustomer->payments()->sum('amount_paid');
        $remaining   = $saleCustomer->share_amount - $alreadyPaid;

        if ($validated['amount_paid'] > $remaining) {
            return back()->with(
                'error',
                'المبلغ المدفوع (' . number_format($validated['amount_paid']) . ') يتجاوز المتبقي على هذا الشريك (' . number_format($remaining) . ')'
            );
        }

        // ── إنشاء الدفعة ──
        Payment::create([
            'unit_sale_customer_id' => $validated['unit_sale_customer_id'],
            'amount_paid'           => $validated['amount_paid'],
            'payment_date'          => $validated['payment_date'],
            'payment_method'        => $validated['payment_method'],
            'reference_number'      => $validated['reference_number'] ?? null,
            'notes'                 => $validated['notes'] ?? null,
        ]);

        // ── تحديث حالة الوحدة بناءً على جميع الشركاء ──
        $unitSale        = $saleCustomer->unitSale;
        $unit            = $unitSale->unit;
        $allSaleCustomers = $unitSale->saleCustomers;

        $totalPaidAll  = 0;
        $totalShareAll = 0;
        $allFullyPaid  = true;

        foreach ($allSaleCustomers as $sc) {
            $paid           = $sc->payments()->sum('amount_paid');
            $totalPaidAll  += $paid;
            $totalShareAll += $sc->share_amount;

            if ($paid < $sc->share_amount) {
                $allFullyPaid = false;
            }
        }

        // ✅ إصلاح: ثلاث حالات بدلاً من اثنتين
        if ($allFullyPaid) {
            $unit->status = 'sold';
        } elseif ($totalPaidAll > 0) {
            $unit->status = 'partially_paid';
        } else {
            $unit->status = 'reserved';
        }

        $unit->save();

        return redirect()->back()->with('success', 'تم تسجيل الدفعة بنجاح');
    }

    // ───────────────────────────────────────────────
    // عرض دفعات عميل معين
    // ───────────────────────────────────────────────
    public function show($id)
    {
        // ✅ إصلاح: جلب saleCustomer مباشرة بدلاً من الاعتماد على أول دفعة
        // هذا يمنع تعطل الصفحة عندما لا توجد دفعات بعد
        $saleCustomer = UnitSaleCustomer::with([
            'unitSale.marketer',
            'unitSale.unit.project',
            'customer',
            'payments',
        ])->findOrFail($id);

        $unitSale = $saleCustomer->unitSale;
        $payments = $saleCustomer->payments;

        return view('payments.show', compact('payments', 'saleCustomer', 'unitSale'));
    }

    // ───────────────────────────────────────────────
    // تصدير دفعات العميل
    // ───────────────────────────────────────────────
    public function exportCustomerPayments($id)
    {
        $unitSale = UnitSale::with([
            'unit',
            'saleCustomers.customer',
        ])->findOrFail($id);

        $customerName = $unitSale->customer_names;
        $unitName     = $unitSale->unit->unit_number . ' ' . $unitSale->unit->type;

        return Excel::download(
            new PaymentCustomerExport($id, $customerName, $unitName),
            'customer_payments.xlsx'
        );
    }
}