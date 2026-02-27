<?php

namespace App\Exports;

use App\Models\Unit;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnitExport implements FromCollection , WithMapping , WithHeadings , WithStyles , ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Unit::with(['project.company' , 'unitSale.buyer' , 'unitSale.marketer' ,'unitSale.payments'])->get();
    }

    public function map($unit): array
    {
        $price = $unit->price ?? 0;
        $total_price = $unit->unitSale?->total_price ?? 0 ;
        $amount_paid = $unit->unitSale?->payments->sum('amount_paid') ?? 0;
        $remaining = $total_price  - $amount_paid;

        return [
         
           $unit->project->company->name ?? 'بدون شركة', // اسم الشركة
           $unit->project->name ?? 'بدون مشروع' , 
           $unit->unit_number , 
           $unit->type , 
           $unit->area , 
           $unit->floor ,
           $unit->rooms , 
           $unit->zone , 
           $unit->unitSale?->buyer?->name ?? 'غير مباعة' ,
           $unit->unitSale?->marketer?->name ?? 'غير مباعة' ,
           $price, 
           $unit->unitSale?->discount ?? 0,
           $unit->unitSale?->total_price ?? $price,
           $amount_paid , 
           $remaining ,
           $unit->unitSale?->contract_number,
           $unit->unitSale?->commission,
           $unit->unitSale?->buyer->iban ?? '-',
           $unit->unitSale?->sale_date,
           
        ];
    }

    public function headings(): array
    {
        return [
            'اسم الشركة',
            'اسم المشروع',
            'نموذج الوحدة',
            'نوع الوحدة',
            'المساحة' , 
            'الطابق' , 
            'عدد الغرف' , 
            'الزون' ,
            'اسم المشتري' ,
            'المسوق الرئيسي' ,
            'قيمة الوحدة' ,
            'قيمة الخصم' ,
            'السعر النهائي' ,
            'المبلغ المدفوع' , 
            'المبلغ المتبقي' , 
            'رقم العقد',
            'قيمة العمولة' ,
            'رقم حساب العميل' ,
            'تاريخ البيع',
        ];
    }

    public function styles(Worksheet $sheet)
    {
         // تنسيق النص للصفوف A:J
         $sheet->getStyle('A:S')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:S1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
         // تنسيق الأعمدة الخاصة بالأرقام (سعر، المدفوع، المتبقي) بصيغة عملة
         $sheet->getStyle('K2:O' . $sheet->getHighestRow())
             ->getNumberFormat()
             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
  
    }

    
}


