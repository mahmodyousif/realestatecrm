<?php

namespace App\Exports\Sheets;

use App\Models\Company;
use App\Models\UnitSale;
use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompanyStatsSheet implements FromArray, WithTitle 
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function array(): array
    {
        $company = Company::withCount([
            'units as available_units' => fn($q) => $q->where('units.status', 'available'),
            'units as sold_units'      => fn($q) => $q->where('units.status', 'sold'),
            'units as reserved_units'  => fn($q) => $q->where('units.status', 'reserved'),
        ])
        ->withCount('projects')
        ->findOrFail($this->companyId);
        $totalSales = UnitSale::whereHas('unit.project', fn($q) =>
            $q->where('company_id', $this->companyId)
        )->sum('total_price');

        $paid = Payment::whereHas('unitSale.unit.project', fn($q) =>
            $q->where('company_id', $this->companyId)
        )->sum('amount_paid');

        return [
            ['اسم الشركة', $company->name],
            ['عدد المشاريع', $company->projects_count],
            ['الوحدات المتاحة', $company->available_units],
            ['الوحدات المباعة', $company->sold_units],
            ['الوحدات المحجوزة', $company->reserved_units],
            ['إجمالي المبيعات', $totalSales],
            ['الإيرادات المحققة', $paid],
            ['المبالغ المتبقية', $totalSales - $paid],
        ];
    }

    public function title(): string
    {
        return 'الإحصائيات';
    }
}
