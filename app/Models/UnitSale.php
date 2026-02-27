<?php

namespace App\Models;

use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class UnitSale extends Model {
    protected $table = 'unit_sales';

    protected $fillable = [
        'unit_id',
        'buyer_id',
        'marketer_id',
        'investor_id',
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
    
    public function buyer(){
        return $this->belongsTo(Customer::class , 'buyer_id') ;
    }

    public function investor(){
        return $this->belongsTo(Customer::class , 'investor_id') ;
    }

    public function marketer() {
        return $this->belongsTo(Customer::class, 'marketer_id') ;
    }

    public function payments(){
        return $this->hasMany(Payment::class);
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

      public function getStatusAttribute()
      {
          return $this->remaining > 0 ? 'reserved' : 'sold';
      }
}