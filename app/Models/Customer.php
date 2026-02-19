<?php

namespace App\Models;

use App\Models\UnitSale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'id_card',
        'phone',
        'email',
        'address',
        'notes',
    ]; 

  
    public function purchases() {
        return $this->hasMany(UnitSale::class, 'buyer_id');
    }
    public function investor() {
        return $this->hasMany(UnitSale::class, 'investor_id');
    }

    public function marketedSales() {
        return $this->hasMany(UnitSale::class, 'marketer_id');
    }

    public function unit(){
        return $this->hasManyThrough(
            UnitSale::class , 
            Customer::class
        ) ;
    }


    public function payments()
    {
    return $this->hasManyThrough(
        Payment::class,   // الجدول النهائي اللي بدك توصله
        UnitSale::class,  // الجدول الوسيط
        'buyer_id',       // المفتاح في UnitSale اللي يشير للـ Customer
        'unit_sale_id',   // المفتاح في Payment اللي يشير للـ UnitSale
        'id',             // المفتاح المحلي في Customer
        'id'              // المفتاح المحلي في UnitSale
    );
}

public function sellers()
{
    return $this->hasManyThrough(
        Payment::class,   // الجدول النهائي اللي بدك توصله
        UnitSale::class,  // الجدول الوسيط
        'marketer_id',       // المفتاح في UnitSale اللي يشير للـ Customer
        'unit_sale_id',   // المفتاح في Payment اللي يشير للـ UnitSale
        'id',             // المفتاح المحلي في Customer
        'id'              // المفتاح المحلي في UnitSale
    );
}
}
