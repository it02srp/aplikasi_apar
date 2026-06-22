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

class AparSummarySheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    private int $rowJumlah;
    private int $rowJenis;
    private int $rowKondisi;
    private int $rowStatus;
    private int $rowLokasi;
    private int $lastRow;

    public function __construct(private Collection $apars) {}

    public function title(): string { return 'Ringkasan'; }

    public function array(): array
    {
        $today  = Carbon::today();
        $rows   = [];

        // ── Judul ──────────────────────────────────────────────────────────
        $rows[] = ['RINGKASAN DATA APAR — PT Sinar Rimba Pasifik'];
        $rows[] = ['Diekspor pada: ' . Carbon::now()->format('d/m/Y H:i')];
        $rows[] = [];

        // ── Statistik jumlah ───────────────────────────────────────────────
        $total       = $this->apars->count();
        $expired     = $this->apars->filter(fn($a) => $a->expiry_date->lt($today))->count();
        $nearExpiry  = $this->apars->filter(fn($a) => !$a->expiry_date->lt($today) && $a->expiry_date->diffInDays($today, false) >= -30)->count();
        $aktif       = $total - $expired - $nearExpiry;
        $maintenance = $this->apars->filter(fn($a) => $a->is_maintenance)->count();

        $this->rowJumlah = count($rows) + 1;
        $rows[] = ['JUMLAH APAR'];
        $rows[] = ['Total APAR',          $total];
        $rows[] = ['Aktif',               $aktif];
        $rows[] = ['Hampir Kadaluarsa',   $nearExpiry];
        $rows[] = ['Kadaluarsa',          $expired];
        $rows[] = ['Sedang Maintenance',  $maintenance];
        $rows[] = [];

        // ── Per Jenis ──────────────────────────────────────────────────────
        $this->rowJenis = count($rows) + 1;
        $rows[] = ['PER JENIS'];
        $rows[] = ['Jenis', 'Jumlah', 'Persentase'];
        $byType = $this->apars->groupBy('type')->map->count()->sortDesc();
        foreach ($byType as $type => $count) {
            $pct = $total > 0 ? round($count / $total * 100, 1) : 0;
            $rows[] = [$type, $count, $pct . '%'];
        }
        $rows[] = [];

        // ── Per Kondisi ────────────────────────────────────────────────────
        $this->rowKondisi = count($rows) + 1;
        $rows[] = ['PER KONDISI'];
        $rows[] = ['Kondisi', 'Jumlah', 'Persentase'];
        $byCondition = $this->apars->groupBy('condition')->map->count();
        foreach ($byCondition as $cond => $count) {
            $pct = $total > 0 ? round($count / $total * 100, 1) : 0;
            $rows[] = [$cond, $count, $pct . '%'];
        }
        $rows[] = [];

        // ── Per Status Kadaluarsa ──────────────────────────────────────────
        $this->rowStatus = count($rows) + 1;
        $rows[] = ['PER STATUS KADALUARSA'];
        $rows[] = ['Status', 'Jumlah', 'Persentase'];
        $statusData = [
            'Aktif'             => $aktif,
            'Hampir Kadaluarsa' => $nearExpiry,
            'Kadaluarsa'        => $expired,
        ];
        foreach ($statusData as $status => $count) {
            $pct = $total > 0 ? round($count / $total * 100, 1) : 0;
            $rows[] = [$status, $count, $pct . '%'];
        }
        $rows[] = [];

        // ── Per Lokasi ─────────────────────────────────────────────────────
        $this->rowLokasi = count($rows) + 1;
        $rows[] = ['PER LOKASI'];
        $rows[] = ['Lokasi', 'Jumlah', 'Aktif', 'Hampir Kadaluarsa', 'Kadaluarsa'];
        $byLocation = $this->apars->groupBy('location')->sortKeys();
        foreach ($byLocation as $loc => $group) {
            $locExpired   = $group->filter(fn($a) => $a->expiry_date->lt($today))->count();
            $locNear      = $group->filter(fn($a) => !$a->expiry_date->lt($today) && $a->expiry_date->diffInDays($today, false) >= -30)->count();
            $locAktif     = $group->count() - $locExpired - $locNear;
            $rows[]       = [$loc, $group->count(), $locAktif, $locNear, $locExpired];
        }

        $this->lastRow = count($rows);

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 26,
            'B' => 12,
            'C' => 18,
            'D' => 20,
            'E' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // ── Judul ─────────────────────────────────────────────────
                $ws->mergeCells('A1:E1');
                $ws->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $ws->getRowDimension(1)->setRowHeight(30);

                $ws->mergeCells('A2:E2');
                $ws->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF166534']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdcfce7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Helper: style section header
                $styleSection = function (int $row, string $color, string $bgColor) use ($ws) {
                    $ws->mergeCells("A{$row}:E{$row}");
                    $ws->getStyle("A{$row}:E{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF' . $color]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bgColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $ws->getRowDimension($row)->setRowHeight(20);
                };

                // Helper: style sub-header (label row)
                $styleSubHeader = function (int $row, string $bgColor) use ($ws) {
                    $ws->getStyle("A{$row}:E{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bgColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    ]);
                    $ws->getRowDimension($row)->setRowHeight(18);
                };

                // Helper: style data range
                $styleRange = function (int $fromRow, int $toRow, string $evenBg) use ($ws) {
                    for ($r = $fromRow; $r <= $toRow; $r++) {
                        $bg = ($r % 2 === 0) ? $evenBg : 'FFFFFFFF';
                        $ws->getStyle("A{$r}:E{$r}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                        ]);
                        $ws->getStyle("B{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                };

                // ── Jumlah APAR ────────────────────────────────────────────
                $rJumlah = $this->rowJumlah;
                $styleSection($rJumlah, 'FFFFFF', '1D4ED8'); // biru
                $styleRange($rJumlah + 1, $rJumlah + 5, 'FFEFF6FF');
                // Warnai baris spesifik
                $ws->getStyle("A" . ($rJumlah + 2))->getFont()->getColor()->setARGB('FF15803D'); // Aktif
                $ws->getStyle("A" . ($rJumlah + 3))->getFont()->getColor()->setARGB('FFD97706'); // Hampir
                $ws->getStyle("A" . ($rJumlah + 4))->getFont()->getColor()->setARGB('FFB91C1C'); // Expired
                $ws->getStyle("A" . ($rJumlah + 5))->getFont()->getColor()->setARGB('FF7C3AED'); // Maintenance

                // ── Per Jenis ──────────────────────────────────────────────
                $rJenis = $this->rowJenis;
                $styleSection($rJenis, 'FFFFFF', '15803D');
                $styleSubHeader($rJenis + 1, '15803D');
                $lastJenis = $rJenis + 1 + $this->apars->groupBy('type')->count();
                $styleRange($rJenis + 2, $lastJenis, 'FFF0FDF4');

                // ── Per Kondisi ────────────────────────────────────────────
                $rKondisi = $this->rowKondisi;
                $styleSection($rKondisi, 'FFFFFF', 'D97706');
                $styleSubHeader($rKondisi + 1, 'D97706');
                $lastKondisi = $rKondisi + 1 + $this->apars->groupBy('condition')->count();
                $styleRange($rKondisi + 2, $lastKondisi, 'FFFEF3C7');

                // Warna per kondisi
                for ($r = $rKondisi + 2; $r <= $lastKondisi; $r++) {
                    $val = $ws->getCell("A{$r}")->getValue();
                    $color = match($val) {
                        'Good'            => 'FF15803D',
                        'Needs Attention' => 'FFD97706',
                        'Replace'         => 'FFB91C1C',
                        default           => 'FF374151',
                    };
                    $ws->getStyle("A{$r}")->getFont()->getColor()->setARGB($color);
                    $ws->getStyle("A{$r}")->getFont()->setBold(true);
                }

                // ── Per Status ─────────────────────────────────────────────
                $rStatus = $this->rowStatus;
                $styleSection($rStatus, 'FFFFFF', 'B91C1C');
                $styleSubHeader($rStatus + 1, 'B91C1C');
                $styleRange($rStatus + 2, $rStatus + 4, 'FFFFF1F1');

                $ws->getStyle("A" . ($rStatus + 2))->getFont()->getColor()->setARGB('FF15803D');
                $ws->getStyle("A" . ($rStatus + 3))->getFont()->getColor()->setARGB('FFD97706');
                $ws->getStyle("A" . ($rStatus + 4))->getFont()->getColor()->setARGB('FFB91C1C');

                // ── Per Lokasi ─────────────────────────────────────────────
                $rLokasi = $this->rowLokasi;
                $styleSection($rLokasi, 'FFFFFF', '374151');
                $styleSubHeader($rLokasi + 1, '374151');
                $lastLokasi = $rLokasi + 1 + $this->apars->groupBy('location')->count();
                $styleRange($rLokasi + 2, $lastLokasi, 'FFF9FAFB');

                // Bold label jumlah di kolom B per lokasi
                for ($r = $rLokasi + 2; $r <= $lastLokasi; $r++) {
                    $ws->getStyle("B{$r}")->getFont()->setBold(true);
                }

                // Bold semua label di kolom A (nama baris statistik)
                for ($r = $rJumlah + 1; $r <= $rJumlah + 5; $r++) {
                    $ws->getStyle("A{$r}")->getFont()->setBold(true);
                }
            },
        ];
    }
}
