<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class Unit extends Model
{
    use HasFactory;

    protected $fillable  = [
        'unit_number',
        'type',
        'project_id',
        'area',
        'floor',
        'zone',
        'rooms',
        'price',
        'status',
    ];

        
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function buyer() {
        return $this->belongsTo(Customer::class, 'buyer_id');
    }
    public function unitSale() {
        return $this->hasOne(UnitSale::class);
    }

}

