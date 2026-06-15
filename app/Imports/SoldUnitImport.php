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
        'price_mismatch'         => [],
        'overpaid'               => [],
        'contract_isExisting'    => [],
        'invalid_payment_method' => [],
        'share_not_100'          => [],
        'invalid_customer_type'  => [],
        'missing_customer'       => [],
        'missing_marketer'       => [],
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
                'الحصة', 'الحصة %'        => 'share_percentage',
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
        $rows = $rows->filter(fn($row) =>
            !empty(trim($row['project'] ?? '')) &&
            !empty(trim($row['unit_number'] ?? ''))
        );

        $grouped = $rows->groupBy(function ($row) {
            return implode('||', [
                trim($row['project'] ?? ''),
                trim($row['unit_number'] ?? ''),
                trim($row['type'] ?? ''),
                trim($row['floor'] ?? ''),
            ]);
        });

        foreach ($grouped as $unitRows) {
            $this->processUnitRows($unitRows);
        }
    }

    private function processUnitRows(Collection $unitRows): void
    {
        $firstRow = $unitRows->first();

        $projectName = trim($firstRow['project'] ?? '');
        $unitNumber  = trim($firstRow['unit_number'] ?? '');

        // ── المشروع ──
        $project = Project::where('name', $projectName)->first();
        if (!$project) {
            $this->warningMessages['missing_projects'][] = compact('unitNumber', 'projectName');
            return;
        }

        // ── الوحدة ──
        $unit = Unit::where('unit_number', $unitNumber)
            ->where('type', trim($firstRow['type'] ?? ''))
            ->where('floor', trim($firstRow['floor'] ?? ''))
            ->where('project_id', $project->id)
            ->first();

        if (!$unit) {
            $this->warningMessages['missing_units'][] = compact('unitNumber', 'projectName');
            return;
        }

        if ($unit->status === 'sold' || $unit->unitSale()->exists()) {
            $this->warningMessages['duplicate_units'][] = compact('unitNumber', 'projectName');
            return;
        }

        // ── السعر ──
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

        // ── العقود ──
        foreach ($unitRows as $row) {
            $contractNumber = trim($row['contract_number'] ?? '');

            if ($contractNumber &&
                UnitSaleCustomer::where('contract_number', $contractNumber)->exists()
            ) {
                $this->warningMessages['contract_isExisting'][] = [
                    'unit_number'     => $unitNumber,
                    'project'         => $projectName,
                    'contract_number' => $contractNumber,
                ];
                return;
            }
        }

        // ── العملاء ──
        $customers = [];

        foreach ($unitRows as $row) {

            $buyerName = trim($row['buyer'] ?? '');
            $buyerTypeRaw = trim($row['buyer_type'] ?? '');

            // ✔ تحويل عربي → إنجليزي
            $buyerTypeMap = [
                'مشتري'   => 'buyer',
                'مسوق'     => 'marketer',
                'مستثمر'   => 'investor',
            ];

            $buyerType = $buyerTypeMap[$buyerTypeRaw] ?? strtolower($buyerTypeRaw);

            $allowedTypes = ['buyer', 'marketer', 'investor'];

            if (!in_array($buyerType, $allowedTypes)) {
                $this->warningMessages['invalid_customer_type'][] = [
                    'unit_number' => $unitNumber,
                    'project'     => $projectName,
                    'buyer'       => $buyerName,
                    'type_given'  => $buyerTypeRaw,
                ];
                return;
            }

            // ── البحث عن العميل ──
            $customer = Customer::whereRaw('LOWER(name) = ?', [mb_strtolower($buyerName)])
                ->when($buyerType === 'buyer', fn($q) => $q->where('type', 'buyer'))
                ->when($buyerType === 'investor', fn($q) => $q->where('type', 'investor'))
                ->when($buyerType === 'marketer', fn($q) => $q->where('type', 'marketer'))
                ->first();

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
                'share'           => (float) ($row['share_percentage'] ?? 0),
                'amount_paid'     => (float) ($row['amount_paid'] ?? 0),
                'contract_number' => trim($row['contract_number'] ?? ''),
            ];
        }

        // ── مجموع الحصص ──
        $totalShare = array_sum(array_column($customers, 'share'));

        if (abs($totalShare - 100) > 0.01) {
            $this->warningMessages['share_not_100'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
                'total_share' => $totalShare,
            ];
            return;
        }

        // ── الأسعار النهائية ──
        $discount   = (float) ($firstRow['discount'] ?? 0);
        $totalPrice = $unitPrice - $discount;
        $commission = (float) ($firstRow['commission'] ?? 0);
        $saleDate   = $this->transformDate($firstRow['sale_date'] ?? null);

        $marketerName = trim($firstRow['marketer'] ?? '');
        $marketer = null;

        if ($marketerName !== '') {
            $marketer = Customer::whereRaw('LOWER(name) = ?', [mb_strtolower($marketerName)])
                ->where('type', 'marketer')
                ->first();

            if (!$marketer) {
                $this->warningMessages['missing_marketer'][] = [
                    'unit_number'   => $unitNumber,
                    'project'       => $projectName,
                    'marketer'      => $marketerName,
                ];
                return;
            }
        }

        // ── الدفع ──
        $paymentMethods = [
            'كاش'        => 'cash',
            'تقسيط'      => 'installment',
            'رهن عقاري'  => 'mortgage',
            'تحويل بنكي' => 'transfer',
        ];

        $paymentMethod = $paymentMethods[trim($firstRow['payment_method'] ?? '')] ?? null;

        if (!$paymentMethod) {
            $this->warningMessages['invalid_payment_method'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
            ];
            return;
        }

        // ── الدفع الكلي ──
        $totalPaid = array_sum(array_column($customers, 'amount_paid'));
            
        if ($totalPaid > $totalPrice) {
            $this->warningMessages['overpaid'][] = [
                'unit_number' => $unitNumber,
                'project'     => $projectName,
            ];
            return;
        }

        // ── حفظ البيانات ──
        DB::transaction(function () use (
            $unit, $customers, $unitPrice, $discount,
            $totalPrice, $commission, $saleDate,
            $paymentMethod, $totalPaid, $marketer
        ) {

            $sale = UnitSale::create([
                'unit_id'        => $unit->id,
                'marketer_id'    => $marketer?->id,
                'sale_date'      => $saleDate,
                'payment_method' => $paymentMethod,
                'unit_price'     => $unitPrice,
                'discount'       => $discount,
                'total_price'    => $totalPrice,
                'commission'     => $commission,
            ]);

            foreach ($customers as $c) {

                $shareAmount = $totalPrice * ($c['share'] / 100);

                $record = UnitSaleCustomer::create([
                    'unit_sale_id'     => $sale->id,
                    'customer_id'      => $c['id'],
                    'contract_number'  => $c['contract_number'],
                    'share_percentage' => $c['share'],
                    'share_amount'     => $shareAmount,
                ]);

                if ($c['amount_paid'] > 0) {
                    $record->payments()->create([
                        'amount_paid'    => $c['amount_paid'],
                        'payment_date'   => $saleDate,
                        'payment_method' => $paymentMethod,
                        'reference_number' => 0,
                        'notes'          => 'دفعة استيراد',
                    ]);
                }
            }

           if($totalPaid >= $totalPrice){
            $unit->status = 'sold';
            } elseif($totalPaid > 0 && $totalPaid < 25000){
                $unit->status = 'reserved';
            } elseif ($totalPaid >= 25000 && $totalPaid < $totalPrice) {
                $unit->status = 'partially_paid';
            } else {
                $unit->status = 'available';
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
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}