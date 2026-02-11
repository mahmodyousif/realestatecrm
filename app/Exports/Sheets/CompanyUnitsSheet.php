<?php

namespace App\Exports\Sheets;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompanyUnitsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Unit::whereHas('project', fn($q) =>
            $q->where('company_id', $this->companyId)
        )->with('project')
         ->get()
         ->map(function ($unit) {
            return [
                $unit->unit_number,
                $unit->type,
                $unit->project->name,
                $unit->area,
                $unit->floor,
                $unit->status,
                $unit->price,
            ];
         });
    }

    public function headings(): array
    {
        return [
            'رقم الوحدة',
            'النوع',
            'المشروع',
            'المساحة',
            'الطابق',
            'الحالة',
            'السعر'
        ];
    }

    public function title(): string
    {
        return 'الوحدات';
    }
}
