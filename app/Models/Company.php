<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory; 
    protected $fillable = [
        'name',
   ];

   public function projects(){
    return $this->hasMany(Project::class);
   }

   public function units(){
    return $this->hasManyThrough(Unit::class, Project::class);
   }

   public function unitSales(){
    return $this->hasManyThrough(
        UnitSale::class , 
        Unit::class , 
        'project_id' , 
        'unit_id', 
        'id', 
        'id'
    );
   }

   
    public function soldUnitsCount() {
        return $this->units()->where('status', 'sold')->count();
    }

    public function reservedUnitsCount() {
        return $this->units()->where('status', 'reserved')->count();
    }

    public function availableUnitsCount() {
        return $this->units()->where('status', 'available')->count();
    }

}
