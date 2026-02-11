<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompanyProjectsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Project::where('company_id', $this->companyId)
            ->select('name', 'floors', 'location', 'aria_range', 'status')
            ->get();
    }

    public function headings(): array
    {
        return ['اسم المشروع', 'عدد الطوابق', 'المنطقة', 'المساحة', 'الحالة'];
    }

    public function title(): string
    {
        return 'المشاريع';
    }
}
