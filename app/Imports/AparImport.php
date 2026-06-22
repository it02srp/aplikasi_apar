<?php

namespace App\Imports;

use App\Models\Apar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AparImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $skipped  = 0;
    public array $errors = [];

    private array $validUnits      = ['kg', 'liter'];
    private array $validConditions = ['Good', 'Needs Attention', 'Replace'];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $line = $i + 2; // +2 karena row 1 = heading

            try {
                $data = $this->mapRow($row);

                // Validasi kolom wajib
                $missing = [];
                if (empty($data['location']))       $missing[] = 'Lokasi';
                if (empty($data['type']))            $missing[] = 'Jenis';
                if (empty($data['capacity']))        $missing[] = 'Kapasitas';
                if (empty($data['capacity_unit']))   $missing[] = 'Satuan';
                if (empty($data['manufacture_date'])) $missing[] = 'Tgl Produksi';
                if (empty($data['expiry_date']))     $missing[] = 'Tgl Kadaluarsa';
                if (empty($data['condition']))       $missing[] = 'Kondisi';

                if ($missing) {
                    $this->errors[] = "Baris {$line}: kolom wajib kosong — " . implode(', ', $missing);
                    $this->skipped++;
                    continue;
                }

                // Validasi nilai enum
                if (!in_array($data['capacity_unit'], $this->validUnits)) {
                    $this->errors[] = "Baris {$line}: Satuan '{$data['capacity_unit']}' tidak valid (kg/liter).";
                    $this->skipped++;
                    continue;
                }
                if (!in_array($data['condition'], $this->validConditions)) {
                    $this->errors[] = "Baris {$line}: Kondisi '{$data['condition']}' tidak valid.";
                    $this->skipped++;
                    continue;
                }

                // Auto-generate code jika kosong
                if (empty($data['code'])) {
                    $data['code'] = Apar::generateCode();
                }

                // Skip jika kode sudah ada
                if (Apar::where('code', $data['code'])->exists()) {
                    $this->errors[] = "Baris {$line}: Kode '{$data['code']}' sudah ada, dilewati.";
                    $this->skipped++;
                    continue;
                }

                Apar::create($data);
                $this->imported++;

            } catch (\Throwable $e) {
                $this->errors[] = "Baris {$line}: " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    private function mapRow($row): array
    {
        return [
            'code'                 => trim($row['kode_apar'] ?? ''),
            'location'             => trim($row['lokasi'] ?? ''),
            'building'             => trim($row['gedung'] ?? '') ?: null,
            'floor'                => trim($row['lantai'] ?? '') ?: null,
            'room'                 => trim($row['ruangan'] ?? '') ?: null,
            'type'                 => ucwords(strtolower(trim($row['jenis'] ?? ''))),
            'capacity'             => is_numeric($row['kapasitas'] ?? '') ? (float)$row['kapasitas'] : null,
            'capacity_unit'        => strtolower(trim($row['satuan'] ?? '')),
            'manufacture_date'     => $this->parseDate($row['tanggal_produksi'] ?? null),
            'expiry_date'          => $this->parseDate($row['tanggal_kadaluarsa'] ?? null),
            'last_inspection_date' => $this->parseDate($row['inspeksi_terakhir'] ?? null),
            'next_inspection_date' => $this->parseDate($row['inspeksi_berikutnya'] ?? null),
            'condition'            => trim($row['kondisi'] ?? 'Good'),
            'responsible_person'   => trim($row['penanggung_jawab'] ?? '') ?: null,
            'notes'                => trim($row['catatan'] ?? '') ?: null,
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel date serial number
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float)$value))
                    ->format('Y-m-d');
            } catch (\Throwable) {}
        }

        $value = trim((string)$value);

        // DD/MM/YYYY
        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Throwable) {}

        // YYYY-MM-DD or other Carbon-parseable
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {}

        return null;
    }
}
