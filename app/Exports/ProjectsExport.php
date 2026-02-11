<?php

namespace App\Exports;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;


class ProjectsExport implements   FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize 
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
        return Project::with('company')->get() ;
    }

    public function map($project): array
    {
        return [
            $project->company?->name ?? '—',
            $project->name,
            $project->floors,
            $project->total_units,
            $project->aria_range,
            $project->location,
            $project->status == 'active' ? 'نشط' :($project->status == 'planning' ? 'تحت الانشاء'  : 'مكتمل'),
        
        ];
    }

    public function headings(): array
    {
        return [
            'الشركة',
            'اسم المشروع',
            'عدد الطوابق',
            'اجمالي الوحدات' , 
            'نطاق المساحات' , 
            'الموقع' , 
            'الحالة' ,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}

