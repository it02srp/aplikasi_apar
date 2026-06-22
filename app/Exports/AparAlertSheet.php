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

class AparAlertSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    public function __construct(private Collection $apars) {}

    public function title(): string { return 'Perlu Perhatian'; }

    public function array(): array
    {
        $today = Carbon::today();
        $rows  = [];

        $rows[] = ['APAR PERLU PERHATIAN — PT Sinar Rimba Pasifik'];
        $rows[] = ['Mencakup: Kadaluarsa · Hampir Kadaluarsa (≤30 hari) · Kondisi Needs Attention / Replace'];
        $rows[] = [];

        $rows[] = [
            'No', 'Kode APAR', 'Lokasi', 'Gedung/Lantai/Ruangan',
            'Jenis', 'Kapasitas',
            'Tgl Kadaluarsa', 'Sisa Hari',
            'Kondisi', 'Status',
            'Inspeksi Berikutnya', 'Penanggung Jawab',
        ];

        $filtered = $this->apars->filter(function ($a) use ($today) {
            $expiry      = $a->expiry_date;
            $isExpired   = $expiry->lt($today);
            $isNear      = !$isExpired && $expiry->diffInDays($today, false) >= -30;
            $badCondition = in_array($a->condition, ['Needs Attention', 'Replace']);
            return $isExpired || $isNear || $badCondition;
        })->values();

        if ($filtered->isEmpty()) {
            $rows[] = ['-', 'Tidak ada APAR yang perlu perhatian', '', '', '', '', '', '', '', '', '', ''];
            return $rows;
        }

        foreach ($filtered as $i => $a) {
            $expiry  = $a->expiry_date;
            $diffDay = (int) $today->diffInDays($expiry, false);

            if ($expiry->lt($today)) {
                $status   = 'Kadaluarsa';
                $sisaHari = 'Lewat ' . abs($diffDay) . ' hari';
            } elseif ($diffDay <= 30) {
                $status   = 'Hampir Kadaluarsa';
                $sisaHari = $diffDay . ' hari lagi';
            } else {
                $status   = 'Aktif';
                $sisaHari = $diffDay . ' hari lagi';
            }

            $lokDetail = collect([$a->building, $a->floor ? 'Lt.'.$a->floor : null, $a->room])->filter()->implode(' / ') ?: '-';

            $rows[] = [
                $i + 1,
                $a->code,
                $a->location,
                $lokDetail,
                $a->type,
                $a->capacity . ' ' . $a->capacity_unit,
                $expiry->format('d/m/Y'),
                $sisaHari,
                $a->condition,
                $status,
                $a->next_inspection_date ? $a->next_inspection_date->format('d/m/Y') : '-',
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
            'G' => 16,
            'H' => 18,
            'I' => 18,
            'J' => 18,
            'K' => 18,
            'L' => 20,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Judul
                $ws->mergeCells('A1:L1');
                $ws->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFB91C1C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $ws->getRowDimension(1)->setRowHeight(30);

                $ws->mergeCells('A2:L2');
                $ws->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF7F1D1D']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Header tabel baris 4
                $ws->getStyle('A4:L4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFB91C1C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF991B1B']]],
                ]);
                $ws->getRowDimension(4)->setRowHeight(22);
                $ws->freezePane('A5');

                // Data rows
                $lastRow = $ws->getHighestRow();
                for ($r = 5; $r <= $lastRow; $r++) {
                    $bg = ($r % 2 === 0) ? 'FFFFF1F1' : 'FFFFFFFF';
                    $ws->getStyle("A{$r}:L{$r}")->applyFromArray([
                        'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                    ]);

                    // Kondisi (I)
                    $kondisi = $ws->getCell("I{$r}")->getValue();
                    if ($kondisi === 'Needs Attention') {
                        $ws->getStyle("I{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFD97706']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    } elseif ($kondisi === 'Replace') {
                        $ws->getStyle("I{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFB91C1C']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Status (J)
                    $status = $ws->getCell("J{$r}")->getValue();
                    if ($status === 'Kadaluarsa') {
                        $ws->getStyle("J{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFB91C1C']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    } elseif ($status === 'Hampir Kadaluarsa') {
                        $ws->getStyle("J{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFD97706']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Center columns
                    foreach (['A', 'B', 'E', 'F', 'G', 'H', 'K'] as $col) {
                        $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
            },
        ];
    }
}
