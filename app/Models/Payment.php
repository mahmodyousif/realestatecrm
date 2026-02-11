<?php

namespace App\Models;

use App\Models\UnitSale;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'unit_sale_id',    // المفتاح الأجنبي للربط مع عملية البيع
        'amount_paid',     // المبلغ المدفوع
        'payment_date',    // تاريخ الدفع
        'payment_method',  // طريقة الدفع
        'reference_number' ,
        'notes',           // ملاحظات إضافية
    ]; 

    // العلاقة مع عملية البيع
    public function unitSale()
    {
        return $this->belongsTo(UnitSale::class, 'unit_sale_id');
    }
}
