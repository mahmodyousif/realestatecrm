<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection , WithHeadings , WithStyles , ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() 
    {
        return Customer::select('type', 'name', 'id_card', 'phone', 'email', 'address')
        ->get()
        ->map(function ($customer) {

            $customer->type = match ($customer->type) {
                'marketer' => 'مسوق',
                'investor' => 'مستثمر',
                'buyer' => 'مشتري',
                default => $customer->type,
            };  

             $customer->id_card = !empty($customer->id_card) 
                ? $customer->id_card 
                : '-';


            return $customer;
        });
        }

      public function headings(): array
    {
        return [
            'النوع',
            'الاسم',
            'الهوية او السجل التجاري',
            'الهاتف',
            'البريد الإلكتروني',
            'العنوان',
        ];
    }

     public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2196F3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}
