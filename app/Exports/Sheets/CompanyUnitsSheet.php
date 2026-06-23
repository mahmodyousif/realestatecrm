<?php

namespace App\Exports\Sheets;

use App\Models\Unit;
use App\Models\UnitSale;
use App\Models\UnitSaleCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyUnitsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles , ShouldAutoSize
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    
     public function collection()
{
    return UnitSaleCustomer::with([
        'unitSale.unit.project',
        'marketer',
        'customer',
        'payments',
    ])
    ->whereHas('unitSale.unit.project', fn($q) =>
        $q->where('company_id', $this->companyId)
    )
    ->get()
    ->map(function ($saleCustomer) {

        $paid = $saleCustomer->payments->sum('amount_paid');

        return [

            $saleCustomer->unitSale->unit->project->name ?? '-',

            $saleCustomer->unitSale->unit->unit_number ?? '-',

            $saleCustomer->unitSale->unit->type ?? '-',

            $saleCustomer->unitSale->unit->area ?? '-',

            $saleCustomer->unitSale->unit->floor ?? '-',

            $saleCustomer->unitSale->unit->rooms ?? '-',

            $saleCustomer->unitSale->unit->zone ?? '-',

            $saleCustomer->customer->name ?? '-',

            $saleCustomer->marketer->name ?? '-',

            $saleCustomer->share_percentage . '%',

            $saleCustomer->share_amount ?? 0,

            $paid,

            ($saleCustomer->share_amount ?? 0) - $paid,

            $saleCustomer->contract_number ?? '-',

            $saleCustomer->commission_amount ?? 0,

            $saleCustomer->customer->iban ?? '-',

            $saleCustomer->sale_date ?? '-',
        ];
    });
}
    


    public function headings(): array
    {
        return [
            'المشروع',
            'نموذج الوحدة',
            'نوع الوحدة',
            'المساحة',
            'الطابق',
            'عدد الغرف',
            'الزون',
            'اسم المشتري',
            'المسوق الرئيسي',
            'الحصص (%)',
            'قيمة الوحدة',
            'المبلغ المدفوع',
            'المبلغ المتبقي',
            'رقم العقد',
            'قيمة العمولة',
            'رقم حساب العميل',
            'تاريخ البيع',
        ];
    }

    public function styles(Worksheet $sheet)
    {
         // تنسيق النص للصفوف A:J
         $sheet->getStyle('A:Q')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

         // تنسيق الرأس
         $sheet->getStyle('A1:Q1')->applyFromArray([
             'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
             'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
             'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
         ]);
 
         // تنسيق الأعمدة الخاصة بالأرقام (سعر، المدفوع، المتبقي) بصيغة عملة
         $sheet->getStyle('K2:M' . $sheet->getHighestRow())
             ->getNumberFormat()
             ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

               // تلوين المبلغ المتبقي حسب القيمة
    $highestRow = $sheet->getHighestRow();
    for ($row = 2; $row <= $highestRow; $row++) {
        $remaining = $sheet->getCell('M' . $row)->getValue();
        if ($remaining == 0) {
            $sheet->getStyle('M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4CAF50');

        }
        elseif ($remaining > 0 && $remaining <= 10000) {
            // متبقي صغير، أصفر
            $sheet->getStyle('M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        } elseif ($remaining > 10000) {
            // متبقي كبير، أحمر
            $sheet->getStyle('M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
        }}
     
    }

    public function title(): string
    {
        return 'الوحدات';
    }
}
