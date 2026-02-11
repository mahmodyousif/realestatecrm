<?php

namespace App\Exports;

use App\Models\UnitSale;
use Maatwebsite\Excel\Concerns\FromCollection;

class UnitSalesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UnitSale::all();
    }
}
