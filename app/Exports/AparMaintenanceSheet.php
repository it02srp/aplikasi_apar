<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AparMaintenanceSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function __construct(private Collection $apars) {}

    public function title(): string { return 'Sedang Maintenance'; }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['APAR SEDANG MAINTENANCE — PT Sinar Rimba Pasifik'];
        $rows[] = ['APAR yang saat ini dikeluarkan dari operasional untuk perbaikan/penggantian'];
        $rows[] = [];

        $rows[] = [
            'No', 'Kode APAR', 'Lokasi', 'Gedung/Lantai/Ruangan',
            'Jenis', 'Kapasitas', 'Kondisi',
            'Mulai Maintenance', 'Tgl Kadaluarsa', 'Penanggung Jawab',
        ];

        $inMaintenance = $this->apars->filter(fn($a) => $a->is_maintenance)->values();

        if ($inMaintenance->isEmpty()) {
            $rows[] = ['-', 'Tidak ada APAR yang sedang maintenance', '', '', '', '', '', '', '', ''];
            return $rows;
        }

        foreach ($inMaintenance as $i => $a) {
            $mulai   = $a->maintenance_started_at ? $a->maintenance_started_at->format('d/m/Y') : '-';
            $lokDet  = collect([$a->building, $a->floor ? 'Lt.'.$a->floor : null, $a->room])->filter()->implode(' / ') ?: '-';

            $rows[] = [
                $i + 1,
                $a->code,
                $a->location,
                $lokDet,
                $a->type,
                $a->capacity . ' ' . $a->capacity_unit,
                $a->condition,
                $mulai,
                $a->expiry_date->format('d/m/Y'),
                $a->responsible_person ?? '-',
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 13,
            'C' => 22,
            'D' => 24,
            'E' => 15,
            'F' => 12,
            'G' => 18,
            'H' => 18,
            'I' => 16,
            'J' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                $ws->mergeCells('A1:J1');
                $ws->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $ws->getRowDimension(1)->setRowHeight(30);

                $ws->mergeCells('A2:J2');
                $ws->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF4C1D95']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDE9FE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $ws->getStyle('A4:J4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7C3AED']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF6D28D9']]],
                ]);
                $ws->getRowDimension(4)->setRowHeight(22);
                $ws->freezePane('A5');

                $lastRow = $ws->getHighestRow();
                for ($r = 5; $r <= $lastRow; $r++) {
                    $bg = ($r % 2 === 0) ? 'FFF5F3FF' : 'FFFFFFFF';
                    $ws->getStyle("A{$r}:J{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                    ]);

                    // Kondisi (G)
                    $kondisi = $ws->getCell("G{$r}")->getValue();
                    $kondisiStyle = match($kondisi) {
                        'Good'            => ['font' => ['color' => ['argb' => 'FF15803D']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']]],
                        'Needs Attention' => ['font' => ['color' => ['argb' => 'FFD97706']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']]],
                        'Replace'         => ['font' => ['color' => ['argb' => 'FFB91C1C']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']]],
                        default           => [],
                    };
                    if ($kondisiStyle) {
                        $ws->getStyle("G{$r}")->applyFromArray(array_merge($kondisiStyle, [
                            'font'      => array_merge($kondisiStyle['font'], ['bold' => true]),
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]));
                    }

                    foreach (['A', 'B', 'E', 'F', 'H', 'I'] as $col) {
                        $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }
}
