<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\UnitSale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsControllerV2 extends Controller
{

    public function index(Request $request)
    {
    $companyId = $request->company_id;
    $projectId = $request->project_id;
    $from = $request->from;
    $to = $request->to;

    $companies = Company::select('id','name')->get();
    
    $projects = Project::when($companyId, function ($q) use ($companyId) {
        $q->where('company_id', $companyId);
    })
    ->select('id','name','company_id')
    ->get();

    $salesQuery = UnitSale::query()
    ->with([
        'unit.project.company',
        'payments',
        'buyer'
    ]);

    $salesQuery->when($projectId, function ($q) use ($projectId) {
        $q->whereHas('unit.project', function ($qq) use ($projectId) {
            $qq->where('id', $projectId);
        });
    });

    $salesQuery->when($companyId, function ($q) use ($companyId) {
        $q->whereHas('unit.project.company', function ($qq) use ($companyId) {
            $qq->where('id', $companyId);
        });
    });

    if ($from && $to) {
        $salesQuery->whereBetween(
            'sale_date',
            [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ]
        );
    }

    $sales = $salesQuery->get();
    $totalPrice = $sales->sum('total_price');
    $totalPaid = $sales->flatMap->payments->sum('amount_paid');
    $remaining = $totalPrice - $totalPaid;
    return view('reports', compact(
        'companies',
        'projects',
        'sales',
        'totalPrice',
        'totalPaid',
        'remaining'
    ));
}
}
