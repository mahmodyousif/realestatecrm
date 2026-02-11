<?php

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        $units = Unit::with(['project', 'unitSale.payments', 'unitSale.buyer'])
            ->where('status', '!=', 'available')
            ->get();

        return $units->map(function($unit) {
            $unitPrice = $unit->price ?? 0;
            $totalPaid = $unit->unitSale?->payments->sum('amount_paid') ?? 0;
            $remainingAmount = $unitPrice - $totalPaid;
            $buyerName = $unit->unitSale?->buyer?->name ?? '-';
            $saleDate = $unit->unitSale?->sale_date ?? '-';
            $status = $unit->status == 'sold' ? 'مباعة' : 'محجوزة';

            return [
                'رقم الوحدة' => $unit->unit_number,
                'المشروع' => $unit->project?->name ?? '-',
                'المساحة' => $unit->area,
                'السعر الكلي' => $unitPrice,
                'المبلغ المدفوع' => $totalPaid,
                'المتبقي' => $remainingAmount,
                'المشتري' => $buyerName,
                'تاريخ البيع' => $saleDate,
                'الحالة' => $status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'رقم الوحدة',
            'المشروع',
            'المساحة',
            'السعر الكلي',
            'المبلغ المدفوع',
            'المتبقي',
            'المشتري',
            'تاريخ البيع',
            'الحالة',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // تنسيق الرأس
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // محاذاة الأعمدة
        $sheet->getStyle('A:I')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $highestRow = $sheet->getHighestRow();

        // تنسيق الأرقام كأرقام مع فاصلة آلاف
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getStyle('D' . $row . ':F' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            // تلوين عمود المتبقي
            $remaining = $sheet->getCell('F' . $row)->getValue();
            $price = $sheet->getCell('D' . $row)->getValue();

            if ($remaining == 0) {
                $color = 'FF4CAF50'; // أخضر
            } elseif ($remaining <= ($price / 2)) {
                $color = 'FFFFEB3B'; // أصفر
            } else {
                $color = 'FFFF0000'; // أحمر
            }

            $sheet->getStyle('F' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($color);
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // رقم الوحدة
            'B' => 25, // المشروع
            'C' => 12, // المساحة
            'D' => 15, // السعر الكلي
            'E' => 18, // المبلغ المدفوع
            'F' => 15, // المتبقي
            'G' => 25, // المشتري
            'H' => 18, // تاريخ البيع
            'I' => 15, // الحالة
        ];
    }
}
