<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\UnitSale;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Company;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // =======================
        // 1️⃣ إعداد التواريخ
        // =======================
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : null;

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : null;

        // =======================
        // 2️⃣ Base Query (مهم جدًا)
        // =======================
        $baseUnitSalesQuery = UnitSale::with(['unit.project', 'payments', 'buyer'])
            ->when($request->project_id, fn ($q) =>
                $q->whereHas('unit.project', fn ($q2) =>
                    $q2->where('id', $request->project_id)
                )
            )
            ->when($request->company_id, fn ($q) =>
                $q->whereHas('unit.project', fn ($q2) =>
                    $q2->where('company_id', $request->company_id)
                )
            )
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));

        // =======================
        // 3️⃣ البيانات العامة
        // =======================
        $data = (clone $baseUnitSalesQuery)->get();
        $unitSalesCount = (clone $baseUnitSalesQuery)->count();
        $totalPrice = (clone $baseUnitSalesQuery)->sum('total_price');

        // =======================
        // 4️⃣ المدفوعات
        // =======================
        $basePaymentQuery = Payment::when($request->project_id, fn ($q) =>
                $q->whereHas('unitSale.unit.project', fn ($q2) =>
                    $q2->where('id', $request->project_id)
                )
            )
            ->when($request->company_id, fn ($q) =>
                $q->whereHas('unitSale.unit.project', fn ($q2) =>
                    $q2->where('company_id', $request->company_id)
                )
            )
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to));

        $totalPaid = (clone $basePaymentQuery)->sum('amount_paid');
        $remaining = max(0, $totalPrice - $totalPaid);

        // =======================
        // 5️⃣ مؤشرات اليوم
        // =======================
        $todaySales = (clone $baseUnitSalesQuery)
            ->whereDate('sale_date', today())
            ->count();

        $todayPayments = (clone $basePaymentQuery)
            ->whereDate('created_at', today())
            ->sum('amount_paid');

        // =======================
        // 6️⃣ مؤشرات الشهر الحالي
        // =======================
        $currentMonthSalesCount = (clone $baseUnitSalesQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $currentMonthSalesPayment = (clone $basePaymentQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_paid');

        // =======================
        // 7️⃣ المخطط الشهري (sale_date)
        // =======================
        $startOfMonth = now()->startOfMonth();
        $endOfToday = now()->endOfDay();

        $salesChartRaw = (clone $baseUnitSalesQuery)
            ->whereBetween('sale_date', [$startOfMonth, $endOfToday])
            ->selectRaw('DATE(sale_date) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $salesLabels = [];
        $salesData = [];

        for ($d = $startOfMonth->copy(); $d->lte(now()); $d->addDay()) {
            $date = $d->toDateString();
            $salesLabels[] = $d->format('j');
            $salesData[] = isset($salesChartRaw[$date])
                ? (float) $salesChartRaw[$date]->total
                : 0;
        }

        // =======================
        // 8️⃣ المشاريع والإحصاءات
        // =======================
        $projects = Project::withCount([
            'units as available_count' => fn ($q) => $q->where('status', 'available'),
            'units as reserved_count'  => fn ($q) => $q->where('status', 'reserved'),
            'units as sold_count'      => fn ($q) => $q->where('status', 'sold'),
        ])
        ->when($request->company_id, fn ($q) =>
            $q->where('company_id', $request->company_id)
        )
        ->when($request->project_id, fn ($q) =>
            $q->where('id', $request->project_id)
        )
        ->get();

        $projectLabels = $projects->pluck('name')->toArray();
        $projectAvailable = $projects->pluck('available_count')->map(fn ($v) => (int) $v)->toArray();
        $projectReserved  = $projects->pluck('reserved_count')->map(fn ($v) => (int) $v)->toArray();
        $projectSold      = $projects->pluck('sold_count')->map(fn ($v) => (int) $v)->toArray();

        $allProjects = Project::select('id', 'name')->get();
        $companies = Company::select('id', 'name')->get();

        return view('reports', compact(
            'data',
            'allProjects',
            'companies',
            'unitSalesCount',
            'totalPrice',
            'totalPaid',
            'remaining',
            'todaySales',
            'todayPayments',
            'currentMonthSalesCount',
            'currentMonthSalesPayment',
            'salesLabels',
            'salesData',
            'projectLabels',
            'projectAvailable',
            'projectReserved',
            'projectSold'
        ));
    }

    public function export(Request $request)
    {
        return Excel::download(
            new SalesReportExport($request->all()),
            'reports.xlsx'
        );
    }
}