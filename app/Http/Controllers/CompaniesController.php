<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payment;
use App\Models\UnitSale;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Exports\CompanyReportExport;
use Maatwebsite\Excel\Facades\Excel;

class CompaniesController extends Controller
{
    public function index($id)
    {
        $company = Company::withCount([
            'units as available_units_count' => fn($q) => $q->where('units.status', 'available'),
            'units as sold_units_count'      => fn($q) => $q->where('units.status', 'sold'),
            'units as reserved_units_count'  => fn($q) => $q->where('units.status', 'reserved'),
        ])->withCount('projects')->findOrFail($id);

        // مجموع السعر الإجمالي للوحدات التابعة للشركة
        $totalSalesPrice = UnitSale::whereHas('unit.project', function($q) use ($id) {
            $q->where('company_id', $id);
        })->sum('total_price');

        // مجموع المدفوعات الفعلية من جدول payments للوحدات التابعة للشركة
        $amountPaid = Payment::whereHas('unitSale.unit.project', function($q) use ($id) {
            $q->where('company_id', $id);
        })->sum('amount_paid');

        

        // اجمالي المدفوع اليوم للشركة
        $amountPaidToday = Payment::whereHas('unitSale.unit.project', function($q) use ($id) {
            $q->where('company_id', $id);
        })->whereDate('created_at' , today())->sum('amount_paid');


        
        $todaySalesCount = UnitSale::whereHas('unit.project', function($q) use ($id) {
            $q->where('company_id', $id);
        })->whereDate('created_at', today())->count();
        
        // المبلغ المدفوع للشركة هذا الشهر 
        $currentMonthSalesPayment = Payment::whereHas('unitSale.unit.project', function($q) use ($id) {
            $q->where('company_id', $id) ;
        })->whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->sum('amount_paid');



        $currentMonthSalesCount = UnitSale::whereHas('unit.project', function($q) use ($id) {
            $q->where('company_id', $id);
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->count();
        
        // المبلغ المتبقي
        $remainingAmount = $totalSalesPrice - $amountPaid;

        // جلب كل الوحدات والمشاريع للشركة مع تسمية منفصلة لكل pagination
        $allUnits = $company->units()->with('project')->paginate(5, ['*'], 'units_page');
        $allProjects = $company->projects()->paginate(5, ['*'], 'projects_page');


        // جلب عدد المشاريع هذا الشهر 

        $projectCountThisMonth = Project::whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->Count() ;
    
       
        return view('company', compact(
            'company',
            'allUnits',
            'allProjects',
            'totalSalesPrice',
            'amountPaid',
            'remainingAmount' ,
            'amountPaidToday',
            'todaySalesCount' ,
            'currentMonthSalesPayment' ,
            'currentMonthSalesCount',
            'projectCountThisMonth'

        ));
    }

    public function store(Request $request)
    {
        $dataToInsert = ['name' => $request->name];
        Company::create($dataToInsert);
        return redirect()->route('dashboard')->with('success', 'تم إضافة الشركة بنجاح');
    }

    public function export($id)
    {
        return Excel::download(
            new CompanyReportExport($id),
            'company_report.xlsx'
        );
}
}
