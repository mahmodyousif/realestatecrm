<?php

namespace App\Http\Controllers;
use App\Models\Unit ; 
use App\Models\Project;
use App\Models\Customer;
use App\Models\UnitSale;
use App\Exports\UnitExport;
use App\Imports\UnitsImport;
use App\Models\Company;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UnitsController extends Controller
{

    public function index(Request $request) {
        $data = Unit::with(['project'])
        ->when($request->company_id, function($q) use ($request){
            $q->whereHas('project', function($query) use ($request){
                $query->where('company_id' , $request->company_id) ;
            }) ;
        }) 
        ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->when($request->floor, fn($q) => $q->where('floor', $request->floor))
        ->paginate(10);

        return view('units.index', [
            'data' => $data,
            'projects' => Project::all(),
            'companies' =>Company::all(),
            'buyers' =>Customer::where('type','buyer')->get(),
            'marketers'=>Customer::where('type','marketer')->get(),
            'investors'=>Customer::where('type','investor')->get(),
        ]);
    }

    public function store(Request $request){
        $dataToInsert = [
            'project_id'=>$request->project_id ,
            'type' =>$request->type,
            'unit_number' =>$request->unit_number,
            'area' =>$request->area,
            'floor' =>$request->floor,
            'rooms'=>$request->rooms,
            'price'=>$request->price,
            'status' => 'available',

        ] ;
        Unit::create($dataToInsert) ;
        $availabelUnitsCount = Unit::where('status', 'available')->count();
        return redirect()->route('units')->with('success', 'تم إضافة الوحدة بنجاح');

    }

    public function show($id){
        $unit = Unit::with('unitSale')->with('buyer')->findOrFail($id);
        $totalPrice = $unit->unitSale?->total_price ?? 0;
        $totalPaid  = $unit->unitSale?->payments->sum('amount_paid') ?? 0;
        $remaining  = $totalPrice - $totalPaid;
        $buyers = Customer::where('type', 'buyer')->get();
        $marketers = Customer::where('type', 'marketer')->get();
        $investors = Customer::where('type', 'investor')->get();
        return view('units.show', compact(
                'unit' , 
                'totalPrice' ,
                'totalPaid' ,
                'remaining',
                'buyers',
                'marketers',
                'investors'
              )) ;  
    } 

    public function edit($id) {
        $unit = Unit::find($id) ;
        $projects = Project::all() ;
        return view('units.edit' , compact('unit', 'projects')) ;  
    }

    public function update($id, Request $request){
        $unit = Unit::find($id);
        $projects = Project::all() ;
        
        $unit->project_id = $request->project_id ;
        $unit->type = $request->type ;
        $unit->unit_number = $request->unit_number ;
        $unit->area = $request->area ;
        $unit->floor = $request->floor ;
        $unit->rooms = $request->rooms ;
        $unit->price = $request->price ;
        $unit->save();
        return redirect()->route('edit_unit', $unit->id)
        ->with('success', 'تم التحديث بنجاح');
    }

    public function delete($id){
        $unit = Unit::findOrFail($id) ; 
        $unit->delete() ;
        return redirect()->route('units')->with('success', 'تم حذف الوحدة بنجاح');
    }


    public function unitSell($id){
        $unit = Unit::findOrFail($id) ;
        return view('units.sell', [
            'unit' => $unit,
            'projects' => Project::all(),
            'buyers' => Customer::where('type', 'buyer')->get(),
            'marketers' => Customer::where('type', 'marketer')->get(),
            'investors' => Customer::where('type', 'investor')->get(),
        ]) ;
    }

    public function export()
    {
        return Excel::download(new UnitExport, 'units.xlsx');
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
        $import = new UnitsImport();
        Excel::import($import, $request->file('file'));
        $added = $import->addedCount;
        
        return redirect()->back()->with('success', "تم إضافة {$added} وحدة جديد بنجاح!");

    }
}
