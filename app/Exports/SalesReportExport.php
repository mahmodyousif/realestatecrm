<?php

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        $units = Unit::with(['project', 'unitSale.payments', 'unitSale.buyer'])
            ->where('status', '!=', 'available')
            ->get();

        $counter = 1;
        return $units->map(function($unit) use (&$counter) {
            $unitPrice = $unit->price ?? 0;
            $totalPaid = $unit->unitSale?->payments->sum('amount_paid') ?? 0;
            $remainingAmount = $unit->unitSale->total_price - $totalPaid;
            $buyerName = $unit->unitSale?->buyer?->name ?? '-';
            $marketerName = $unit->unitSale?->marketer?->name ?? '-';
            $saleDate = $unit->unitSale?->sale_date ?? '-';
            $status = $unit->status == 'sold' ? 'مباعة' : 'محجوزة';

            return [
                'الرقم التسلسلي' => $counter++, 
                'الشركة' => $unit->project?->company->name ?? '-',
                'المشروع' => $unit->project?->name ?? '-',
                'نموذج الوحدة' => $unit->unit_number,
                'نوع الوحدة' => $unit->type,
                'الطابق' => $unit->floor, 
                'الزون' => $unit->zone,
                'المساحة' => $unit->area,
                'قيمة الوحدة' => $unitPrice,
                'قيمة الخصم' => $unit->unitSale->discount ?? 0,
                'السعر النهائي' => $unit->unitSale?->total_price ?? $unitPrice,
                'المبلغ المدفوع' => $totalPaid,
                'المتبقي' => $remainingAmount,
                'المشتري' => $buyerName,
                'المسوق الرئيسي' => $marketerName, 
                'رقم العقد' => $unit->unitSale?->contract_number,
                'قيمة العمولة' => $unit->unitSale?->commission , 
                'تاريخ البيع' => $saleDate,
                'رقم حساب العميل' => $unit->unitSale?->buyer->iban ?? '-',
                'الحالة' => $status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'الرقم التسلسلي',
            'الشركة',
            'المشروع',
            'نموذج الوحدة',
            'نوع الوحدة',
            'الطابق',
            'الزون',
            'المساحة',
            'قيمة الوحدة',
            'قيمة الخصم',
            'السعر النهائي',
            'المبلغ المدفوع',
            'المتبقي',
            'المشتري',
            'المسوق الرئيسي',
            'رقم العقد',
            'قيمة العمولة' ,
            'تاريخ البيع',
            'رقم حساب العميل',
            'الحالة',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // تنسيق الرأس
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // محاذاة الأعمدة
        $sheet->getStyle('A:T')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $highestRow = $sheet->getHighestRow();

        // تنسيق الأرقام كأرقام مع فاصلة آلاف
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getStyle('I' . $row . ':M' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            // تلوين عمود المتبقي
            $remaining = $sheet->getCell('M' . $row)->getValue();
            $price = $sheet->getCell('K' . $row)->getValue();

            if ($remaining == 0) {
                $color = 'FF4CAF50'; // أخضر
            } elseif ($remaining <= ($price / 2)) {
                $color = 'FFFFEB3B'; // أصفر
            } else {
                $color = 'FFFF0000'; // أحمر
            }

            $sheet->getStyle('M' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($color);
        }
    }
}


