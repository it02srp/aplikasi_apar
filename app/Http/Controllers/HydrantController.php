<?php

namespace App\Http\Controllers;

use App\Models\Hydrant;
use App\Models\HydrantInspection;
use App\Models\HydrantMaintenance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HydrantController extends Controller
{
    public function index(Request $request)
    {
        $query = Hydrant::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('responsible_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        $hydrants = $query->with('latestInspection')->paginate(10)->withQueryString();
        
        return view('hydrant.index', compact('hydrants'));
    }

    public function create()
    {
        $nextCode = Hydrant::generateCode();
        $admins = \App\Models\User::where('role', 'admin')->get();
        return view('hydrant.create', compact('nextCode', 'admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location'           => 'required|string|max:255',
            'hose_length'        => 'required|integer|in:20,30',
            'condition'          => 'required|in:Good,Needs Attention,Damaged',
            'responsible_person' => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        $validated['code'] = Hydrant::generateCode();

        Hydrant::create($validated);

        return redirect()->route('hydrant.index')->with('success', 'Data Hydrant berhasil ditambahkan.');
    }

    public function show($code)
    {
        $hydrant = Hydrant::where('code', $code)->with([
            'inspections'          => fn($q) => $q->orderByDesc('periode')->orderByDesc('id'),
            'inspections.inspector',
            'latestInspection',
            'maintenances'         => fn($q) => $q->orderByDesc('maintenance_date')->orderByDesc('id'),
            'maintenances.performer',
            'latestMaintenance',
        ])->firstOrFail();

        // For public access vs admin access, we can handle inside the view.
        return view('hydrant.show', compact('hydrant'));
    }

    public function edit($code)
    {
        $hydrant = Hydrant::where('code', $code)->firstOrFail();
        $admins = \App\Models\User::where('role', 'admin')->get();
        return view('hydrant.edit', compact('hydrant', 'admins'));
    }

    public function update(Request $request, $code)
    {
        $hydrant = Hydrant::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'location'           => 'required|string|max:255',
            'hose_length'        => 'required|integer|in:20,30',
            'condition'          => 'required|in:Good,Needs Attention,Damaged',
            'responsible_person' => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        $hydrant->update($validated);

        return redirect()->route('hydrant.index')->with('success', 'Data Hydrant berhasil diperbarui.');
    }

    public function destroy($code)
    {
        $hydrant = Hydrant::where('code', $code)->firstOrFail();
        $hydrant->delete();

        return redirect()->route('hydrant.index')->with('success', 'Data Hydrant berhasil dihapus.');
    }

    public function inspectionIndex(Request $request)
    {
        $query = HydrantInspection::with(['hydrant', 'inspector'])->orderByDesc('periode')->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('hydrant', function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        $inspections = $query->paginate(10)->withQueryString();
        $hydrants = Hydrant::orderBy('code')->get();

        return view('hydrant.inspection', compact('inspections', 'hydrants'));
    }

    public function storeInspectionAdmin(Request $request)
    {
        return $this->processInspection($request, $request->hydrant_id);
    }

    public function storeInspection(Request $request, $code)
    {
        $hydrant = Hydrant::where('code', $code)->firstOrFail();
        return $this->processInspection($request, $hydrant->id);
    }

    private function processInspection(Request $request, $hydrantId)
    {
        $validated = $request->validate([
            'periode'             => 'required|date_format:Y-m',
            'inspected_at'        => 'nullable|date',
            'item_01_kondisi_box' => 'required|in:OK,NOT OK',
            'item_02_akses_bebas' => 'required|in:OK,NOT OK',
            'item_03_nozzle'      => 'required|in:OK,NOT OK',
            'item_04_selang'      => 'required|in:OK,NOT OK',
            'item_05_valve'       => 'required|in:OK,NOT OK',
            'item_06_coupling'    => 'required|in:OK,NOT OK',
            'item_07_kunci'       => 'required|in:OK,NOT OK',
            'item_08_pillar'      => 'required|in:OK,NOT OK',
            'item_09_tekanan'     => 'required|in:OK,NOT OK',
            'item_10_hose_rack'   => 'required|in:OK,NOT OK',
            'item_11_pompa'       => 'required|in:OK,NOT OK',
            'notes'               => 'nullable|string',
            'photo'               => 'required|image|max:5120',
        ]);

        $hydrant = Hydrant::findOrFail($hydrantId);

        // Check unique per periode
        $exists = HydrantInspection::where('hydrant_id', $hydrant->id)
            ->where('periode', $validated['periode'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Hydrant ini sudah diinspeksi pada periode tersebut.');
        }

        $validated['hydrant_id']   = $hydrant->id;
        $validated['inspected_by'] = Auth::id();
        $validated['inspected_at'] = Carbon::now();
        $validated['photo']        = $request->file('photo')->store('photos/hydrant/inspeksi', 'public');

        $inspection = HydrantInspection::create($validated);

        // Update status hydrant based on inspection
        $jumlahNotOk = collect([
            $inspection->item_01_kondisi_box,
            $inspection->item_02_akses_bebas,
            $inspection->item_03_nozzle,
            $inspection->item_04_selang,
            $inspection->item_05_valve,
            $inspection->item_06_coupling,
            $inspection->item_07_kunci,
            $inspection->item_08_pillar,
            $inspection->item_09_tekanan,
            $inspection->item_10_hose_rack,
            $inspection->item_11_pompa,
        ])->filter(fn($v) => $v === 'NOT OK')->count();

        $itemKritisBermasalah = $inspection->item_09_tekanan === 'NOT OK' || $inspection->item_11_pompa === 'NOT OK';

        $kondisiBaru = match(true) {
            $jumlahNotOk === 0 => 'Good',
            $jumlahNotOk <= 2 && !$itemKritisBermasalah => 'Needs Attention',
            default => 'Damaged',
        };

        $hydrant->update(['condition' => $kondisiBaru]);

        return back()->with('success', 'Inspeksi hydrant berhasil disimpan.');
    }

    public function destroyInspection($id)
    {
        $inspection = HydrantInspection::findOrFail($id);
        $inspection->delete();

        return back()->with('success', 'Data inspeksi hydrant berhasil dihapus.');
    }
    
    public function printAll()
    {
        $hydrants = Hydrant::orderBy('code')->get();
        return view('hydrant.print-all', compact('hydrants'));
    }

    public function print($code)
    {
        $hydrant = Hydrant::where('code', $code)->firstOrFail();
        return view('hydrant.print', compact('hydrant'));
    }
    
    public function exportData()
    {
        // TODO: Implement Maatwebsite Excel export
        return back()->with('error', 'Export Excel Hydrant Data belum diimplementasi.');
    }

    public function storeMaintenance(Request $request, $code)
    {
        $hydrant = Hydrant::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|in:' . implode(',', HydrantMaintenance::$types),
            'technician'       => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
            'photo'            => 'required|image|max:5120',
        ]);

        $validated['hydrant_id']   = $hydrant->id;
        $validated['performed_by'] = Auth::id();
        $validated['photo']        = $request->file('photo')->store('photos/hydrant/maintenance', 'public');

        HydrantMaintenance::create($validated);

        return back()->with('success', 'History maintenance berhasil ditambahkan.');
    }

    public function destroyMaintenance($id)
    {
        HydrantMaintenance::findOrFail($id)->delete();
        return back()->with('success', 'Data maintenance berhasil dihapus.');
    }

    public function exportInspection(Request $request)
    {
        // TODO: Implement Maatwebsite Excel export
        return back()->with('error', 'Export Excel Inspeksi Hydrant belum diimplementasi.');
    }

    public function importInspeksi(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
        ]);

        $path = $request->file('file')->getRealPath();

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($path);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        $sheet = $spreadsheet->getSheetByName('sunmary hydrant');
        if (!$sheet) {
            // fallback: coba sheet pertama
            $sheet = $spreadsheet->getActiveSheet();
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        // --- Temukan semua kolom dengan tanggal di baris 12 (TANGGAL row) ---
        $highColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $sheet->getHighestColumn()
        );

        // dateColumns: [ colIndex => ['date' => Carbon, 'periode' => 'Y-m'] ]
        $dateColumns = [];
        for ($ci = 1; $ci <= $highColIndex; $ci++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
            $raw = trim($sheet->getCell($col . '12')->getFormattedValue());
            if (empty($raw) || in_array(strtoupper($raw), ['TANGGAL', 'TANGGAL INSPEKSI'])) continue;

            // Format: DD-MM-YYYY atau DD/MM/YYYY
            $date = null;
            foreach (['d-m-Y', 'd/m/Y', 'Y-m-d'] as $fmt) {
                try {
                    $date = \Carbon\Carbon::createFromFormat($fmt, $raw);
                    break;
                } catch (\Throwable) {}
            }
            if (!$date) continue;

            $dateColumns[$ci] = [
                'date'    => $date,
                'periode' => $date->format('Y-m'),
            ];
        }

        if (empty($dateColumns)) {
            return back()->with('error', 'Tidak ditemukan data tanggal inspeksi di file Excel. Pastikan format file sudah benar (baris TANGGAL harus berisi tanggal dd-mm-yyyy).');
        }

        // --- Temukan baris data (baris di mana kolom B = angka, kolom C = lokasi) ---
        $dataRows = []; // [ ['row' => int, 'location' => string] ]
        $seenLocations = [];
        for ($r = 1; $r <= $sheet->getHighestRow(); $r++) {
            $bVal = trim($sheet->getCell('B' . $r)->getFormattedValue());
            $cVal = trim($sheet->getCell('C' . $r)->getFormattedValue());
            if (is_numeric($bVal) && !empty($cVal) && strtolower($cVal) !== 'area hydrant') {
                $locKey = strtolower($cVal);
                if (!in_array($locKey, $seenLocations)) {
                    $seenLocations[] = $locKey;
                    $dataRows[] = ['row' => $r, 'location' => $cVal];
                }
            }
        }

        if (empty($dataRows)) {
            return back()->with('error', 'Tidak ditemukan data lokasi hydrant di file Excel.');
        }

        $inspectorId = Auth::id();

        $toStatus = function (string $val): string {
            $lower = strtolower(trim($val));
            if (empty($lower) || $lower === '-' || $lower === 'tidak ada' || str_starts_with($lower, 'tidak')) return 'NOT OK';
            if (str_starts_with($lower, 'ada') || $lower === 'ok') return 'OK';
            return 'OK'; // default OK jika ada tapi tidak jelas
        };

        foreach ($dataRows as $rowData) {
            $location = $rowData['location'];
            $rowNum   = $rowData['row'];

            // Cari hydrant berdasarkan lokasi (case-insensitive)
            $hydrant = Hydrant::whereRaw('LOWER(TRIM(location)) = ?', [strtolower(trim($location))])->first();
            if (!$hydrant) {
                // Auto-create hydrant baru
                $hydrant = Hydrant::create([
                    'code'        => Hydrant::generateCode(),
                    'location'    => $location,
                    'hose_length' => 20,
                    'condition'   => 'Good',
                ]);
            }

            foreach ($dateColumns as $ci => $dateInfo) {
                $periode = $dateInfo['periode'];

                // Skip jika periode ini sudah ada
                if (HydrantInspection::where('hydrant_id', $hydrant->id)->where('periode', $periode)->exists()) {
                    $skipped++;
                    continue;
                }

                // Kolom: ci=Nozel, ci+1=Selang, ci+2=Kopling, ci+3=Pompa
                $colNozel   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
                $colSelang  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $colKopling = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 2);
                $colPompa   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 3);

                $valNozel   = trim($sheet->getCell($colNozel   . $rowNum)->getFormattedValue());
                $valSelang  = trim($sheet->getCell($colSelang  . $rowNum)->getFormattedValue());
                $valKopling = trim($sheet->getCell($colKopling . $rowNum)->getFormattedValue());
                $valPompa   = trim($sheet->getCell($colPompa   . $rowNum)->getFormattedValue());

                // Skip baris kosong (tidak ada data inspeksi bulan ini)
                if (empty($valNozel) && empty($valSelang) && empty($valKopling) && empty($valPompa)) {
                    $skipped++;
                    continue;
                }

                $statusNozel   = $toStatus($valNozel);
                $statusSelang  = $toStatus($valSelang);
                $statusKopling = $toStatus($valKopling);
                $statusPompa   = $toStatus($valPompa);

                HydrantInspection::create([
                    'hydrant_id'          => $hydrant->id,
                    'inspected_by'        => $inspectorId,
                    'periode'             => $periode,
                    'inspected_at'        => $dateInfo['date']->format('Y-m-d'),
                    'item_01_kondisi_box' => 'OK',
                    'item_02_akses_bebas' => 'OK',
                    'item_03_nozzle'      => $statusNozel,
                    'item_04_selang'      => $statusSelang,
                    'item_05_valve'       => 'OK',
                    'item_06_coupling'    => $statusKopling,
                    'item_07_kunci'       => 'OK',
                    'item_08_pillar'      => 'OK',
                    'item_09_tekanan'     => 'OK',
                    'item_10_hose_rack'   => 'OK',
                    'item_11_pompa'       => $statusPompa,
                ]);

                $imported++;
            }
        }

        $msg = "Import selesai: {$imported} inspeksi berhasil diimpor";
        if ($skipped > 0) $msg .= ", {$skipped} dilewati (sudah ada / kosong)";
        $msg .= '.';

        return back()->with('success', $msg);
    }
}
