<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\UnitSale;
use Illuminate\Http\Request;
use App\Exports\CustomerExport;
use App\Exports\CustomerFullExport;
use App\Imports\CustomerImport;
use Maatwebsite\Excel\Facades\Excel;

class CustomersController extends Controller
{

    public function index() {
        $data = Customer::all();
        return view('customers.index' , ['data' => $data]);
    }

    public function show($id){
        $customer = Customer::with('purchases.unit', 'purchases.payments')
        ->withSum('purchases as totalPrice', 'total_price')
        ->withSum('payments as totalPaid', 'amount_paid')
        ->withCount('purchases as purchases_count')
        ->findOrFail($id);
    
    $remaining = ($customer->totalPrice ?? 0) - ($customer->totalPaid ?? 0);
    
    return view('customers.show', compact('customer', 'remaining'));
    }

    public function marketerShow($id){
        $marketer = Customer::with('marketedSales.unit')
            ->withSum('marketedSales as totalPrice', 'total_price')
            ->withSum('sellers as totalPaid', 'amount_paid')
            ->withCount('marketedSales as sales_count')
            ->findOrFail($id);
        $remaining = ($marketer->totalPrice ?? 0) - ($marketer->totalPaid ?? 0);
        return view('customers.marketer_show', compact('marketer', 'remaining'));
    }

    public function store(Request $request){
        $dataToInsert = [
            'type'=>$request->type,
            'name'=>$request->name,
            'id_card'=>$request->id_card,
            'phone'=>$request->phone,
            'email'=>$request->email,
            'address'=>$request->address,
            'notes'=>$request->notes
        ] ; 
        Customer::create($dataToInsert) ;
        return redirect()->route('customers')->with('success', 'تم إضافة العميل بنجاح');
    }

    public function edit($id) {
        $customer = Customer::find($id) ;
        return view('customers.edit' , compact('customer')) ;  
    }

    public function update($id, Request $request){
        $customer = Customer::find($id);
        $customer->type = $request->type;
        $customer->name = $request->name;
        $customer->id_card= $request->id_card;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->notes = $request->notes;
        $customer->save();
        return redirect()->route('edit_customer', $customer->id)
        ->with('success', 'تم التحديث بنجاح');
    }

    public function exportCustomerFull($id)
    {
        // جلب الـ Customer مع كل العلاقات المطلوبة
        $customer = Customer::with(['purchases.unit', 'purchases.payments'])->findOrFail($id);
    
        $fileName = 'customer_' . $customer->name . '_full_report.xlsx';
    
        // تمرير الـ Model مباشرة للـ Export
        return Excel::download(new CustomerExport($customer), $fileName);
    }


     public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
        $import = new CustomerImport();
        Excel::import($import, $request->file('file'));
        $added = $import->addedCount;
        return redirect()->back()->with('success', "تم إضافة {$added} عميل جديد بنجاح!");

    }
}
