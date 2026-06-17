<?php

namespace App\Exports;

use App\Models\Apar;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AparInspectionSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(private Apar $apar) {}

    public function title(): string
    {
        // Sheet name max 31 chars, no special chars
        $title = $this->apar->code;
        return mb_substr(preg_replace('/[\/\\\?\*\[\]:]/', '-', $title), 0, 31);
    }

    public function array(): array
    {
        $apar = $this->apar;
        $rows = [];

        // ── Header info APAR ──────────────────────────────────────────────
        $rows[] = ['PEMERIKSAAN BERKALA TABUNG PEMADAM KEBAKARAN'];
        $rows[] = ['PT Sinar Rimba Pasifik'];
        $rows[] = [];
        $rows[] = ['Kode APAR',        $apar->code,                    '', 'Jenis',          $apar->type];
        $rows[] = ['Lokasi',           $apar->location,                '', 'Kapasitas',      $apar->capacity . ' ' . $apar->capacity_unit];
        $rows[] = ['Gedung/Lantai',    collect([$apar->building, $apar->floor ? 'Lt.'.$apar->floor : null, $apar->room])->filter()->implode(' / ') ?: '-',
                   '', 'Tgl Produksi',   $apar->manufacture_date->format('d/m/Y')];
        $rows[] = ['Kondisi',          $apar->condition,               '', 'Tgl Kadaluarsa', $apar->expiry_date->format('d/m/Y')];
        $rows[] = ['Penanggung Jawab', $apar->responsible_person ?: '-', '', 'Inspeksi Terakhir',
                   $apar->last_inspection_date ? $apar->last_inspection_date->format('d/m/Y') : '-'];
        $rows[] = [];

        // ── Header tabel pemeriksaan ──────────────────────────────────────
        $rows[] = ['No', 'Tanggal & Jam', '1 Handle', '2 Selang', '3 Pin Kunci', '4 Indikator', '5 Tabung', '6 Masa Apar', 'Status', 'Petugas', 'Catatan'];

        // ── Data pemeriksaan ──────────────────────────────────────────────
        $inspections = $apar->inspections;

        if ($inspections->isEmpty()) {
            $rows[] = ['-', 'Belum ada data pemeriksaan', '', '', '', '', '', '', '', '', ''];
        } else {
            foreach ($inspections as $i => $ins) {
                $rows[] = [
                    $i + 1,
                    $ins->inspected_at->format('d/m/Y H:i'),
                    $ins->kondisi_handle    ?? '-',
                    $ins->kondisi_selang    ?? '-',
                    $ins->kondisi_pin_kunci ?? '-',
                    $ins->kondisi_indikator ?? '-',
                    $ins->kondisi_tabung    ?? '-',
                    $ins->kondisi_masa_apar ?? '-',
                    $ins->isAllOk() ? 'Semua OK' : 'Ada Masalah',
                    $ins->inspector?->username ?? '-',
                    $ins->notes ?? '',
                ];
            }
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 20,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 14,
            'I' => 14,
            'J' => 16,
            'K' => 30,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Judul ─────────────────────────────────────────────────
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF166534']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Info APAR (baris 4–8) ─────────────────────────────────
                // Label kolom A bold
                foreach ([4, 5, 6, 7, 8] as $r) {
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF374151']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
                    ]);
                    $sheet->getStyle("D{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF374151']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
                    ]);
                    // Merge value columns
                    $sheet->mergeCells("B{$r}:C{$r}");
                    $sheet->mergeCells("E{$r}:K{$r}");
                }

                // Border info block
                $sheet->getStyle('A4:K8')->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']],
                    ],
                ]);

                // ── Header tabel pemeriksaan (baris 10) ───────────────────
                $sheet->getStyle('A10:K10')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD97706']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFB45309']]],
                ]);
                $sheet->getRowDimension(10)->setRowHeight(22);
                $sheet->freezePane('A11');

                // ── Data rows ─────────────────────────────────────────────
                $lastRow = $sheet->getHighestRow();
                for ($r = 11; $r <= $lastRow; $r++) {
                    $bgColor  = ($r % 2 === 0) ? 'FFFFF7ED' : 'FFFFFFFF';
                    $sheet->getStyle("A{$r}:K{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                    ]);

                    // Warna kondisi kolom C–H
                    foreach (['C', 'D', 'E', 'F', 'G', 'H'] as $col) {
                        $val = $sheet->getCell("{$col}{$r}")->getValue();
                        if ($val === 'OK') {
                            $sheet->getStyle("{$col}{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FF15803D']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        } elseif ($val === 'NOT OK') {
                            $sheet->getStyle("{$col}{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FFB91C1C']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }

                    // Warna kolom Status (I)
                    $status = $sheet->getCell("I{$r}")->getValue();
                    if ($status === 'Semua OK') {
                        $sheet->getStyle("I{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF15803D']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    } elseif ($status === 'Ada Masalah') {
                        $sheet->getStyle("I{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFB91C1C']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }
            },
        ];
    }
}
