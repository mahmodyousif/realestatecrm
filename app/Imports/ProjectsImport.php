<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ProjectsImport implements ToModel, WithHeadingRow, WithChunkReading
{
    use Importable;

    public $addedCount = 0;

    protected $existingProjects;
    protected $companies;

    public function __construct()
    {
        // جلب المشاريع الموجودة مسبقًا مرة واحدة
        $this->existingProjects = Project::pluck('name')->toArray();

        // جلب الشركات الموجودة: الاسم => ID
        $this->companies = Company::pluck('id','name')->toArray();


        HeadingRowFormatter::extend('custom', function ($value) {
            return match (trim($value)) {
                'الشركة'            => 'company',
                'اسم المشروع'       => 'name',
                'قيمة المشروع' => 'price',
                'عدد الطوابق'       => 'floors',
                'إجمالي الوحدات'    => 'total_units',
                'نطاق المساحات'     => 'aria_range',
                'الموقع'            => 'location',
                'الحالة'            => 'status',
                default              => $value,
            };
        });
        HeadingRowFormatter::default('custom');

    }

    public function model(array $row)
    {
        // تنظيف البيانات
        $projectName = trim($row['name']);
        $companyNameFromExcel = trim($row['company']);

        // تجاهل المشاريع الموجودة مسبقًا
        if(in_array($projectName, $this->existingProjects)) {
            return null;
        }

        // جلب ID الشركة أو إنشاؤها إذا غير موجودة
        if(isset($this->companies[$companyNameFromExcel])) {
            $companyId = $this->companies[$companyNameFromExcel];
        } else {
            $company = Company::create(['name' => $companyNameFromExcel]);
            $companyId = $company->id;

            // إضافة الشركة الجديدة للمصفوفة لتجنب إنشاءها عدة مرات
            $this->companies[$companyNameFromExcel] = $companyId;
        }

        $this->addedCount++;
        $status = null ; 
        if($row['status'] == 'نشط') {
            $status = 'active' ;
        } elseif($row['status'] == 'تحت الإنشاء') {
            $status = 'planning' ; 
        } elseif($row['status'] == 'مكتمل') {
            $status = 'completed' ;
        }
        // إنشاء المشروع الجديد
        return new Project([
            'company_id' => $companyId,
            'name' => $projectName,
            'price' => $row['price'],
            'floors' => $row['floors'],
            'total_units' => $row['total_units'],
            'aria_range' => $row['aria_range'],
            'location' => $row['location'],
            'status' => $status,
        ]);
    }

    // تقسيم الملف إلى chunks لتقليل استهلاك الذاكرة
    public function chunkSize(): int
    {
        return 1000; // معالجة 1000 صف في كل مرة
    }
}
