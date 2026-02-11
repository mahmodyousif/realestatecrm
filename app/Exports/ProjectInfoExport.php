<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


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
            new class($this->project) implements FromArray, WithTitle, WithStyles , ShouldAutoSize {
                protected $project;
                public function __construct($project) { $this->project = $project; }

                public function array(): array
                {
                    $units = $this->project->units;

                    // الوحدات المباعة والمحجوزة
                    $soldOrReservedUnits = $units->whereIn('status', ['sold', 'reserved']);
                
                    // حساب القيم المالية فقط للوحدات المباعة + المحجوزة
                    $totalUnitsPrice = $soldOrReservedUnits->sum('price'); // إجمالي أسعار المباعة + المحجوزة
                    $totalPaid = $soldOrReservedUnits->sum(function($unit) {
                        return $unit->unitSale ? $unit->unitSale->payments->sum('amount_paid') : 0;
                    });
                    $totalRemaining = $totalUnitsPrice - $totalPaid;
                
                    // عد الوحدات حسب الحالة
                    $soldUnitsCount = $units->where('status', 'sold')->count();
                    $reservedUnitsCount = $units->where('status', 'reserved')->count();
                    $availableUnitsCount = $units->where('status', 'available')->count();
                
                
                    return [

                        [
                            'اسم المشروع' , 
                            'عدد الطوابق' ,
                            'عدد الوحدات' ,
                            'نطاق المساحات' ,
                            'الموقع' ,
                            'الحالة' ,
                            'اجمالي اسعار الوحدات المباعة او المحجوزة' ,
                            'الايردات المحققة' ,
                            'المبالغ المتبقية' ,
                            'عدد الوحدات المباعة' , 
                            'عدد الوحدات المحجوزة' ,
                            'عدد الوحدات المتاحة' ,

                        ] ,

                        [
                            $this->project->name,
                            $this->project->floors,
                            $this->project->total_units,
                            $this->project->aria_range, 
                            $this->project->location ,
                            $this->project->status === 'planning' ? 'تحت الإنشاء' : ($this->project->status === 'active' ? 'نشط' : 'مكتمل') ,
                            number_format($totalUnitsPrice) . ' ريال' , 
                            number_format($totalPaid) . ' ريال' , 
                            number_format($totalRemaining) . ' ريال' ,
                            $soldUnitsCount . ' وحدة' , 
                            $reservedUnitsCount . ' وحدة' , 
                            $availableUnitsCount . ' وحدة'
                        ] ,
                    ] ;
                }

                public function title(): string { return 'تفاصيل المشروع'; }

                public function styles(Worksheet $sheet)
                {
                    $sheet->getStyle('A:M')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A1:M1')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
               
            },

            new class($this->project) implements FromArray, WithTitle, WithHeadings, WithStyles , ShouldAutoSize {
                protected $project;
                public function __construct($project) { $this->project = $project; }

                public function array(): array
                {
                    return $this->project->units->map(function($unit) {
                        $paid = $unit->unitSale ? $unit->unitSale->payments->sum('amount_paid') : 0;
                        $remaining = $unit->price - $paid;
                        $buyer = $unit->unitSale ? $unit->unitSale->buyer->name : '-'; 
                        $marketer = $unit->unitSale ? $unit->unitSale->marketer?->name : '-'; 
                        return [
                            $unit->type,
                            $unit->unit_number,
                            $unit->area,
                            $unit->floor,
                            $unit->rooms,
                            number_format($unit->price) . ' ريال',
                            $buyer ,
                            $marketer , 
                            number_format($paid) . ' ريال',     
                            number_format($remaining) . ' ريال',
                            $unit->status === 'sold' ? 'مباعة' : ($unit->status === 'reserved' ? 'محجوزة' : 'متاحة'),
                        ];
                    })->toArray();
                }

                public function headings(): array
                {
                    return ['نوع الوحدة','رقم الوحدة','المساحة','الطابق','عدد الغرف','السعر' ,'المشتري','المسوق' ,'المدفوع' , 'المتبقي' ,'الحالة'];
                }

                public function title(): string { return 'الوحدات'; }

                public function styles(Worksheet $sheet)
                {
                    $sheet->getStyle('A:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A1:K1')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
            }
        ];
    }
  
}
