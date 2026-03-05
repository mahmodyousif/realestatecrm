<?php

namespace App\Imports;

use App\Models\Unit;
use App\Models\Project;
use App\Models\Customer;
use App\Models\UnitSale;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class SoldUnitImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithValidation,
    SkipsOnFailure
{
    use Importable, SkipsFailures;

    public int $addedCount = 0;
     public array $warningMessages = [
        'duplicate_units' => [],
        'missing_projects' => [],
        'missing_units' => [],
        'price_mismatch' => [],
        'contract_isExisting' => [],
    ];


    public function __construct()
    {
        HeadingRowFormatter::extend('custom', function ($value) {
            return match (trim($value)) {
                'اسم المشروع', 'المشروع'        => 'project',
                'نموذج الوحدة'                  => 'unit_number',
                'نوع الوحدة'                    => 'type',
                'الطابق'                         => 'floor',
                'اسم المشتري'                   => 'buyer',
                'المسوق الرئيسي'                => 'marketer',
                'المستثمر'                      => 'investor',
                'قيمة الوحدة'                   => 'unit_price',
                'قيمة الخصم'                    => 'discount',
                'السعر النهائي'                 => 'total_price',
                'رقم العقد'                     => 'contract_number',
                'تاريخ العقد'                   => 'sale_date',
                'طريقة الدفع'                   => 'payment_method',
                'قيمة العمولة'                  => 'commission',
                default                         => $value,
            };
        });

        HeadingRowFormatter::default('custom');
    }

    public function rules(): array
    {
        return [
            'unit_number'     => 'required',
            'type'            => 'required',
            'floor'            => 'required',
            'project'         => 'required',
            'contract_number' => 'required',
            'sale_date'       => 'required|date',
            'payment_method'  => 'required',
            'unit_price'      => 'required|numeric|min:0',
        ];
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {

            //التحقق من وجود المشروع قبل محاولة ربط الوحدة به
            $projectName = trim($row['project']);
            $project = Project::where('name', trim($row['project']))->first();
            if (!$project) {
               $this->warningMessages['missing_projects'][] = [
                'unit_number' => $row['unit_number'],
                'project'     => $projectName,
            ];
                return null;
            }

            // جلب الوحدة والتحقق من وجوجها قبل تسجيل البيع    
            $unit = Unit::where('unit_number', trim($row['unit_number']))
                ->where('type', trim($row['type'] ?? ''))
                ->where('floor', trim($row['floor'] ?? ''))
                ->where('project_id', $project->id)
                ->first();

            if (!$unit) {
                $this->warningMessages['missing_units'][] = [
                    'unit_number' => $row['unit_number'],
                    'project'       => $projectName,
                ];
                return null;
            }

            if ($unit->status === 'sold' || $unit->unitSale) {
                $this->warningMessages['duplicate_units'][] = [
                    'unit_number' => $row['unit_number'],
                    'project'     => $projectName,
                ];
                return null;
            }

            $unitPriceInSystem = $unit->price;
            $priceInExcel = (float) $row['unit_price'];
            if ($unitPriceInSystem != $priceInExcel) {
                $this->warningMessages['price_mismatch'][] = [
                    'unit_number' => $row['unit_number'],
                    'project'     => $projectName,
                    'price_in_system' => $unitPriceInSystem,
                    'price_in_excel' => $priceInExcel,
                ];
                return null;
            }
                /** 3️⃣ منع تكرار رقم العقد */
            if (UnitSale::where('contract_number', trim($row['contract_number']))->exists()) {
                $this->warningMessages['contract_isExisting'][] = [
                    'unit_number' => $row['unit_number'],
                    'project'     => $projectName,
                ];
                return null;
            }

            /** 4️⃣ العملاء (case-insensitive) */
            $findCustomer = fn ($name, $type = null) =>
                $name
                    ? Customer::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
                        ->when($type, fn ($q) => $q->where('type', $type))
                        ->first()?->id
                    : null;
                    
            $buyerId    = $findCustomer($row['buyer'] ?? null, 'buyer');
            $marketerId = $findCustomer($row['marketer'] ?? null);
            $investorId = $findCustomer($row['investor'] ?? null, 'investor');

            /** 5️⃣ الأسعار */
            $unitPrice = (float) $row['unit_price'];
            $discount  = isset($row['discount']) ? (float) $row['discount'] : 0;

            $totalPrice = $unitPrice - $discount;
                
                $saleDate = Carbon::parse(trim($row['sale_date']))->format('Y-m-d');
                
                $paymentMethods = [
                'كاش'         => 'cash',
                'تقسيط'       => 'installment',
                'رهن عقاري'   => 'mortgage',
                'تحويل بنكي'  => 'transfer',
            ];

             $paymentMethod = $paymentMethods[trim($row['payment_method'])] ?? null;
            if (!$paymentMethod) {
                $this->warningMessages['invalid_payment_method'][] = [
                    'unit_number' => $row['unit_number'],
                    'project'     => $projectName,
                    'value'       => $row['payment_method'] ?? null,
                ];
                $paymentMethod = 'cash'; // قيمة افتراضية
            }
            /** 6️⃣ إنشاء عملية البيع */

            
            $sale = UnitSale::create([
                'unit_id'         => $unit->id,
                'buyer_id'        => $buyerId,
                'marketer_id'     => $marketerId,
                'investor_id'     => $investorId ?? null,
                'sale_date'       => $saleDate,
                'payment_method'  => $paymentMethod,
                'unit_price'      => $unitPrice,
                'discount'        => $discount,
                'total_price'     => $totalPrice,
                'contract_number' => trim($row['contract_number']),
                'commission'      => isset($row['commission']) ? (float) $row['commission'] : 0,
            ]);

            /** 7️⃣ دفعة واحدة كاملة */
            $sale->payments()->create([
                'amount_paid'    => $totalPrice,
                'payment_date'   => $saleDate,
                'payment_method' => $paymentMethod,
                'reference_number' => 0,
                'notes'          => 'دفعة كاملة (استيراد)',
            ]);

            /** 8️⃣ تحديث حالة الوحدة */
            $unit->update(['status' => 'sold']);

            $this->addedCount++;

            return $sale;
        });
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}