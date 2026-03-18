<?php

namespace App\Imports;

use App\Models\Unit;
use App\Models\Project;
use App\Models\Customer;
use App\Models\UnitSale;
use App\Models\UnitSaleCustomer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\Importable;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SoldUnitImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    SkipsOnFailure
{
    use Importable, SkipsFailures;

    public int $addedCount = 0;
    public array $warningMessages = [
        'duplicate_units'        => [],
        'missing_projects'       => [],
        'missing_units'          => [],
        'price_mismatch'         => [],   // السعر في الإكسل ≠ السعر في النظام
        'overpaid'               => [],   // إجمالي الدفعات يتجاوز السعر الكلي
        'contract_isExisting'    => [],
        'invalid_payment_method' => [],
        'share_not_100'          => [],
        'invalid_customer_type'  => [],
        'missing_customer'       => [],   // ✅ جديد: عميل غير موجود في النظام
    ];

    public function __construct()
    {
        HeadingRowFormatter::extend('custom', function ($value) {
            return match (trim($value)) {
                'اسم المشروع', 'المشروع'  => 'project',
                'نموذج الوحدة'            => 'unit_number',
                'نوع الوحدة'              => 'type',
                'الطابق'                  => 'floor',
                'اسم المشتري'             => 'buyer',
                'نوع المشتري'             => 'buyer_type',
                'رقم العقد'               => 'contract_number',
                // ✅ إصلاح: كل قيمة في سطر مستقل بدلاً من الفاصلة
                'الحصة'                   => 'share_percentage',
                'الحصة %'                 => 'share_percentage',
                'المبلغ المدفوع'          => 'amount_paid',
                'المسوق الرئيسي'          => 'marketer',
                'قيمة الوحدة'             => 'unit_price',
                'قيمة الخصم'              => 'discount',
                'تاريخ العقد'             => 'sale_date',
                'طريقة الدفع'             => 'payment_method',
                'قيمة العمولة'            => 'commission',
                'السعر النهائي'           => 'total_price',
                default                   => $value,
            };
        });

        HeadingRowFormatter::default('custom');
    }

    public function collection(Collection $rows)
    {
        // ✅ إصلاح: فلترة الصفوف الفارغة قبل التجميع
        $rows = $rows->filter(fn($row) => !empty(trim($row['project'] ?? '')) && !empty(trim($row['unit_number'] ?? '')));

        $grouped = $rows->groupBy(function ($row) {
            return implode('||', [
                trim($row['project']     ?? ''),
                trim($row['unit_number'] ?? ''),
                trim($row['type']        ?? ''),
                trim($row['floor']       ?? ''),
            ]);
        });

        foreach ($grouped as $key => $unitRows) {
            $this->processUnitRows($unitRows);
        }
    }

    private function processUnitRows(Collection $unitRows): void
    {
        $firstRow    = $unitRows->first();
        $projectName = trim($firstRow['project']     ?? '');
        $unitNumber  = trim($firstRow['unit_number'] ?? '');

        // ── التحقق من المشروع ──
        $project = Project::where('name', $projectName)->first();
        if (!$project) {
            $this->warningMessages['missing_projects'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
            ];
            return;
        }

        // ── التحقق من الوحدة ──
        $unit = Unit::where('unit_number', $unitNumber)
            ->where('type',       trim($firstRow['type']  ?? ''))
            ->where('floor',      trim($firstRow['floor'] ?? ''))
            ->where('project_id', $project->id)
            ->first();

        if (!$unit) {
            $this->warningMessages['missing_units'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
            ];
            return;
        }

        // ── التحقق من أن الوحدة لم تُباع مسبقاً ──
        if ($unit->status === 'sold' || $unit->unitSale()->exists()) {
            $this->warningMessages['duplicate_units'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
            ];
            return;
        }

        // ── التحقق من تطابق السعر (سعر الإكسل مقابل سعر النظام فقط) ──
        $unitPrice = (float) ($firstRow['unit_price'] ?? 0);
        if ($unit->price != $unitPrice) {
            $this->warningMessages['price_mismatch'][] = [
                'unit_number'     => $unitNumber,
                'project'         => $projectName,
                'price_in_system' => $unit->price,
                'price_in_excel'  => $unitPrice,
            ];
            return;
        }

        // ── التحقق من تكرار أرقام العقود ──
        foreach ($unitRows as $row) {
            $contractNumber = trim($row['contract_number'] ?? '');
            if ($contractNumber && UnitSaleCustomer::where('contract_number', $contractNumber)->exists()) {
                $this->warningMessages['contract_isExisting'][] = [
                    'unit_number'     => $unitNumber,
                    'project'         => $projectName,
                    'contract_number' => $contractNumber,
                ];
                return;
            }
        }

        // ── بناء مصفوفة الشركاء ──
        $customers = [];
        foreach ($unitRows as $row) {
            $buyerName  = trim($row['buyer']      ?? '');
            $buyerType  = trim($row['buyer_type'] ?? 'customer');
            $share      = (float) ($row['share_percentage'] ?? 0);
            $amountPaid = (float) ($row['amount_paid']      ?? 0);
            $contract   = trim($row['contract_number']      ?? '');

            // ── التحقق من نوع المشتري ──
            if (!in_array($buyerType, ['customer', 'investor'])) {
                $this->warningMessages['invalid_customer_type'][] = [
                    'unit_number' => $unitNumber,
                    'project'     => $projectName,
                    'buyer'       => $buyerName,
                    'type_given'  => $buyerType,
                ];
                return;
            }

            // ── البحث عن العميل ──
            $customer = Customer::whereRaw('LOWER(name) = ?', [mb_strtolower($buyerName)])
                ->when($buyerType === 'investor', fn($q) => $q->where('type', 'investor'))
                ->when($buyerType === 'customer', fn($q) => $q->where('type', 'buyer'))
                ->first();

            // ✅ إصلاح: إيقاف المعالجة إن لم يُوجد العميل بدلاً من إدخال null
            if (!$customer) {
                $this->warningMessages['missing_customer'][] = [
                    'unit_number' => $unitNumber,
                    'project'     => $projectName,
                    'buyer'       => $buyerName,
                    'buyer_type'  => $buyerType,
                ];
                return;
            }

            $customers[] = [
                'id'              => $customer->id,
                'type'            => $buyerType,
                'share'           => $share,
                'amount_paid'     => $amountPaid,
                'contract_number' => $contract,
            ];
        }

        // ── التحقق من أن مجموع الحصص = 100 ──
        $totalShare = array_sum(array_column($customers, 'share'));
        if (abs($totalShare - 100) > 0.01) {
            $this->warningMessages['share_not_100'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
                'total_share' => $totalShare,
            ];
            return;
        }

        // ── الأسعار ──
        $discount   = (float) ($firstRow['discount']   ?? 0);
        $totalPrice = $unitPrice - $discount;
        $commission = (float) ($firstRow['commission'] ?? 0);
        $saleDate   = $this->transformDate($firstRow['sale_date'] ?? null);

        // ── طريقة الدفع ──
        $paymentMethods = [
            'كاش'        => 'cash',
            'تقسيط'      => 'installment',
            'رهن عقاري'  => 'mortgage',
            'تحويل بنكي' => 'transfer',
        ];
        $paymentMethod = $paymentMethods[trim($firstRow['payment_method'] ?? '')] ?? null;

        // ✅ إصلاح: إيقاف المعالجة عند طريقة دفع غير صالحة بدلاً من افتراض 'cash'
        if (!$paymentMethod) {
            $this->warningMessages['invalid_payment_method'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
                'value'       => $firstRow['payment_method'] ?? null,
            ];
            return;
        }

        // ── المسوق ──
        $marketerName = trim($firstRow['marketer'] ?? '');
        $marketerId   = $marketerName
            ? Customer::whereRaw('LOWER(name) = ?', [mb_strtolower($marketerName)])->first()?->id
            : null;

        // ── التحقق من إجمالي المدفوعات ──
        // ✅ إصلاح: استخدام مصفوفة منفصلة 'overpaid' بدلاً من 'price_mismatch'
        $totalPaid = array_sum(array_column($customers, 'amount_paid'));
        if ($totalPaid > $totalPrice) {
            $this->warningMessages['overpaid'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
                'total_paid'  => $totalPaid,
                'total_price' => $totalPrice,
            ];
            return;
        }

        // ── إنشاء البيع داخل transaction ──
        DB::transaction(function () use (
            $unit, $customers, $unitPrice, $discount, $totalPrice,
            $commission, $saleDate, $paymentMethod, $marketerId, $totalPaid
        ) {
            $sale = UnitSale::create([
                'unit_id'        => $unit->id,
                'marketer_id'    => $marketerId,
                'sale_date'      => $saleDate,
                'payment_method' => $paymentMethod,
                'unit_price'     => $unitPrice,
                'discount'       => $discount,
                'total_price'    => $totalPrice,
                'commission'     => $commission,
            ]);

            foreach ($customers as $customerData) {
                $shareAmount = $totalPrice * ($customerData['share'] / 100);

                $custRecord = UnitSaleCustomer::create([
                    'unit_sale_id'     => $sale->id,
                    'customer_id'      => $customerData['id'],
                    'contract_number'  => $customerData['contract_number'],
                    'share_percentage' => $customerData['share'],
                    'share_amount'     => $shareAmount,
                ]);

                if (!empty($customerData['amount_paid']) && $customerData['amount_paid'] > 0) {
                    $custRecord->payments()->create([
                        'amount_paid'      => $customerData['amount_paid'],
                        'payment_date'     => $saleDate,
                        'payment_method'   => $paymentMethod,
                        'reference_number' => 0,
                        'notes'            => 'دفعة شريك (استيراد)',
                    ]);
                }
            }

            if ($totalPaid == $totalPrice) {
                $unit->status = 'sold';
            } elseif ($totalPaid > 0) {
                $unit->status = 'partially_paid';
            } else {
                $unit->status = 'reserved';
            }
            $unit->save();

            $this->addedCount++;
        });
    }

    private function transformDate($value): string
    {
        if (empty($value)) return now()->format('Y-m-d');

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            return Carbon::parse(trim($value))->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}