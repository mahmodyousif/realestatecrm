<?php

namespace App\Exports\Sheets;

use App\Models\Company;
use App\Models\Payment;
use App\Models\UnitSale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyStatsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        $company = Company::withCount([
            'units as available_units' => fn($q) => $q->where('units.status', 'available'),
            'units as sold_units'      => fn($q) => $q->where('units.status', 'sold'),
            'units as reserved_units'  => fn($q) => $q->where('units.status', 'reserved'),
        ])
        ->withCount('projects')
        ->findOrFail($this->companyId);

        $totalSales = UnitSale::whereHas('unit.project', fn($q) =>
            $q->where('company_id', $this->companyId)
        )->sum('total_price');

        $paid = Payment::whereHas('unitSale.unit.project', fn($q) =>
            $q->where('company_id', $this->companyId)
        )->sum('amount_paid');

        // نعيد Collection صف واحد بالقيم
        return collect([[
            $company->name,
            $company->projects_count,
            $company->available_units,
            $company->sold_units,
            $company->reserved_units,
            $totalSales,
            $paid,
            $totalSales - $paid,
        ]]);
    }

    public function headings(): array
    {
        return [
            'اسم الشركة',
            'عدد المشاريع',
            'الوحدات المتاحة',
            'الوحدات المباعة',
            'الوحدات المحجوزة',
            'إجمالي مبيعات الوحدات',
            'المبلغ المدفوع',
            'المبلغ المتبقي',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // محاذاة النصوص في جميع الأعمدة
        $sheet->getStyle('A:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // تنسيق الرأس
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // تنسيق الأعمدة الرقمية (إجمالي، مدفوع، متبقي)
        $sheet->getStyle('F2:H' . $sheet->getHighestRow())
              ->getNumberFormat()
              ->setFormatCode('#,##0');

        // تلوين المبلغ المتبقي حسب القيمة
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $remaining = $sheet->getCell('H' . $row)->getValue();
            if ($remaining == 0) {
                $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4CAF50');
            } elseif ($remaining > 0 && $remaining <= 10000) {
                $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
            } elseif ($remaining > 10000) {
                $sheet->getStyle('H' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
            }
        }
    }

    public function title(): string
    {
        return 'الإحصائيات';
    }
}