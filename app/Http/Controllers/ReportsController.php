<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\UnitSale;
use App\Models\Unit;
use Carbon\Carbon;
use App\Models\Project;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
class ReportsController extends Controller
{
    public function index(Request $request){

        if ($request->filled(['from', 'to'])) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to   = Carbon::parse($request->to)->endOfDay();
            $PaymentInDate = Payment::whereBetween('created_at' , [$from , $to])->sum('amount_paid') ; 
            $unitSalesCountInDate = UnitSale::whereBetween('created_at', [$from, $to])->count();

        } else {
            // افتراضي
            $PaymentInDate  =   0;
            $unitSalesCountInDate = 0; 
        }
        


        $data = Unit::with(['project' , 'unitSale.payments' , 'unitSale.buyer'])->where('status' ,'!=', 'available')->get() ;
        $unitSalesCount = UnitSale::count();
        $totalPrice = UnitSale::sum('total_price');
        $totalPaid = Payment::sum('amount_paid') ;
        $remaining = $totalPrice - $totalPaid  ;

        $todaySales = UnitSale::whereDate('sale_date' , today())->count();
        // حساب المبلغ المدفوع اليوم 
        $todayPayments = Payment::whereDate('created_at' , today())->sum('amount_paid');

        // عدد الوحدات المباعة هذا الشهر
        $currentMonthSalesCount = UnitSale::whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->count()  ; 
        // اجمالي المدفوع هذا الشهر
        $currentMonthSalesPayment = Payment::whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->sum('amount_paid') ; 
        // تجهيز بيانات المبيعات اليومية للشهر الحالي للرسم البياني
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfToday = Carbon::now()->endOfDay();

        $salesQuery = UnitSale::whereBetween('sale_date', [$startOfMonth->toDateString(), $endOfToday->toDateString()])
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
            $salesData[] = isset($salesQuery[$date]) ? (float) $salesQuery[$date]->total : 0;
        }

        // إحصاءات الوحدات حسب المشروع (عدد المتاحة / المحجوزة / المباعة)
        $projects = Project::withCount([
            'units as available_count' => function ($q) { $q->where('status', 'available'); },
            'units as reserved_count' => function ($q) { $q->where('status', 'reserved'); },
            'units as sold_count'     => function ($q) { $q->where('status', 'sold'); },
        ])->get();

        $projectLabels = $projects->pluck('name')->toArray();
        $projectAvailable = $projects->pluck('available_count')->map(fn($v)=>(int)$v)->toArray();
        $projectReserved = $projects->pluck('reserved_count')->map(fn($v)=>(int)$v)->toArray();
        $projectSold = $projects->pluck('sold_count')->map(fn($v)=>(int)$v)->toArray();

        return view('reports' , compact(
            'data' , 
            'unitSalesCount' , 
            'totalPrice' ,
            'totalPaid' ,
            'remaining',
            'PaymentInDate' , 
            'currentMonthSalesCount' ,
            'unitSalesCountInDate',
            'todaySales' , 
            'todayPayments' ,
            'currentMonthSalesPayment',
            'salesLabels',
            'salesData',
            'projectLabels',
            'projectAvailable',
            'projectReserved',
            'projectSold'
        ));

    }

    public function export() 
    {
        return Excel::download(new SalesReportExport, 'reports.xlsx');
    }
}
