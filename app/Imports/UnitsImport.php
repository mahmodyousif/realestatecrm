<?php

namespace App\Imports;

use App\Models\Unit;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
class UnitsImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;


    public $addedCount = 0;   
    public array $warningMessages = [
        'duplicate_units' => [],
        'missing_projects' => [],
    ];
    

    public function __construct()
    {
            
        HeadingRowFormatter::extend('custom', function ($value) {
            return match (trim($value)) {
                'نموذج الوحدة'   => 'unit_number',
                'نوع الوحدة'   => 'type',
                'المشروع'      => 'project_id',
                'المساحة'      => 'area',
                'الطابق'       => 'floor',
                'الزون'        => 'zone',
                'عدد الغرف'    => 'rooms',
                'السعر'        => 'price',
                'الحالة'       => 'status',
                default        => $value,
            };
        });
    
        HeadingRowFormatter::default('custom');
    }
    
    public function rules(): array
    {
        return [
            'unit_number'  => 'required',
            'type'         => 'required',
            'project_id' => 'required',
            'area'         => 'required|numeric',
            'floor'        => 'required|numeric',
            'zone'         => 'required|numeric',
            'rooms'        => 'required|numeric',
            'price'        => 'required|numeric',
            'status'       => 'required',
        ];
    }
    
    public function model(array $row)
    {
 
        $projectName = trim($row['project_id']);
        $projectId = Project::where('name' , $projectName)->value('id') ;

        if(!$projectId){
            $this->warningMessages['missing_projects'][] = [
                'unit_number' => $row['unit_number'],
                'project'     => $projectName,
            ];
            return null ;
        }

        $existingUnit = Unit::where('unit_number', trim($row['unit_number']))
                        ->where('type', trim($row['type']))
                        ->where('project_id', $projectId)
                        ->where('floor', (int) $row['floor'])
                        ->where('zone', (int) $row['zone'])
                        ->where('rooms', (int) $row['rooms'])
   
                      ->exists();
        if ($existingUnit) {
            $this->warningMessages['duplicate_units'][] = [
                'unit_number' => $row['unit_number'],
                'type'        => $row['type'],
                'project'     => $projectName,
            ];
            return null; // تخطي هذا الصف وعدم إضافته
        }
        
        $this->addedCount++;
        $status = match($row['status']){
            'مباعة'=> 'sold',
            'محجوزة' => 'reserved' , 
            'جاهزة للبيع' => 'available' ,
            default => 'available' ,} ; 
      
        // إنشاء الوحدة الجديدة

        return new Unit([
            'unit_number' => trim((string) $row['unit_number']),
            'type'        => trim((string) $row['type']),
            'project_id'  => $projectId,
            'area'        => (float) $row['area'],
            'floor'       => (int) $row['floor'],
            'zone'        => (int) $row['zone'],
            'rooms'       => (int) $row['rooms'],
            'price'       => (float) $row['price'],
            'status'      => $status,
        ]);
    }

    // تقسيم الملف إلى chunks لتقليل استهلاك الذاكرة
    public function chunkSize(): int
    {
        return 1000; // معالجة 1000 صف في كل مرة
    }
}
