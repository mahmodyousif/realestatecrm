<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\CustomerFullExport;
use App\Exports\MarketerExport;
use App\Imports\CustomerImport;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Project;
use App\Models\UnitSale;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomersController extends Controller
{

    public function index(Request $request) {
        $data = Customer::query()
        ->when($request->company_id, function ($query, $companyId) {
            $query->where(function($q) use ($companyId) {
                // المشترين
                $q->whereHas('purchases.unit.project', function($qq) use ($companyId) {
                    $qq->where('company_id', $companyId);
                })
                // المستثمرين
                ->orWhereHas('investor.unit.project', function($qq) use ($companyId) {
                    $qq->where('company_id', $companyId);
                })
                // البائعين
                ->orWhereHas('marketedSales.unit.project', function($qq) use ($companyId) {
                    $qq->where('company_id', $companyId);
                });
            });
        })
        ->when($request->project_id, function ($query, $projectId) {
            $query->where(function($q) use ($projectId) {
                $q->whereHas('purchases.unit.project', function($qq) use ($projectId) {
                    $qq->where('id', $projectId);
                })
                ->orWhereHas('investor.unit.project', function($qq) use ($projectId) {
                    $qq->where('id', $projectId);
                })
                ->orWhereHas('marketedSales.unit.project', function($qq) use ($projectId) {
                    $qq->where('id', $projectId);
                });
            });
        })
        ->get();
        $allProjects = Project::all();
        $companies = Company::all();    
        return view('customers.index' , compact('data', 'allProjects', 'companies')) ;
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
        $commission = UnitSale::where('marketer_id' , $id)->sum('commission') ;
        $remaining = ($marketer->totalPrice ?? 0) - ($marketer->totalPaid ?? 0);
        return view('customers.marketer_show', compact('marketer','commission', 'remaining'));
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

    public function exportMarketer($id)
    {
        $marketer = Customer::findOrFail($id);
        $fileName = 'marketer_' . $marketer->name . '_report.xlsx';
        return Excel::download(new MarketerExport($id), $fileName);
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
