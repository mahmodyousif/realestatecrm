<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
                    'الاسم', 'رقم الهاتف', 'البريد الإلكتروني', 'نوع العميل', 'تاريخ التسجيل',
                    'عدد الوحدات المشتراة', 'المبلغ المستحق', 'المبلغ المدفوع', 'المبلغ المتبقي'
                ];
            }

            public function styles(Worksheet $sheet)
            {
                $sheet->getStyle('A1:I1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); // نص أبيض
                $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0074D9'); // خلفية زرقاء
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
                        'السعر' => $pur->total_price,
                        'المبلغ المدفوع' => $paid,
                        'المبلغ المتبقي' => $pur->total_price - $paid,
                        'تاريخ البيع' => $pur->sale_date,
                    ];
                }
                return $rows;
            }

            public function headings(): array
            {
                return ['#', 'اسم الوحدة', 'السعر', 'المبلغ المدفوع', 'المبلغ المتبقي', 'تاريخ البيع'];
            }

            public function styles(Worksheet $sheet)
            {
                $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); // نص أبيض
                $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0074D9'); // خلفية زرقاء
            }
        };

        return $sheets;
    }
}
