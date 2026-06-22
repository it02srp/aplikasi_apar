<?php

namespace App\Http\Controllers;

use App\Exports\AparDataExport;
use App\Exports\AparInspectionExport;
use App\Exports\AparTemplateExport;
use App\Imports\AparImport;
use App\Models\Apar;
use App\Models\AparInspection;
use App\Models\AparMaintenance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AparController extends Controller
{
    public function index(Request $request)
    {
        $query = Apar::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($condition = $request->input('condition')) {
            $query->where('condition', $condition);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $apars = $query->orderBy('code')->paginate(15)->withQueryString();
        $types = Apar::distinct()->orderBy('type')->pluck('type');

        return view('apar.index', compact('apars', 'types'));
    }

    public function create()
    {
        $nextCode = Apar::generateCode();
        $types = Apar::distinct()->orderBy('type')->pluck('type');
        return view('apar.create', compact('nextCode', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                  => 'nullable|string|unique:apars,code',
            'location'              => 'required|string|max:255',
            'building'              => 'nullable|string|max:255',
            'floor'                 => 'nullable|string|max:100',
            'room'                  => 'nullable|string|max:100',
            'type'                  => 'required|string|max:100',
            'capacity'              => 'required|numeric|min:0',
            'capacity_unit'         => 'required|in:kg,liter',
            'manufacture_date'      => 'required|date',
            'expiry_date'           => 'required|date|after:manufacture_date',
            'last_inspection_date'  => 'nullable|date',
            'next_inspection_date'  => 'nullable|date',
            'condition'             => 'required|in:Good,Needs Attention,Replace',
            'responsible_person'    => 'nullable|string|max:255',
            'notes'                 => 'nullable|string',
        ]);

        $validated['type'] = ucwords(strtolower(trim($validated['type'])));

        if (empty($validated['code'])) {
            $validated['code'] = Apar::generateCode();
        }

        Apar::create($validated);

        return redirect()->route('apar.index')
            ->with('success', "APAR {$validated['code']} berhasil ditambahkan.");
    }

    public function show(string $code)
    {
        $apar = Apar::where('code', $code)
            ->with(['maintenances.performer', 'inspections.inspector'])
            ->firstOrFail();
        return view('apar.show', compact('apar'));
    }

    public function storeInspection(Request $request, string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'kondisi_handle'    => 'required|in:OK,NOT OK',
            'kondisi_selang'    => 'required|in:OK,NOT OK',
            'kondisi_pin_kunci' => 'required|in:OK,NOT OK',
            'kondisi_indikator' => 'required|in:OK,NOT OK',
            'kondisi_tabung'    => 'required|in:OK,NOT OK',
            'kondisi_masa_apar' => 'required|in:OK,NOT OK',
            'notes'             => 'nullable|string',
        ]);

        $now = Carbon::now();

        AparInspection::create([
            'apar_id'           => $apar->id,
            'inspected_at'      => $now,
            'inspected_by'      => Auth::id(),
            'kondisi_handle'    => $validated['kondisi_handle'],
            'kondisi_selang'    => $validated['kondisi_selang'],
            'kondisi_pin_kunci' => $validated['kondisi_pin_kunci'],
            'kondisi_indikator' => $validated['kondisi_indikator'],
            'kondisi_tabung'    => $validated['kondisi_tabung'],
            'kondisi_masa_apar' => $validated['kondisi_masa_apar'],
            'notes'             => $validated['notes'] ?? null,
        ]);

        // Update last_inspection_date on the APAR
        $apar->update(['last_inspection_date' => $now->toDateString()]);

        return redirect()->route('apar.show', $code)
            ->with('success', 'Pemeriksaan berkala berhasil disimpan.');
    }

    public function destroyInspection(int $id)
    {
        $inspection = AparInspection::findOrFail($id);
        $code       = $inspection->apar->code;
        $inspection->delete();

        $from = request()->query('from');
        if ($from === 'list') {
            return redirect()->route('apar.inspection.index')
                ->with('success', 'Record pemeriksaan berhasil dihapus.');
        }

        return redirect()->route('apar.show', $code)
            ->with('success', 'Record pemeriksaan berhasil dihapus.');
    }

    public function inspectionIndex(Request $request)
    {
        $query = AparInspection::with(['apar', 'inspector'])
            ->orderByDesc('inspected_at')
            ->orderByDesc('id');

        if ($search = $request->input('search')) {
            $query->whereHas('apar', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('inspected_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('inspected_at', '<=', $dateTo);
        }

        if ($year = $request->input('year')) {
            $query->whereYear('inspected_at', $year);
        }

        $inspections = $query->paginate(25)->withQueryString();
        $aparList    = Apar::orderBy('code')->get(['id', 'code', 'location']);

        $availableYears = AparInspection::selectRaw('YEAR(inspected_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('apar.inspection', compact('inspections', 'aparList', 'availableYears'));
    }

    public function exportInspection(Request $request)
    {
        $codes = $request->input('codes', []);

        if (empty($codes)) {
            return redirect()->back()->with('error', 'Pilih minimal satu APAR untuk diexport.');
        }

        $filename = 'pemeriksaan_berkala_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AparInspectionExport($codes), $filename);
    }

    public function storeInspectionAdmin(Request $request)
    {
        $validated = $request->validate([
            'apar_code'         => 'required|exists:apars,code',
            'kondisi_handle'    => 'required|in:OK,NOT OK',
            'kondisi_selang'    => 'required|in:OK,NOT OK',
            'kondisi_pin_kunci' => 'required|in:OK,NOT OK',
            'kondisi_indikator' => 'required|in:OK,NOT OK',
            'kondisi_tabung'    => 'required|in:OK,NOT OK',
            'kondisi_masa_apar' => 'required|in:OK,NOT OK',
            'notes'             => 'nullable|string',
        ], [
            'apar_code.required' => 'Pilih APAR terlebih dahulu.',
            'apar_code.exists'   => 'APAR tidak ditemukan.',
        ]);

        $apar = Apar::where('code', $validated['apar_code'])->firstOrFail();
        $now  = Carbon::now();

        AparInspection::create([
            'apar_id'           => $apar->id,
            'inspected_at'      => $now,
            'inspected_by'      => Auth::id(),
            'kondisi_handle'    => $validated['kondisi_handle'],
            'kondisi_selang'    => $validated['kondisi_selang'],
            'kondisi_pin_kunci' => $validated['kondisi_pin_kunci'],
            'kondisi_indikator' => $validated['kondisi_indikator'],
            'kondisi_tabung'    => $validated['kondisi_tabung'],
            'kondisi_masa_apar' => $validated['kondisi_masa_apar'],
            'notes'             => $validated['notes'] ?? null,
        ]);

        $apar->update(['last_inspection_date' => $now->toDateString()]);

        return redirect()->route('apar.inspection.index')
            ->with('success', "Pemeriksaan {$apar->code} berhasil disimpan.");
    }

    public function toggleMaintenance(string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();

        if ($apar->is_maintenance) {
            $apar->update([
                'is_maintenance'         => false,
                'maintenance_started_at' => null,
            ]);
            $msg = "APAR {$code} selesai maintenance.";
        } else {
            $apar->update([
                'is_maintenance'         => true,
                'maintenance_started_at' => Carbon::now(),
            ]);
            $msg = "APAR {$code} sedang dalam maintenance.";
        }

        return redirect()->route('apar.show', $code)->with('success', $msg);
    }

    public function storeMaintenanceAdmin(Request $request)
    {
        $validated = $request->validate([
            'apar_code'             => 'required|exists:apars,code',
            'maintenance_date'      => 'required|date',
            'next_inspection_date'  => 'nullable|date|after:maintenance_date',
            'maintenance_type'      => 'required|in:Inspeksi Rutin,Pengisian Ulang,Penggantian Komponen,Perbaikan,Lainnya',
            'technician'            => 'nullable|string|max:255',
            'notes'                 => 'nullable|string',
        ], [
            'apar_code.required'            => 'Pilih APAR terlebih dahulu.',
            'apar_code.exists'              => 'APAR tidak ditemukan.',
            'maintenance_date.required'     => 'Tanggal inspeksi wajib diisi.',
            'maintenance_type.required'     => 'Jenis maintenance wajib dipilih.',
            'next_inspection_date.after'    => 'Tanggal inspeksi berikutnya harus setelah tanggal inspeksi.',
        ]);

        $apar = Apar::where('code', $validated['apar_code'])->firstOrFail();

        AparMaintenance::create([
            'apar_id'              => $apar->id,
            'maintenance_date'     => $validated['maintenance_date'],
            'next_inspection_date' => $validated['next_inspection_date'] ?? null,
            'maintenance_type'     => $validated['maintenance_type'],
            'technician'           => $validated['technician'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'performed_by'         => Auth::id(),
        ]);

        // Update tanggal inspeksi di data APAR
        $apar->update([
            'last_inspection_date' => $validated['maintenance_date'],
            'next_inspection_date' => $validated['next_inspection_date'] ?? $apar->next_inspection_date,
        ]);

        return redirect()->route('apar.maintenance.index')
            ->with('success', "Maintenance {$apar->code} berhasil dicatat.");
    }

    public function storeMaintenance(Request $request, string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'maintenance_date'      => 'required|date',
            'next_inspection_date'  => 'nullable|date|after:maintenance_date',
            'maintenance_type'      => 'required|in:Inspeksi Rutin,Pengisian Ulang,Penggantian Komponen,Perbaikan,Lainnya',
            'technician'            => 'nullable|string|max:255',
            'notes'                 => 'nullable|string',
        ], [
            'maintenance_date.required'  => 'Tanggal inspeksi wajib diisi.',
            'maintenance_type.required'  => 'Jenis maintenance wajib dipilih.',
            'next_inspection_date.after' => 'Tanggal inspeksi berikutnya harus setelah tanggal inspeksi.',
        ]);

        AparMaintenance::create([
            'apar_id'              => $apar->id,
            'maintenance_date'     => $validated['maintenance_date'],
            'next_inspection_date' => $validated['next_inspection_date'] ?? null,
            'maintenance_type'     => $validated['maintenance_type'],
            'technician'           => $validated['technician'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'performed_by'         => Auth::id(),
        ]);

        // Update tanggal inspeksi di data APAR
        $apar->update([
            'last_inspection_date' => $validated['maintenance_date'],
            'next_inspection_date' => $validated['next_inspection_date'] ?? $apar->next_inspection_date,
        ]);

        return redirect()->route('apar.maintenance.index')
            ->with('success', "Maintenance {$apar->code} berhasil dicatat.");
    }

    public function destroyMaintenance(int $id)
    {
        $maintenance = AparMaintenance::findOrFail($id);
        $code = $maintenance->apar->code;
        $maintenance->delete();

        // Redirect kembali ke halaman asal (list atau show)
        $from = request()->query('from');
        if ($from === 'list') {
            return redirect()->route('apar.maintenance.index')
                ->with('success', 'Record maintenance berhasil dihapus.');
        }

        return redirect()->route('apar.show', $code)
            ->with('success', 'Record maintenance berhasil dihapus.');
    }

    public function maintenanceIndex(Request $request)
    {
        $query = AparMaintenance::with(['apar', 'performer'])
            ->orderByDesc('maintenance_date')
            ->orderByDesc('id');

        if ($search = $request->input('search')) {
            $query->whereHas('apar', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('maintenance_type', $type);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('maintenance_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('maintenance_date', '<=', $dateTo);
        }

        if ($year = $request->input('year')) {
            $query->whereYear('maintenance_date', $year);
        }

        $maintenances = $query->paginate(25)->withQueryString();

        $today = Carbon::today();

        $overdueInspection = Apar::whereNotNull('next_inspection_date')
            ->whereDate('next_inspection_date', '<', $today)
            ->orderBy('next_inspection_date')
            ->get();

        $upcomingInspection = Apar::whereNotNull('next_inspection_date')
            ->whereDate('next_inspection_date', '>=', $today)
            ->whereDate('next_inspection_date', '<=', $today->copy()->addDays(30))
            ->orderBy('next_inspection_date')
            ->get();

        $aparList = Apar::orderBy('code')->get(['id', 'code', 'location']);

        $availableYears = AparMaintenance::selectRaw('YEAR(maintenance_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('apar.maintenance', compact('maintenances', 'overdueInspection', 'upcomingInspection', 'aparList', 'availableYears'));
    }

    public function edit(string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();
        $types = Apar::distinct()->orderBy('type')->pluck('type');
        return view('apar.edit', compact('apar', 'types'));
    }

    public function update(Request $request, string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'location'              => 'required|string|max:255',
            'building'              => 'nullable|string|max:255',
            'floor'                 => 'nullable|string|max:100',
            'room'                  => 'nullable|string|max:100',
            'type'                  => 'required|string|max:100',
            'capacity'              => 'required|numeric|min:0',
            'capacity_unit'         => 'required|in:kg,liter',
            'manufacture_date'      => 'required|date',
            'expiry_date'           => 'required|date|after:manufacture_date',
            'last_inspection_date'  => 'nullable|date',
            'next_inspection_date'  => 'nullable|date',
            'condition'             => 'required|in:Good,Needs Attention,Replace',
            'responsible_person'    => 'nullable|string|max:255',
            'notes'                 => 'nullable|string',
        ]);

        $validated['type'] = ucwords(strtolower(trim($validated['type'])));

        $apar->update($validated);

        return redirect()->route('apar.index')
            ->with('success', "APAR {$apar->code} berhasil diperbarui.");
    }

    public function destroy(string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();
        $apar->delete();

        return redirect()->route('apar.index')
            ->with('success', "APAR {$code} berhasil dihapus.");
    }

    public function exportData()
    {
        $filename = 'data_apar_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AparDataExport(), $filename);
    }

    public function downloadTemplate()
    {
        return Excel::download(new AparTemplateExport(), 'template_import_apar.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new AparImport();
        Excel::import($import, $request->file('file'));

        $msg = "Import selesai: {$import->imported} data berhasil diimpor";
        if ($import->skipped > 0) {
            $msg .= ", {$import->skipped} baris dilewati";
        }
        $msg .= '.';

        return redirect()->route('apar.index')
            ->with('success', $msg)
            ->with('import_errors', $import->errors);
    }

    public function printAll()
    {
        $apars = Apar::orderBy('code')->get();
        return view('apar.print-all', compact('apars'));
    }

    public function print(string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();
        return view('apar.print', compact('apar'));
    }

    public function dashboard()
    {
        $today = Carbon::today();
        $total        = Apar::count();
        $expired      = Apar::whereDate('expiry_date', '<', $today)->count();
        $nearExpiry   = Apar::whereDate('expiry_date', '>=', $today)
                            ->whereDate('expiry_date', '<=', $today->copy()->addDays(30))
                            ->count();
        $good         = $total - $expired - $nearExpiry;

        $recentApars  = Apar::orderByDesc('updated_at')->limit(5)->get();

        return view('dashboard', compact('total', 'expired', 'nearExpiry', 'good', 'recentApars'));
    }
}
