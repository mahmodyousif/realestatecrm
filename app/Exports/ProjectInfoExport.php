<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectInfoExport implements WithMultipleSheets
{
    protected $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

    public function sheets(): array
    {
        return [

            // =========================
            // 📊 Sheet 1: تفاصيل المشروع
            // =========================
            new class($this->project) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize {

                protected $project;

                public function __construct($project)
                {
                    $this->project = $project;
                }

                public function array(): array
                {
                    $units = $this->project->units;

                    $soldOrReservedUnits = $units->whereIn('status', ['sold', 'reserved']);

                    $totalUnitsPrice = $soldOrReservedUnits->sum(function ($unit) {
                        $sale = $unit->unitSale;
                        if (!$sale) return 0;

                        return ($sale->total_price ?? $unit->price)
                            - ($sale->discount ?? 0);
                    });

                    $totalPaid = $soldOrReservedUnits->sum(function ($unit) {
                        $sale = $unit->unitSale;
                        if (!$sale) return 0;

                        return $sale->saleCustomers->sum(function ($sc) {
                            return $sc->payments->sum('amount_paid');
                        });
                    });

                    $totalRemaining = $totalUnitsPrice - $totalPaid;

                    $soldUnitsCount = $units->where('status', 'sold')->count();
                    $reservedUnitsCount = $units->where('status', 'reserved')->count();
                    $partiallyPaidUnitsCount = $units->where('status', 'partially_paid')->count();
                    $availableUnitsCount = $units->where('status', 'available')->count();

                    return [
                        [
                            'اسم المشروع',
                            'عدد الطوابق',
                            'عدد الوحدات',
                            'نطاق المساحات',
                            'الموقع',
                            'الحالة',
                            'إجمالي قيمة المبيعات',
                            'الإيرادات المحققة',
                            'المبالغ المتبقية',
                            'المباعة',
                            'المحجوزة',
                            'المتاحة',
                            'مدفوعة جزئياً',
                        ],

                        [
                            $this->project->name,
                            $this->project->floors,
                            $this->project->total_units,
                            $this->project->aria_range,
                            $this->project->location,
                            $this->project->status === 'planning' ? 'تحت الإنشاء' :
                                ($this->project->status === 'active' ? 'نشط' : 'مكتمل'),

                            number_format($totalUnitsPrice) . ' ريال',
                            number_format($totalPaid) . ' ريال',
                            number_format($totalRemaining) . ' ريال',

                            $soldUnitsCount . ' وحدة',
                            $reservedUnitsCount . ' وحدة',
                            $availableUnitsCount . ' وحدة',
                            $partiallyPaidUnitsCount . ' وحدة',
                        ],
                    ];
                }

                public function title(): string
                {
                    return 'تفاصيل المشروع';
                }

                public function styles($sheet)
                {
                    $sheet->getStyle('A:M')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle('A1:M1')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
                    ]);
                }
            },

            // =========================
            // 🏠 Sheet 2: الوحدات (بعد التعديل)
            // =========================
            new class($this->project) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithHeadings {

                protected $project;

                public function __construct($project)
                {
                    $this->project = $project;
                }

                public function array(): array
                {
                    return $this->project->units->map(function ($unit) {

                        $sale = $unit->unitSale;

                        if (!$sale) {
                            return [
                                $unit->type,
                                $unit->unit_number,
                                $unit->area,
                                $unit->floor,
                                $unit->rooms,
                                number_format($unit->price) . ' ريال',
                                '-',
                                '-',
                                0,
                                0,
                                0,
                                '-',
                                'متاحة',
                            ];
                        }

                        $paid = $sale->saleCustomers->sum(function ($sc) {
                            return $sc->payments->sum('amount_paid');
                        });

                        $remaining = ($sale->total_price ?? 0) - $paid;

                        $buyers = $sale->saleCustomers
                            ->pluck('customer.name')
                            ->unique()
                            ->implode(', ');

                        $marketers = $sale->saleCustomers
                            ->pluck('marketer.name')
                            ->unique()
                            ->implode(', ');

                        return [
                            $unit->type,
                            $unit->unit_number,
                            $unit->area,
                            $unit->floor,
                            $unit->rooms,
                            number_format($unit->price) . ' ريال',

                            $buyers,
                            $marketers,

                            number_format($sale->total_price ?? 0) . ' ريال',
                            number_format($sale->discount ?? 0) . ' ريال',
                            number_format($remaining) . ' ريال',

                            $sale->saleCustomers->pluck('contract_number')->implode(', '),

                            $unit->status === 'sold'
                                ? 'مباعة'
                                : ($unit->status === 'reserved'
                                    ? 'محجوزة'
                                    : ($unit->status === 'partially_paid'
                                        ? 'مدفوعة جزئياً'
                                        : 'متاحة')),
                        ];
                    })->toArray();
                }

                public function headings(): array
                {
                    return [
                        'نوع الوحدة',
                        'رقم الوحدة',
                        'المساحة',
                        'الطابق',
                        'عدد الغرف',
                        'السعر',
                        'المشترون',
                        'المسوقون',
                        'إجمالي البيع',
                        'الخصم',
                        'المتبقي',
                        'أرقام العقود',
                        'الحالة',
                    ];
                }

                public function title(): string
                {
                    return 'الوحدات';
                }

                public function styles($sheet)
                {
                    $sheet->getStyle('A:M')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle('A1:M1')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
                    ]);
                }
            }
        ];
    }
}