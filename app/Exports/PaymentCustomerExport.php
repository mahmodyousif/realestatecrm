<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Maatwebsite\Excel\Concerns\FromCollection; 
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;



class PaymentCustomerExport implements FromCollection, WithHeadings , ShouldAutoSize ,WithStyles , WithEvents
{
    protected $unitSaleId;
    protected $customerName;
    protected $unitName ;
    public function __construct($unitSaleId , $customerName ,$unitName)
    {
        $this->unitSaleId = $unitSaleId;
        $this->customerName = $customerName ;
        $this->unitName = $unitName ;
    }

    /**
     * البيانات المصدّرة
     */
    public function collection()
    {
        return Payment::where('unit_sale_id', $this->unitSaleId)
            ->get()
            ->map(function ($payment) {
                return [
                    'قيمة الدفعة'     => $payment->amount_paid,
                    'تاريخ الاستلام' => $payment->payment_date,
                    'طريقة الدفع'    => $payment->payment_method === 'cash' ? 'نقدي' : 'شيك',
                    'الرقم المرجعي'  => $payment->reference_number ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'قيمة الدفعة',
            'تاريخ الاستلام',
            'طريقة الدفع',
            'الرقم المرجعي' ,

        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1=> [ // الصف الأول (Header)
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'], 
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2196F3'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            'A:I' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // إدراج اسم العميل فوق الهدينج
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 1); // أضف صف جديد في الأعلى
                $sheet->setCellValue(
                    'A1', "اسم العميل: {$this->customerName} - الوحدة المباعة:  {$this->unitName}"
                );
                
                $sheet->mergeCells('A1:D1');

                // تنسيق الاسم
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12 ,
                        'color' => ['argb' => 'FFFFFFFF'], 
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '244062'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
    }

}
