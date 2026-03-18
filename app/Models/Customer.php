<?php

namespace App\Models;

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
        'iban',
        'notes',
    ];

    // ─────────────────────────────────────────────────────────────
    // العلاقة الأساسية: العميل/المستثمر ← unit_sale_customers
    // (buyer و investor يدخلان النظام عبر هذا الجدول)
    // ─────────────────────────────────────────────────────────────

    /**
     * سجلات الشراكة الخاصة بهذا العميل (buyer أو investor)
     * الجدول الوسيط: unit_sale_customers
     */
    public function saleCustomers()
    {
        return $this->hasMany(UnitSaleCustomer::class, 'customer_id');
    }

    /**
     * عمليات البيع التي اشترك فيها هذا العميل (buyer أو investor)
     * عبر unit_sale_customers
     */
    public function purchases()
    {
        return $this->hasManyThrough(
            UnitSale::class,          // الوجهة النهائية
            UnitSaleCustomer::class,  // الجدول الوسيط
            'customer_id',            // FK في unit_sale_customers → customer
            'id',                     // PK في unit_sales
            'id',                     // PK في customers
            'unit_sale_id'            // FK في unit_sale_customers → unit_sale
        );
    }

    /**
     * الدفعات الخاصة بهذا العميل (buyer أو investor)
     * المسار: customers → unit_sale_customers → payments
     */
    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,           // الوجهة النهائية
            UnitSaleCustomer::class,  // الجدول الوسيط
            'customer_id',            // FK في unit_sale_customers → customer
            'unit_sale_customer_id',  // FK في payments → unit_sale_customer
            'id',                     // PK في customers
            'id'                      // PK في unit_sale_customers
        );
    }

    // ─────────────────────────────────────────────────────────────
    // علاقة المسوّق (marketer_id موجود مباشرة في unit_sales)
    // ─────────────────────────────────────────────────────────────

    /**
     * عمليات البيع التي سوّقها هذا العميل (marketer)
     */
    public function marketedSales()
    {
        return $this->hasMany(UnitSale::class, 'marketer_id');
    }

    /**
     * الدفعات المرتبطة بالمبيعات التي سوّقها هذا المسوّق
     * المسار: unit_sales (marketer_id) → unit_sale_customers → payments
     * ملاحظة: هذه تجلب كل دفعات وحدات المسوّق وليس دفعاته الشخصية
     */
    public function marketedPayments()
    {
        return Payment::whereHas('unitSaleCustomer.unitSale', function ($q) {
            $q->where('marketer_id', $this->id);
        });
    }

    
}