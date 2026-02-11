<?php

namespace App\Exports;

use App\Exports\Sheets\CompanyStatsSheet;
use App\Exports\Sheets\CompanyProjectsSheet;
use App\Exports\Sheets\CompanyUnitsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CompanyReportExport implements WithMultipleSheets
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function sheets(): array
    {
        return [
            new CompanyStatsSheet($this->companyId),
            new CompanyProjectsSheet($this->companyId),
            new CompanyUnitsSheet($this->companyId),
        ];
    }
}
