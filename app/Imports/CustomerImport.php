<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class CustomerImport  implements ToModel, WithHeadingRow, WithChunkReading
{
    
     public $addedCount = 0;

    protected $existingCustomers;

    public function __construct()
    {
        // جلب العملاء الموجودين مسبقًا مرة واحدة
        $this->existingCustomers = Customer::pluck('id_card')->toArray();


        HeadingRowFormatter::extend('custom', function ($value) {
            return match (trim($value)) {
                'نوع العميل'            => 'type',
                'اسم العميل'       => 'name',
                'رقم الهوية او السجل التجاري'       => 'id_card',
                'الهاتف'    => 'phone',
                'البريد الإلكتروني'     => 'email',
                'العنوان'            => 'address',
                'ملاحظات'            => 'notes',
                default              => $value,
            };
        });
        HeadingRowFormatter::default('custom');
    }   
    public function model(array $row)
    {
        if(in_array($row['id_card'], $this->existingCustomers)) {
            return null;
        }
        $this->addedCount++;

        $type = $row['type'] ; 
        if($type === 'مسوق'){
            $type = 'marketer';
        } elseif($type=== 'مستثمر'){
            $type = 'investor' ; 
        } else {
            $type = 'buyer' ; 
        }
        return new Customer([
            'type' => $type, 
            'name' => $row['name'],
            'id_card' => $row['id_card'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'address' => $row['address'],
            'notes' => $row['notes'],
        ]);
    }
     public function chunkSize(): int
    {
        return 1000; // معالجة 1000 صف في كل مرة
    }
}
