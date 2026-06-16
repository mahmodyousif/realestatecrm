<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class CustomerImport  implements ToModel, WithHeadingRow, WithChunkReading
{
    
     public $addedCount = 0;
     public $errors = [];
    
    public function __construct()
    {
    

        HeadingRowFormatter::extend('custom', function ($value) {
            return match (trim($value)) {
                'نوع العميل'            => 'type',
                'اسم العميل'       => 'name',
                'رقم الهوية او السجل التجاري'       => 'id_card',
                'الهاتف'    => 'phone',
                'البريد الإلكتروني'     => 'email',
                'العنوان'            => 'address',
                'رقم حساب العميل'            => 'iban',
                'ملاحظات'            => 'notes',
                default              => $value,
            };
        });
        HeadingRowFormatter::default('custom');
    }
    
    
    public function model(array $row)
    {

        $name  = trim($row['name'] ?? '');
        $phone = trim($row['phone'] ?? '');
        $type = trim($row['type'] ?? '');
        $key = $name . '_' . $phone . '_' . $type; // مفتاح فريد يجمع الاسم، الهاتف، والنوع

        
        if(empty($name)|| empty($type)) {
            return null; 
        }
        
        if($type === 'مسوق'){
            $type = 'marketer';
        } elseif($type=== 'مستثمر'){
            $type = 'investor' ; 
        } else {
            $type = 'buyer' ; 
        }

        $existingCustomer = Customer::where('name', $name)
            ->where('type', $type)
            ->exists();

            if($existingCustomer) {
                return null; // الصف لا يضاف
            }

        $id_card = isset($row['id_card']) ? trim($row['id_card']) : null;
    

        $this->addedCount++;

        return new Customer([
            'type' => $type, 
            'name' => $name, 
            'id_card' =>  $id_card,
            'phone' => $phone ?: null,
            'email' => isset($row['email']) ? trim($row['email']) : null,
            'address' => isset($row['address']) ? trim($row['address']) : null,
            'iban' => isset($row['iban']) ? trim($row['iban']) : null,
            'notes' => isset($row['notes']) ? trim($row['notes']) : null,
        ]);

    }
     public function chunkSize(): int
    {
        return 1000; // معالجة 1000 صف في كل مرة
    }
}
