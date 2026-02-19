<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\UnitSale;
use Carbon\Carbon;
use App\Models\Project;
use App\Exports\SalesReportExport;
use App\Models\Company;
use Maatwebsite\Excel\Facades\Excel;
class ReportsController extends Controller
{
    public function index(Request $request){
        // أساسيات الفلترة على المشروع والشركة فقط
        $unitSalesQuery = UnitSale::with(['unit.project', 'payments', 'buyer'])
            ->when($request->project_id, fn($q) => $q->whereHas('unit.project', fn($q2) => $q2->where('id', $request->project_id)))
            ->when($request->company_id, fn($q) => $q->whereHas('unit.project', fn($q2) => $q2->where('company_id', $request->company_id)));
    
        $data = $unitSalesQuery->get();
        $unitSalesCount = $unitSalesQuery->count();
        $totalPrice = $unitSalesQuery->sum('total_price');
    
        // المدفوعات
        $paymentQuery = Payment::when($request->project_id, fn($q) => $q->whereHas('unitSale.unit.project', fn($q2) => $q2->where('id', $request->project_id)))
            ->when($request->company_id, fn($q) => $q->whereHas('unitSale.unit.project', fn($q2) => $q2->where('company_id', $request->company_id)));
    
        $totalPaid = $paymentQuery->sum('amount_paid');
        $remaining = $totalPrice - $totalPaid;
    
        // الأداء اليوم والشهر الحالي يبقى على أساس التاريخ الفعلي
        $todaySales = $unitSalesQuery->whereDate('sale_date', today())->count();
        $todayPayments = $paymentQuery->whereDate('created_at', today())->sum('amount_paid');
        $currentMonthSalesCount = $unitSalesQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $currentMonthSalesPayment = $paymentQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount_paid');
    
        // المخطط الشهري
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfToday = Carbon::now()->endOfDay();
    
        $salesChartQuery = $unitSalesQuery->whereBetween('sale_date', [$startOfMonth->toDateString(), $endOfToday->toDateString()])
            ->selectRaw('DATE(sale_date) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
    
        $salesLabels = [];
        $salesData = [];
        for ($d = $startOfMonth->copy(); $d->lte(Carbon::now()); $d->addDay()) {
            $date = $d->toDateString();
            $salesLabels[] = $d->format('j');
            $salesData[] = isset($salesChartQuery[$date]) ? (float) $salesChartQuery[$date]->total : 0;
        }
    
        // المشاريع والإحصاءات كما هي
        $projects = Project::withCount([
            'units as available_count' => fn($q) => $q->where('status', 'available'),
            'units as reserved_count' => fn($q) => $q->where('status', 'reserved'),
            'units as sold_count' => fn($q) => $q->where('status', 'sold'),
        ])->when($request->company_id, function($q) use ($request){
            $q->where('company_id' , $request->company_id) ;
        })->when($request->project_id, fn($q) => $q->where('id', $request->project_id))
        ->get();
    
        $projectLabels = $projects->pluck('name')->toArray();
        $projectAvailable = $projects->pluck('available_count')->map(fn($v)=>(int)$v)->toArray();
        $projectReserved = $projects->pluck('reserved_count')->map(fn($v)=>(int)$v)->toArray();
        $projectSold = $projects->pluck('sold_count')->map(fn($v)=>(int)$v)->toArray();
    
        $allProjects = Project::select('id', 'name')->get();
        $companies = Company::select('id', 'name')->get();
    
        return view('reports', compact(
            'data', 'allProjects', 'companies', 'unitSalesCount', 'totalPrice', 'totalPaid', 'remaining',
            'todaySales', 'todayPayments', 'currentMonthSalesCount', 'currentMonthSalesPayment',
            'salesLabels', 'salesData', 'projectLabels', 'projectAvailable', 'projectReserved', 'projectSold'
        ));
    }
    
    

    public function export() 
    {
        return Excel::download(new SalesReportExport, 'reports.xlsx');
    }
}
