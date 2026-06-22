<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AparTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new AparDataSheet(),
            new AparPanduanSheet(),
        ];
    }
}

// ─── Sheet 1: Data ────────────────────────────────────────────────────────────
class AparDataSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string { return 'Data APAR'; }

    public function headings(): array
    {
        return [
            'Kode APAR',
            'Lokasi *',
            'Gedung',
            'Lantai',
            'Ruangan',
            'Jenis *',
            'Kapasitas *',
            'Satuan *',
            'Tanggal Produksi *',
            'Tanggal Kadaluarsa *',
            'Inspeksi Terakhir',
            'Inspeksi Berikutnya',
            'Kondisi *',
            'Penanggung Jawab',
            'Catatan',
        ];
    }

    public function array(): array
    {
        // Contoh baris data
        return [
            [
                '',             // Kode (kosong = auto)
                'Lobby Utama',  // Lokasi
                'Gedung A',     // Gedung
                '1',            // Lantai
                'Ruang Tunggu', // Ruangan
                'CO2',          // Jenis
                '6',            // Kapasitas
                'kg',           // Satuan
                '01/01/2023',   // Tgl Produksi
                '01/01/2028',   // Tgl Kadaluarsa
                '01/06/2025',   // Inspeksi Terakhir
                '01/06/2026',   // Inspeksi Berikutnya
                'Good',         // Kondisi
                'Budi Santoso', // Penanggung Jawab
                '',             // Catatan
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, 'B' => 22, 'C' => 14, 'D' => 10,
            'E' => 16, 'F' => 16, 'G' => 12, 'H' => 10,
            'I' => 18, 'J' => 18, 'K' => 18, 'L' => 18,
            'M' => 18, 'N' => 20, 'O' => 24,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header style
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF15803D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF0F5F2A'],
                ],
            ],
        ]);

        // Sample row style
        $sheet->getStyle('A2:O2')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFdcfce7'],
            ],
            'font' => ['color' => ['argb' => 'FF166534'], 'italic' => true],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFBBF7D0'],
                ],
            ],
        ]);

        // Freeze header row
        $sheet->freezePane('A2');

        // Row heights
        $sheet->getRowDimension(1)->setRowHeight(28);

        return [];
    }
}

// ─── Sheet 2: Panduan ─────────────────────────────────────────────────────────
class AparPanduanSheet implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string { return 'Panduan'; }

    public function array(): array
    {
        return [
            ['PANDUAN PENGISIAN TEMPLATE APAR'],
            [''],
            ['Kolom',              'Keterangan',                                        'Contoh / Pilihan'],
            ['Kode APAR',          'Kosongkan untuk auto-generate (APAR-001, dst)',      'APAR-001'],
            ['Lokasi *',           'Nama lokasi/area APAR berada (wajib diisi)',          'Lobby Utama, Area Produksi'],
            ['Gedung',             'Nama gedung (opsional)',                              'Gedung A'],
            ['Lantai',             'Nomor lantai (opsional)',                             '1, 2, Basement'],
            ['Ruangan',            'Nama ruangan spesifik (opsional)',                    'Ruang Server'],
            ['Jenis *',            'Jenis media pemadam (wajib diisi), jenis baru otomatis ditambahkan', 'Co2 | Dry Powder | Foam | Water | Clean Agent'],
            ['Kapasitas *',        'Angka kapasitas saja, tanpa satuan (wajib diisi)',    '6'],
            ['Satuan *',           'Satuan kapasitas (wajib diisi)',                      'kg | liter'],
            ['Tanggal Produksi *', 'Format: DD/MM/YYYY (wajib diisi)',                    '01/01/2023'],
            ['Tgl Kadaluarsa *',   'Format: DD/MM/YYYY (wajib diisi)',                    '01/01/2028'],
            ['Inspeksi Terakhir',  'Format: DD/MM/YYYY (opsional)',                       '01/06/2025'],
            ['Inspeksi Berikutnya','Format: DD/MM/YYYY (opsional)',                       '01/06/2026'],
            ['Kondisi *',          'Status kondisi fisik APAR (wajib diisi)',              'Good | Needs Attention | Replace'],
            ['Penanggung Jawab',   'Nama PIC yang bertanggung jawab (opsional)',           'Budi Santoso'],
            ['Catatan',            'Catatan tambahan (opsional)',                          'Perlu pengecekan seal'],
            [''],
            ['Catatan:',           '* = wajib diisi'],
            ['',                   'Baris hijau miring di sheet Data APAR adalah contoh, hapus sebelum import'],
            ['',                   'Jangan ubah nama kolom di baris pertama sheet Data APAR'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 22, 'B' => 52, 'C' => 50];
    }

    public function styles(Worksheet $sheet): array
    {
        // Title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF15803D']],
        ]);

        // Table header
        $sheet->getStyle('A3:C3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF15803D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data rows alternating
        for ($r = 4; $r <= 21; $r++) {
            $color = ($r % 2 === 0) ? 'FFF0FDF4' : 'FFFFFFFF';
            $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
            ]);
        }

        return [];
    }
}
