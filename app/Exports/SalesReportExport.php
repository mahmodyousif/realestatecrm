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
        $units = Unit::with([
            'project.company',
            'unitSale.saleCustomers.customer',
            'unitSale.saleCustomers.marketer',
            'unitSale.saleCustomers.payments',
        ])
        ->where('status', '!=', 'available')
        ->get();

        $counter = 1;

        return $units->map(function ($unit) use (&$counter) {

            $sale = $unit->unitSale;

            if (!$sale) {
                return [
                    'الرقم التسلسلي' => $counter++,
                    'الشركة' => $unit->project?->company->name ?? '-',
                    'المشروع' => $unit->project?->name ?? '-',
                    'نموذج الوحدة' => $unit->unit_number,
                    'نوع الوحدة' => $unit->type,
                    'الطابق' => $unit->floor,
                    'الزون' => $unit->zone,
                    'المساحة' => $unit->area,
                    'قيمة الوحدة' => $unit->price,
                    'قيمة الخصم' => 0,
                    'السعر النهائي' => $unit->price,
                    'المبلغ المدفوع' => 0,
                    'المتبقي' => $unit->price,
                    'المشتري' => '-',
                    'المسوق الرئيسي' => '-',
                    'رقم العقد' => '-',
                    'قيمة العمولة' => 0,
                    'تاريخ البيع' => '-',
                    'رقم حساب العميل' => '-',
                    'الحالة' => 'متاحة',
                ];
            }

            // =========================
            // الحسابات المالية الجديدة
            // =========================
            $totalPaid = $sale->saleCustomers->sum(function ($sc) {
                return $sc->payments->sum('amount_paid');
            });

            $remaining = ($sale->total_price ?? $unit->price) - $totalPaid;

            // =========================
            // البيانات المجمعة
            // =========================
            $buyers = $sale->saleCustomers
                ->pluck('customer.name')
                ->unique()
                ->implode(', ');

            $marketers = $sale->saleCustomers
                ->pluck('marketer.name')
                ->unique()
                ->implode(', ');

            $contracts = $sale->saleCustomers
                ->pluck('contract_number')
                ->implode(', ');

            $ibans = $sale->saleCustomers
                ->pluck('customer.iban')
                ->unique()
                ->implode(', ');

            return [
                'الرقم التسلسلي' => $counter++,
                'الشركة' => $unit->project?->company->name ?? '-',
                'المشروع' => $unit->project?->name ?? '-',
                'نموذج الوحدة' => $unit->unit_number,
                'نوع الوحدة' => $unit->type,
                'الطابق' => $unit->floor,
                'الزون' => $unit->zone,
                'المساحة' => $unit->area,

                'قيمة الوحدة' => $unit->price,
                'قيمة الخصم' => $sale->discount ?? 0,
                'السعر النهائي' => $sale->total_price ?? $unit->price,

                'المبلغ المدفوع' => $totalPaid,
                'المتبقي' => $remaining,

                'المشتري' => $buyers,
                'المسوق الرئيسي' => $marketers,

                'رقم العقد' => $contracts,
                'قيمة العمولة' => $sale->saleCustomers->sum('commission_amount'),

                'تاريخ البيع' => $sale->sale_date ?? '-',

                'رقم حساب العميل' => $ibans,

                'الحالة' => $unit->status == 'sold'
                    ? 'مباعة'
                    : 'محجوزة',
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
            'قيمة العمولة',
            'تاريخ البيع',
            'رقم حساب العميل',
            'الحالة',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:T')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2196F3']
            ],
        ]);

        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {

            $sheet->getStyle('I' . $row . ':M' . $row)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $remaining = $sheet->getCell('M' . $row)->getValue();
            $price = $sheet->getCell('K' . $row)->getValue();

            if ($remaining == 0) {
                $color = 'FF4CAF50';
            } elseif ($remaining <= ($price / 2)) {
                $color = 'FFFFEB3B';
            } else {
                $color = 'FFFF0000';
            }

            $sheet->getStyle('M' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($color);
        }
    }
}