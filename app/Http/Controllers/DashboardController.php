<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Unit ; 
use App\Models\UnitSale;
use App\Models\Project ; 
class DashboardController extends Controller
{
    public function index(){
        $totalPrice = UnitSale::sum('total_price') ;
        $totalPaid = Payment::sum('amount_paid') ; 
        $totalRemaining = $totalPrice - $totalPaid ; 
        $projects = Project::with('units')->latest()->take(5)->get(); 
        $projectsCount = Project::count() ;
        $unitsCount =  Unit::count() ; 
        $availableUnitsCount = Unit::where('status', 'available')->count();
        $soldUnitsCount= Unit::where('status', 'sold')->count();
        $reservedUnitsCount = Unit::where('status', 'reserved')->count();
        $latestUnitSold = Unit::with('project')->where('status' , 'sold')->latest()->take(5)->get() ;
        $projectCountThisMonth = Project::whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->Count() ;
        $unitCountThisMonth = Unit::whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->where('status' , 'available')->Count() ;
        
        $soldUnitThisMonth = UnitSale::whereHas('unit' , function($query){
            $query->where('status'  , 'sold');
        })->whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->Count() ; 
        
        // الإيرادات حسب المشروع
        $revenue = $projects->map(fn($p) => $p->units->sum('price')); 


    // إحصاءات الوحدات حسب المشروع (عدد المتاحة / المحجوزة / المباعة)
        $projectsChart = Project::withCount([
            'units as available_count' => function ($q) { $q->where('status', 'available'); },
            'units as reserved_count' => function ($q) { $q->where('status', 'reserved'); },
            'units as sold_count'     => function ($q) { $q->where('status', 'sold'); },
        ])->get();

        $projectLabels = $projectsChart->pluck('name')->toArray();
        $projectAvailable = $projectsChart->pluck('available_count')->map(fn($v)=>(int)$v)->toArray();
        $projectReserved = $projectsChart->pluck('reserved_count')->map(fn($v)=>(int)$v)->toArray();
        $projectSold = $projectsChart->pluck('sold_count')->map(fn($v)=>(int)$v)->toArray();

        return view('dashboard', compact(
            'projects' ,
            'projectsCount' ,
            'unitsCount' ,
            'availableUnitsCount' ,
            'soldUnitsCount' ,
            'reservedUnitsCount' ,
            'totalPaid' ,
            'totalRemaining'   ,
            'latestUnitSold' ,
            'projectCountThisMonth',
            'unitCountThisMonth' ,
            'soldUnitThisMonth' ,
            'projectLabels',
            'projectAvailable',
            'projectReserved',
            'projectSold'
        ));
    }
}
