<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyProjectsSheet implements FromCollection, WithHeadings, WithTitle,WithStyles , ShouldAutoSize
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Project::where('company_id', $this->companyId)
            ->select('name', 'floors','total_units' , 'location', 'aria_range', 'status','notes')
            ->get();
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'عدد الطوابق',
            'إجمالي الوحدات' ,
            'المنطقة',
            'نطاق المساحات', 
            'الحالة',
            'ملاحظات'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:G1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
    }

    public function title(): string
    {
        return 'المشاريع';
    }
}
