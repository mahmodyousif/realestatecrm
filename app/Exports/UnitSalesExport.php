<?php

namespace App\Exports;

use App\Models\UnitSale;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnitSalesExport implements FromCollection , WithMapping , WithHeadings , WithStyles , ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return UnitSale::with(['unit' , 'buyer' , 'marketer' ,'payments'])->get();
    }
    private int $counter = 0;

    public function map($unit): array
    {
        $amount_paid = $unit->payments->sum('amount_paid');
        $price = $unit->total_price;
        $remaining = $price - $amount_paid;
       
        return [
            ++$this->counter,
            $unit->unit->project->company->name ?? 'بدون شركة',
            $unit->unit->project->name ?? 'بدون مشروع',
            $unit->unit->unit_number,
            $unit->unit->type,
            $unit->unit->area,
            $unit->unit->floor,
            $unit->unit->rooms,
            $unit->unit->zone,
            $unit->buyer?->name ?? 'غير مباعة',
            $unit->marketer?->name ?? 'تم البيع عبر الشركة',
            $price,
            $amount_paid,
            $remaining ?? 0 ,
            $unit->commission ?? 0,
            $unit->contract_number,
            $unit->sale_date,
            $unit->buyer->iban ?? '-',
            $unit->created_at?->format('Y-m-d')
        ];
    }

    public function headings(): array
    {
        return [
            'الرقم التسلسلي' ,
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
            'المبلغ المدفوع' , 
            'المبلغ المتبقي' , 
            'قيمة العمولة' ,
            'رقم العقد' ,
            'تاريخ العقد',
            'رقم حساب العميل',
            'تاريخ تسجيل البيع',
        ];
    }

    public function styles(Worksheet $sheet)
    {
         // تنسيق النص للصفوف A:J
         $sheet->getStyle('A:R')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:R1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
         // تنسيق الأعمدة الخاصة بالأرقام (سعر، المدفوع، المتبقي) بصيغة عملة
         $sheet->getStyle('L2:N' . $sheet->getHighestRow())
             ->getNumberFormat()
             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

               // تلوين المبلغ المتبقي حسب القيمة
    $highestRow = $sheet->getHighestRow();
    for ($row = 2; $row <= $highestRow; $row++) {
        $remaining = $sheet->getCell('N' . $row)->getValue();
        if ($remaining == 0) {
            $sheet->getStyle('N' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4CAF50');

        }
        elseif ($remaining > 0 && $remaining <= 10000) {
            // متبقي صغير، أصفر
            $sheet->getStyle('N' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        } elseif ($remaining > 10000) {
            // متبقي كبير، أحمر
            $sheet->getStyle('N' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
        }}
     
    }

    
}


