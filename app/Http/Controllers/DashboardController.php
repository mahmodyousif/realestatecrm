<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\Unit ; 
use App\Models\UnitSale;
use App\Models\Project ; 
class DashboardController extends Controller
{
    public function index(Request $request){
        $totalPrice = UnitSale::sum('total_price') ;
        $totalPaid = Payment::sum('amount_paid') ; 
        $totalRemaining = $totalPrice - $totalPaid ; 

        $projects = Project::with('units')
        ->when($request->company_id, function($q) use ($request){
            $q->where('company_id' , $request->company_id) ;
        })->latest()->take(5)->get(); 

        $projectsCount = Project::when($request->company_id, function($q) use ($request){
            $q->where('company_id' , $request->company_id) ;
        })->count();

        $unitsCount =  Unit::when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        })->count() ; 


        $availableUnitsCount = Unit::where('status', 'available')->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        })->count();

        $soldUnitsCount= Unit::where('status', 'sold')->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        })->count();

        $reservedUnitsCount = Unit::where('status', 'reserved')->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        })->count();

        $latestUnitSold = Unit::with('project')->where('status' , 'sold')
        ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        })
        ->latest()->take(5)->get() ;

        $projectCountThisMonth = Project::when($request->company_id, function($q) use ($request){
            $q->where('company_id' , $request->company_id) ;
        })-> whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->Count() ;
       
       
        $unitCountThisMonth = Unit::when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        })->whereMonth('created_at' , now()->month)->whereYear('created_at' , now()->year)->where('status' , 'available')->Count() ;
        
        $soldUnitThisMonth = UnitSale::whereHas('unit', function($q) use ($request){
            $q->where('status', 'sold')
              ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
              ->when($request->company_id, function($q) use ($request){
                  $q->whereHas('project', fn($p) => $p->where('company_id', $request->company_id));
              });
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        // الإيرادات حسب المشروع
        $revenue = $projects->map(fn($p) => $p->units->sum('price')); 


    // إحصاءات الوحدات حسب المشروع (عدد المتاحة / المحجوزة / المباعة)
        $projectsChart = Project::withCount([
            'units as available_count' => function ($q) { $q->where('status', 'available'); },
            'units as reserved_count' => function ($q) { $q->where('status', 'reserved'); },
            'units as sold_count'     => function ($q) { $q->where('status', 'sold'); },
        ])->when($request->company_id, function($q) use ($request){
            $q->where('company_id' , $request->company_id) ;
        })->get();

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
