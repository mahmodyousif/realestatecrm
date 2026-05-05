<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UnitSale;
use Illuminate\Http\Request;

class ManagmentController extends Controller
{
    public function index()
    {
        $data = UnitSale::with('unit.project.company','payments','customers')->paginate(10)->appends(request()->query());; 
        return view('units.managment', compact('data')) ;
    }
}
