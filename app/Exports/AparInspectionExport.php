<?php

namespace App\Exports;

use App\Models\Apar;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AparInspectionExport implements WithMultipleSheets
{
    public function __construct(private array $codes) {}

    public function sheets(): array
    {
        $apars = Apar::whereIn('code', $this->codes)
            ->with(['inspections.inspector'])
            ->orderBy('code')
            ->get();

        return $apars->map(fn($apar) => new AparInspectionSheet($apar))->toArray();
    }
}
