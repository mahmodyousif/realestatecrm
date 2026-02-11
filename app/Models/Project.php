<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'company_id',
        'floors',
        'total_units',
        'aria_range',
        'location',
        'status',
        'notes',
    ];


    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function unitSales()
    {
        return $this->hasManyThrough(
            UnitSale::class, // الجدول النهائي
            Unit::class,     // الجدول الوسيط
            'project_id',    // مفتاح Foreign Key في Units يشير للمشروع
            'unit_id',       // مفتاح Foreign Key في UnitSales يشير للوحدة
            'id',            // المفتاح الأساسي في Projects
            'id'             // المفتاح الأساسي في Units
        );
    }
}
