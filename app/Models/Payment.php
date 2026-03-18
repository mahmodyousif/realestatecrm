<?php

namespace App\Models;

use App\Models\UnitSale;
use App\Models\UnitSaleCustomer;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'unit_sale_customer_id',    // المفتاح الأجنبي للربط مع عميل عملية البيع
        'amount_paid',              // المبلغ المدفوع
        'payment_date',             // تاريخ الدفع
        'payment_method',           // طريقة الدفع
        'reference_number',
        'notes',                    // ملاحظات إضافية
    ]; 

    // العلاقة مع عميل عملية البيع
    public function unitSaleCustomer()
    {
        return $this->belongsTo(UnitSaleCustomer::class, 'unit_sale_customer_id');
    }

    // العلاقة مع عملية البيع (عبر UnitSaleCustomer)
    // public function unitSale()
    // {
    //     return $this->belongsTo(UnitSale::class, 'id');
    // }
}
