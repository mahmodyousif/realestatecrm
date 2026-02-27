<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements WithMultipleSheets
{
    protected $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    public function sheets(): array
    {
        $sheets = [];

        // ورقة المعلومات الشخصية
        $sheets[] = new class($this->customer) implements FromArray, WithHeadings, ShouldAutoSize, WithStyles {
            protected $customer;

            public function __construct($customer)
            {
                $this->customer = $customer;
            }

            public function array(): array
            {
                $totalPaid = $this->customer->purchases->sum(fn($p) => $p->payments->sum('amount_paid'));
                $totalPrice = $this->customer->purchases->sum('total_price');
                $remaining = $totalPrice - $totalPaid;

                return [[
                    'الاسم' => $this->customer->name,
                    'رقم الهاتف' => $this->customer->phone,
                    'البريد الإلكتروني' => $this->customer->email,
                    'رقم الهوية' => $this->customer->id_number ?? '-',
                    'رقم الحساب البنكي' => $this->customer->iban ?? '-',
                    'نوع العميل' => $this->customer->type == 'buyer' ? 'مشتري' : ($this->customer->type == 'marketer' ? 'مسوق' : 'مستثمر'),
                    'تاريخ التسجيل' => $this->customer->created_at->format('Y-m-d'),
                    'عدد الوحدات المشتراة' => $this->customer->purchases->count(),
                    'المبلغ المستحق' => $totalPrice,
                    'المبلغ المدفوع' => $totalPaid,
                    'المبلغ المتبقي' => $remaining,
                ]];
            }

            public function headings(): array
            {
                return [
                    'الاسم',
                    'رقم الهاتف',
                    'البريد الإلكتروني',
                    'رقم الهوية',
                    'رقم الحساب البنكي',
                    'نوع العميل',
                    'تاريخ التسجيل',
                    'عدد الوحدات المشتراة',
                    'المبلغ المستحق', 
                    'المبلغ المدفوع',
                    'المبلغ المتبقي'
                ];
            }

            public function styles(Worksheet $sheet)
            {
            // تنسيق الرأس
            $sheet->getStyle('A1:K1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // محاذاة الأعمدة
            $sheet->getStyle('A:K')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $highestRow = $sheet->getHighestRow();

            // تنسيق الأرقام كأرقام مع فاصلة آلاف
            for ($row = 2; $row <= $highestRow; $row++) {
                $sheet->getStyle('I' . $row . ':K' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // تلوين عمود المتبقي
                $remaining = $sheet->getCell('K' . $row)->getValue();
                $price = $sheet->getCell('I' . $row)->getValue();

                if ($remaining == 0) {
                    $color = 'FF4CAF50'; // أخضر
                } elseif ($remaining <= ($price / 2)) {
                    $color = 'FFFFEB3B'; // أصفر
                } else {
                    $color = 'FFFF0000'; // أحمر
                }

                $sheet->getStyle('K' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color);
                }
            }
        };

        // ورقة عمليات الشراء
        $sheets[] = new class($this->customer) implements FromArray, WithHeadings, ShouldAutoSize, WithStyles {
            protected $customer;

            public function __construct($customer)
            {
                $this->customer = $customer;
            }

            public function array(): array
            {
                $rows = [];
                $i = 1;
                foreach ($this->customer->purchases as $pur) {
                    $paid = $pur->payments->sum('amount_paid');
                    $rows[] = [
                        '#' => $i++,
                        'اسم الوحدة' => $pur->unit->type . " " . $pur->unit->unit_number,
                        'قيمة الوحدة' => $pur->unit_price,
                        'قيمة الخصم' => $pur->discount,
                        'السعر النهائي' => $pur->total_price,
                        'المبلغ المدفوع' => $paid,
                        'المبلغ المتبقي' => $pur->total_price - $paid,
                        'تاريخ البيع' => $pur->sale_date,
                    ];
                }
                return $rows;
            }

            public function headings(): array
            {
                return ['#', 'اسم الوحدة', 'قيمة الوحدة','قيمة الخصم' , 'السعر النهائي','المبلغ المدفوع', 'المبلغ المتبقي', 'تاريخ البيع'];
            }

            public function styles(Worksheet $sheet)
            {
            // تنسيق الرأس
            $sheet->getStyle('A1:H1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // محاذاة الأعمدة
            $sheet->getStyle('A:H')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $highestRow = $sheet->getHighestRow();

            // تنسيق الأرقام كأرقام مع فاصلة آلاف
            for ($row = 2; $row <= $highestRow; $row++) {
                $sheet->getStyle('C' . $row . ':G' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // تلوين عمود المتبقي
                $remaining = $sheet->getCell('G' . $row)->getValue();
                $price = $sheet->getCell('F' . $row)->getValue();

                if ($remaining == 0) {
                    $color = 'FF4CAF50'; // أخضر
                } elseif ($remaining <= ($price / 2)) {
                    $color = 'FFFFEB3B'; // أصفر
                } else {
                    $color = 'FFFF0000'; // أحمر
                }

                $sheet->getStyle('G' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color);
                }
            }
        };

        return $sheets;
    }
}
