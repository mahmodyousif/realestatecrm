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
        return Unit::with(['project' , 'unitSale.buyer' , 'unitSale.payments'])->get();
    }

    public function map($unit): array
    {
        $amount_paid =  $unit->unitSale?->payments->sum('amount_paid') ; 
        $price = $unit->price ; 
        $remaining = $price - $amount_paid ; 
        return [
           $unit->unit_number , 
           $unit->type , 
           $unit->project->name , 
           $unit->area , 
           $unit->floor ,
           $unit->rooms , 
           $unit->zone , 
           $unit->unitSale?->buyer?->name ?? 'غير مباعة' ,
           $price, 
           $amount_paid , 
           $remaining ,
        ];
    }

    public function headings(): array
    {
        return [
            'رقم الوحدة',
            'نوع الوحدة',
            'اسم المشروع',
            'المساحة' , 
            'الطابق' , 
            'عدد الغرف' , 
            'الزون' ,
            'اسم المشتري' ,
            'سعر الوحدة' ,
            'المبلغ المدفوع' , 
            'المبلغ المتبقي' , 
        ];
    }

    public function styles(Worksheet $sheet)
    {
         // تنسيق النص للصفوف A:J
         $sheet->getStyle('A:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:K1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
         // تنسيق الأعمدة الخاصة بالأرقام (سعر، المدفوع، المتبقي) بصيغة عملة
         $sheet->getStyle('H2:J' . $sheet->getHighestRow())
             ->getNumberFormat()
             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

               // تلوين المبلغ المتبقي حسب القيمة
    $highestRow = $sheet->getHighestRow();
    for ($row = 2; $row <= $highestRow; $row++) {
        $remaining = $sheet->getCell('J' . $row)->getValue();

        if ($remaining > 0 && $remaining <= 10000) {
            // متبقي صغير، أصفر
            $sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        } elseif ($remaining > 10000) {
            // متبقي كبير، أحمر
            $sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
        }}
    }

    
}


