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

class AparAllSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function __construct(private Collection $apars) {}

    public function title(): string { return 'Data APAR'; }

    public function array(): array
    {
        $today = Carbon::today();
        $rows  = [];

        // ── Judul ──────────────────────────────────────────────────────────
        $rows[] = ['DATA APAR — PT Sinar Rimba Pasifik'];
        $rows[] = ['Diekspor pada: ' . Carbon::now()->format('d/m/Y H:i')];
        $rows[] = [];

        // ── Header kolom ───────────────────────────────────────────────────
        $rows[] = [
            'No', 'Kode APAR', 'Lokasi', 'Gedung', 'Lantai', 'Ruangan',
            'Jenis', 'Kapasitas', 'Satuan',
            'Tgl Produksi', 'Tgl Kadaluarsa',
            'Inspeksi Terakhir', 'Inspeksi Berikutnya',
            'Kondisi', 'Status', 'Maintenance',
            'Penanggung Jawab', 'Catatan',
        ];

        // ── Data ───────────────────────────────────────────────────────────
        foreach ($this->apars as $i => $a) {
            $expiry = $a->expiry_date;
            if ($expiry->lt($today)) {
                $status = 'Kadaluarsa';
            } elseif ($expiry->diffInDays($today, false) >= -30) {
                $status = 'Hampir Kadaluarsa';
            } else {
                $status = 'Aktif';
            }

            $rows[] = [
                $i + 1,
                $a->code,
                $a->location,
                $a->building  ?? '-',
                $a->floor     ?? '-',
                $a->room      ?? '-',
                $a->type,
                $a->capacity,
                $a->capacity_unit,
                $a->manufacture_date->format('d/m/Y'),
                $expiry->format('d/m/Y'),
                $a->last_inspection_date  ? $a->last_inspection_date->format('d/m/Y')  : '-',
                $a->next_inspection_date  ? $a->next_inspection_date->format('d/m/Y')  : '-',
                $a->condition,
                $status,
                $a->is_maintenance ? 'Ya' : 'Tidak',
                $a->responsible_person ?? '-',
                $a->notes ?? '',
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 13,  // Kode
            'C' => 22,  // Lokasi
            'D' => 14,  // Gedung
            'E' => 10,  // Lantai
            'F' => 16,  // Ruangan
            'G' => 15,  // Jenis
            'H' => 11,  // Kapasitas
            'I' => 8,   // Satuan
            'J' => 14,  // Tgl Produksi
            'K' => 17,  // Tgl Kadaluarsa
            'L' => 17,  // Inspeksi Terakhir
            'M' => 18,  // Inspeksi Berikutnya
            'N' => 17,  // Kondisi
            'O' => 18,  // Status
            'P' => 13,  // Maintenance
            'Q' => 20,  // Penanggung Jawab
            'R' => 30,  // Catatan
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // ── Judul ─────────────────────────────────────────────────
                $ws->mergeCells('A1:R1');
                $ws->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $ws->getRowDimension(1)->setRowHeight(30);

                $ws->mergeCells('A2:R2');
                $ws->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF166534']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Header tabel (baris 4) ─────────────────────────────────
                $ws->getStyle('A4:R4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF166534']]],
                ]);
                $ws->getRowDimension(4)->setRowHeight(22);
                $ws->freezePane('A5');

                // ── Data rows ─────────────────────────────────────────────
                $lastRow = $ws->getHighestRow();
                for ($r = 5; $r <= $lastRow; $r++) {
                    $bg = ($r % 2 === 0) ? 'FFF0FDF4' : 'FFFFFFFF';
                    $ws->getStyle("A{$r}:R{$r}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    ]);

                    // Warna kolom kondisi (N)
                    $kondisi = $ws->getCell("N{$r}")->getValue();
                    $kondisiStyle = match($kondisi) {
                        'Good'            => ['font' => ['color' => ['argb' => 'FF15803D']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']]],
                        'Needs Attention' => ['font' => ['color' => ['argb' => 'FFD97706']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']]],
                        'Replace'         => ['font' => ['color' => ['argb' => 'FFB91C1C']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']]],
                        default           => [],
                    };
                    if ($kondisiStyle) {
                        $ws->getStyle("N{$r}")->applyFromArray(array_merge($kondisiStyle, [
                            'font'      => array_merge($kondisiStyle['font'], ['bold' => true]),
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]));
                    }

                    // Warna kolom status (O)
                    $status = $ws->getCell("O{$r}")->getValue();
                    $statusStyle = match($status) {
                        'Aktif'            => ['font' => ['color' => ['argb' => 'FF15803D']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']]],
                        'Hampir Kadaluarsa'=> ['font' => ['color' => ['argb' => 'FFD97706']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']]],
                        'Kadaluarsa'       => ['font' => ['color' => ['argb' => 'FFB91C1C']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']]],
                        default            => [],
                    };
                    if ($statusStyle) {
                        $ws->getStyle("O{$r}")->applyFromArray(array_merge($statusStyle, [
                            'font'      => array_merge($statusStyle['font'], ['bold' => true]),
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]));
                    }

                    // Warna kolom maintenance (P)
                    if ($ws->getCell("P{$r}")->getValue() === 'Ya') {
                        $ws->getStyle("P{$r}")->applyFromArray([
                            'font'      => ['bold' => true, 'color' => ['argb' => 'FF7C3AED']],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDE9FE']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Center: No, Kode, Jenis, Kapasitas, Satuan, tgl-tgl, Maintenance
                    foreach (['A', 'B', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'P'] as $col) {
                        $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }
}
