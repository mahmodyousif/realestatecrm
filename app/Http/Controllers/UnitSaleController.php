<?php

namespace App\Http\Controllers;

use App\Exports\UnitSalesExport;
use App\Models\Unit;
use App\Models\UnitSale;
use App\Models\UnitSaleCustomer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UnitSaleController extends Controller
{
    public function store(Request $request) {

        $validated = $request->validate([
            'unit_id' => ['required', Rule::exists('units', 'id')],
            'marketer_id' => ['nullable', 'exists:customers,id'],
            'customers' => ['required', 'array', 'min:1'],
            'customers.*.id' => ['required', 'exists:customers,id'],
            'customers.*.type' => ['required', 'in:customer,investor'],
            'customers.*.share' => ['required', 'numeric', 'min:1', 'max:100'],
            'customers.*.amount_paid' => ['nullable', 'numeric', 'min:0'],
            'customers.*.contract_number' => ['required', 'string', 'unique:unit_sale_customers,contract_number'],
            'sale_date' => ['required', 'date'],
            'payment_method' => ['required', 'string'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'lte:total_price'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
        ], [
            'customers.*.contract_number.unique' => 'رقم العقد المستخدم لأحد الشركاء موجود مسبقًا، يرجى تغييره',
            'customers.*.id.required' => 'يجب اختيار عميل أو مستثمر لكل مشتري',
            'customers.*.id.exists' => 'العميل أو المستثمر المختار غير موجود في النظام',
            'customers.*.type.required' => 'يجب تحديد نوع المشتري',
            'customers.*.type.in' => 'نوع المشتري يجب أن يكون عميل أو مستثمر',
            'customers.*.share.required' => 'يجب تحديد نسبة البيع لكل مشتري',
            'customers.*.share.numeric' => 'نسبة البيع يجب أن تكون رقماً',
            'customers.*.share.min' => 'نسبة البيع يجب أن تكون 1% على الأقل',
            'customers.*.share.max' => 'نسبة البيع يجب أن تكون 100% على الأكثر',
            'customers.*.contract_number.required' => 'يجب إدخال رقم العقد لكل مشتري',
            'discount.lte' => 'الخصم لا يمكن أن يكون أكبر من سعر الوحدة قم بتعديل الخصم',
            'unit_price.min' => 'سعر الوحدة يجب أن يكون 0 أو أكثر',
            'discount.min' => 'الخصم يجب أن يكون 0 أو أكثر',
            'total_price.min' => 'السعر الإجمالي يجب أن يكون 0 أو أكثر',
            'unit_id.required' => 'رقم الوحدة مطلوب',
            'sale_date.required' => 'تاريخ البيع مطلوب',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'unit_id.exists' => 'رقم الوحدة غير موجود في النظام',
        ]);
    
    // التحقق من الحصص إذا كان هناك عملاء متعددين
    if (isset($validated['customers']) && !empty($validated['customers'])) {
        $totalShare = array_sum(array_column($validated['customers'], 'share'));
        if ($totalShare != 100) {
            return back()->with('error', 'مجموع الحصص يجب أن يكون 100%');
        }
    }

    $totalPrice = $validated['unit_price'] - ($validated['discount'] ?? 0);
    $totalPaid = 0;
    foreach ($validated['customers'] as $cust) {
        $shareAmount = $totalPrice * ($cust['share'] / 100);
        if (!empty($cust['amount_paid']) && $cust['amount_paid'] > $shareAmount) {
            return back()->with('error', 'المبلغ المدفوع لشريك لا يمكن أن يتجاوز نصيبه');
        }
        $totalPaid += $cust['amount_paid'] ?? 0;
    }

    if($totalPaid > $totalPrice) {
        return back()->with('error', 'إجمالي الدفعات لا يمكن أن يتجاوز السعر الكلي');
    }

    
        // إنشاء عملية البيع في unit_sales
        $unitSale = UnitSale::create([
            'unit_id' => $validated['unit_id'],
            'marketer_id' => $validated['marketer_id'] ?? null,
            'sale_date' => $validated['sale_date'],
            'payment_method' => $validated['payment_method'],
            'unit_price' => $validated['unit_price'],
            'discount' => $validated['discount'] ?? 0,
            'total_price' => $validated['unit_price'] - ($validated['discount'] ?? 0),
            'commission' => $validated['commission'] ,
        ]);

        // إنشاء العملاء المشتركين
        if (isset($validated['customers']) && !empty($validated['customers'])) {
            $totalPrice = $validated['unit_price'] - ($validated['discount'] ?? 0);
            foreach ($validated['customers'] as $customerData) {
                $shareAmount = $totalPrice * ($customerData['share'] / 100);
                $custRecord = UnitSaleCustomer::create([
                    'unit_sale_id' => $unitSale->id,
                    'customer_id' => $customerData['id'],
                    'contract_number' => $customerData['contract_number'],
                    'share_percentage' => $customerData['share'],
                    'share_amount' => $shareAmount,
                ]);

                // دفعة خاصة بهذا الشريك
                if (!empty($customerData['amount_paid']) && $customerData['amount_paid'] > 0) {
                    $custRecord->payments()->create([
                        'amount_paid' => $customerData['amount_paid'],
                        'payment_date' => $validated['sale_date'],
                        'payment_method' => $validated['payment_method'],
                        'reference_number' => 0,
                        'notes' => 'دفعة شريك',
                    ]);
                }
            }
        }
    
        // تحديث حالة الوحدة
        $unit = Unit::findOrFail($validated['unit_id']);

        // حساب إجمالي المدفوعات من البيانات المرسلة
        $totalPaid = array_sum(array_column($validated['customers'], 'amount_paid'));

        if ($totalPaid == $validated['total_price']) {
            $unit->status = 'sold';
        } elseif ($totalPaid > 0 && $totalPaid < $validated['total_price']) {
            $unit->status = 'partially_paid';
        } else {
            $unit->status = 'reserved';
        }
        $unit->save();
    
        return redirect()->route('units')->with('success', 'تم اجراء عملية بيع بنجاح');
    }
    


    public function unitSellImport(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    $import = new \App\Imports\SoldUnitImport();
    Excel::import($import, $request->file('file'));

    $response = [];

    if ($import->addedCount > 0) {
        $response['success'] = "تم تسجيل {$import->addedCount} عملية بيع بنجاح";
    } else {
        $response['error'] = 'لم يتم تسجيل أي عملية بيع';
    }

    // ✅ فلترة المصفوفات الفارغة فقط
    $warnings = array_filter($import->warningMessages);
    if (!empty($warnings)) {
        $response['warnings'] = $warnings;
    }

    return redirect()->back()->with($response);
}
    public function export()
    {
        return Excel::download(new UnitSalesExport, 'units.xlsx');
    }
}