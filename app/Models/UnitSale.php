<?php

namespace App\Models;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\UnitSaleCustomer;
use Illuminate\Database\Eloquent\Model;

class UnitSale extends Model {
    protected $table = 'unit_sales';

    protected $fillable = [
        'unit_id',
        'marketer_id',
        'sale_date', 
        'payment_method',
        'unit_price',
        'discount',
        'total_price',
        'contract_number',
        'commission',
    ]; 

    // العلاقات
    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    public function marketer() {
        return $this->belongsTo(Customer::class, 'marketer_id');
    }

    public function saleCustomers(){
        return $this->hasMany(UnitSaleCustomer::class);
    }

    public function customers(){
        return $this->hasManyThrough(Customer::class, UnitSaleCustomer::class, 'unit_sale_id', 'id', 'id', 'customer_id');
    }

    public function payments(){
        return $this->hasManyThrough(
            Payment::class,
            UnitSaleCustomer::class,
            'unit_sale_id',
            'unit_sale_customer_id',
            'id',
            'id'
        );
    }

    // العلاقات القديمة للتوافق (قد تكون فارغة في النظام الجديد)
    public function buyer(){
        return $this->belongsTo(Customer::class, 'buyer_id');
    }

    public function investor(){
        return $this->belongsTo(Customer::class, 'investor_id');
    }

    // حساب المدفوع
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount_paid');
    }

    // المتبقي ديناميكي
    public function getRemainingAttribute()
    {
        return $this->total_price - $this->total_paid;
    }

    // الحالة بناءً على المدفوعات
    public function getStatusAttribute()
    {
        $totalPaid = $this->total_paid;
        if ($totalPaid == 0) {
            return 'reserved';
        } elseif ($totalPaid >= $this->total_price) {
            return 'sold';
        } else {
            return 'partially_paid';
        }
    }

    // الحصول على أسماء العملاء كقائمة مفصولة بفواصل
    public function getCustomerNamesAttribute()
    {
        return $this->customers->pluck('name')->join(', ');
    }

    // الحصول على أرقام العقود كقائمة مفصولة بفواصل
    public function getContractNumbersAttribute()
    {
        return $this->saleCustomers->pluck('contract_number')->join(', ');
    }

    // الحصول على إجمالي الحصص
    public function getTotalSharesAttribute()
    {
        return $this->saleCustomers->sum('share_percentage');
    }
}