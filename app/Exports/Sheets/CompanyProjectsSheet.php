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

class CompanyProjectsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        // نرجع Collection لتتوافق مع FromCollection
        return Project::where('company_id', $this->companyId)
            ->select('name', 'floors','total_units', 'location', 'aria_range', 'status','notes')
            ->get()
            ->map(function ($project) {
                // تحويل الحالة
                $statusText = match($project->status) {
                    'active' => 'نشط',
                    'planning' => 'تحت التجهيز',
                    'completed' => 'مكتمل',
                    default => $project->status,
                };
                return [
                    $project->name,
                    $project->floors,
                    $project->total_units,
                    $project->location,
                    $project->aria_range,
                    $statusText,
                    $project->notes,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'عدد الطوابق',
            'إجمالي الوحدات',
            'المنطقة',
            'نطاق المساحات', 
            'الحالة',
            'ملاحظات'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // محاذاة جميع الأعمدة
        $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // تنسيق رأس الأعمدة
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // تنسيق الأعمدة الرقمية (عدد الطوابق، إجمالي الوحدات) كأرقام
        $sheet->getStyle('B2:C' . $sheet->getHighestRow())
              ->getNumberFormat()
              ->setFormatCode('#,##0');
    }

    public function title(): string
    {
        return 'المشاريع';
    }
}