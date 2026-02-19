<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Company;
use App\Models\Project  ;
use App\Exports\UnitExport;
use Illuminate\Http\Request;

use App\Exports\ProjectsExport;
use App\Exports\ProjectInfoExport;
use App\Imports\ProjectsImport;
use Maatwebsite\Excel\Facades\Excel;

class ProjectsController extends Controller
{
    public function index(Request $request) {
        $data = Project::with('company')->when($request->company_id, function($q) use ($request){
            $q->where('company_id' , $request->company_id) ;
        })->when($request->project_id, fn($q) => $q->where('id', $request->project_id))
        ->get();
        return view('projects.index' , [
            'data' => $data,
            'companies' =>Company::all(),
        ]);
    }


    
    public  function create() {
        return view('projects.create' , [
            'companies' =>Company::all(),
        ]) ;
    }

    public function add_project(Request $request){
        $dataToInsert = [
            'name' =>$request->name,
            'price' =>$request->price,
            'company_id'=>$request->company,
            'floors' =>$request->floors,
            'total_units' =>$request->total_units,
            'aria_range' =>$request->aria_range,
            'location' =>$request->location,
            'status' =>$request->status,
            'notes' =>$request->notes,
        ] ; 
        Project::create($dataToInsert) ;
        $projectsCount = Project::count();

        return redirect()->route('projects')->with('success', 'تم إضافة المشروع بنجاح');
    }

    public function show( $id){
        $project = Project::with('units.unitSale.payments')->findOrFail($id);
        $soldUnits = $project->units->where('status' , '!=', 'available'); 
        $soldUnitsCount = $project->units->where('status', 'sold')->count();
        $reservedUnitsCount = $project->units->where('status' , 'reserved')->count();
        $availableUnitsCount = $project->units->where('status', 'available')->count();
        $totalPaid = $project->units->sum(function($unit){
            return $unit->unitSale ? $unit->unitSale->payments->sum('amount_paid') : 0;
        }); 
        
        $totalPrice = $project->units->sum('price');
        $totalSoldPrice = $soldUnits->sum('price');

        $totalRemaining = $totalSoldPrice  - $totalPaid;
        return view('projects.show', compact('project', 'soldUnitsCount','reservedUnitsCount' , 'availableUnitsCount',  'totalPrice', 'totalPaid', 'totalSoldPrice' ,  'totalRemaining'));
    }


    public function edit($id) {
        $project = Project::find($id) ;
        return view('projects.edit' , compact('project')) ;  
    }

    public function update($id, Request $request){
        $project = Project::find($id);

        $project->name = $request->name ;
        $project->price = $request->price ;
        $project->floors = $request->floors ;
        $project->total_units = $request->total_units ;
        $project->aria_range = $request->aria_range ;
        $project->location = $request->location ;
        $project->status = $request->status ;
        $project->save();
        return redirect()->route('edit_project', $project->id)
        ->with('success', 'تم التحديث بنجاح');
    }

    public function delete($id){
        $project = Project::findOrFail($id) ; 
        $project->delete() ;
        return redirect()->route('projects')->with('success', 'تم حذف المشروع بنجاح');
    }

    public function export(){
        return Excel::download(new ProjectsExport, 'projects.xlsx');
    }

    public function exportInfo(Project $project)
    {
        return Excel::download(new ProjectInfoExport($project), "Project_{$project->name}.xlsx");
    }
    
    public function import(Request $request) 
    {

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
        $import = new ProjectsImport();
        Excel::import($import, $request->file('file'));
        $added = $import->addedCount;
        return redirect()->back()->with('success', "تم إضافة {$added} مشروع جديد بنجاح!");

    }
}


