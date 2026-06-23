<?php

namespace App\Exports;


use App\Models\UnitSaleCustomer;
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
        return UnitSaleCustomer::with([
            'customer',
            'marketer',
            'unitSale.unit.project.company',
        ])->where('marketer_id' , $this->marketerId)->get();
    }

    public function headings(): array 
    {
        return [
           'اسم المسوق',
            'اسم المشتري',
            'نموذج الوحدة',
            'نوع الوحدة',
            'الشركة',
            'المشروع',
            'قيمة الحصة',
            'قيمة العمولة',
            'المدفوع',
            'المتبقي',
            'تاريخ البيع'
        ];
    }

    public function map($sale): array
    {
        $paid = $sale->payments->sum('amount_paid');

        return [
        $sale->marketer->name ?? '-',

        $sale->customer->name ?? '-',

        $sale->unitSale->unit->unit_number ?? '-',

        $sale->unitSale->unit->type ?? '-',

        $sale->unitSale->unit->project->company->name ?? '-',

        $sale->unitSale->unit->project->name ?? '-',

        $sale->share_amount ?? 0,

        $sale->commission_amount ?? 0,

        $paid,

        ($sale->share_amount ?? 0) - $paid,

        $sale->sale_date ?? '-',
    ];
}
    

    public function styles(Worksheet $sheet)
    {
         // تنسيق النص للصفوف A:H
         $sheet->getStyle('A:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:K1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
         $sheet->getStyle('G2:J' . $sheet->getHighestRow())
             ->getNumberFormat()
             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

     
    }
}
