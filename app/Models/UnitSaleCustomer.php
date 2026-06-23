<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitSaleCustomer extends Model
{
    protected $table = 'unit_sale_customers';

    protected $fillable = [
        'unit_sale_id',
        'customer_id',
        'contract_number',
        'marketer_id', 
        'commission_amount', 
        'share_percentage',
        'share_amount',
        'sale_date'
    ];

    public function unitSale()
    {
        return $this->belongsTo(UnitSale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function marketer()
    {
        return $this->belongsTo(Customer::class, 'marketer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'unit_sale_customer_id');
    }
}