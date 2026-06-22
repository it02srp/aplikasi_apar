<?php

namespace App\Exports;

use App\Models\Apar;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AparDataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $all = Apar::orderBy('code')->get();

        return [
            new AparAllSheet($all),
            new AparAlertSheet($all),
            new AparMaintenanceSheet($all),
            new AparSummarySheet($all),
        ];
    }
}
