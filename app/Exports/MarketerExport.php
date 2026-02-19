<?php

namespace App\Exports;


use App\Models\UnitSale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class MarketerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $marketerId ; 

    public function __construct($marketerId)
    {
        $this->marketerId = $marketerId ;
    }
    public function collection()
    {
        return UnitSale::with([
            'marketer',
            'unit.project.company'
        ])->where('marketer_id' , $this->marketerId)->get();
    }

    public function headings(): array 
    {
        return [
            'اسم المسوق',
            'نموذج الوحدة' ,
            'نوع الوحدة' ,
            'الشركة' ,
            'المشروع',
            'سعر البيع' ,
            'قيمة العمولة' ,
            'تاريخ البيع'
        ];
    }

    public function map($sale): array
    {
        return[
            $sale->marketer->name ?? '-',
            $sale->unit->unit_number ?? '-',
            $sale->unit->type ?? '-',
            $sale->unit->project->company->name ?? '-',
            $sale->unit->project->name ?? '-',
            $sale->total_price ?? 0,
            $sale->commission ?? 0,
            $sale->sale_date ?? '-',
        ] ;
    }

    public function styles(Worksheet $sheet)
    {
         // تنسيق النص للصفوف A:H
         $sheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:H1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
         $sheet->getStyle('F2:G' . $sheet->getHighestRow())
             ->getNumberFormat()
             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

     
    }
}
