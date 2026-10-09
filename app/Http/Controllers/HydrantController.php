<?php

namespace App\Http\Controllers;

use App\Models\Hydrant;
use App\Models\HydrantInspection;
use Illuminate\Http\Request;
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
        $hydrant = Hydrant::where('code', $code)->with(['inspections' => function($q) {
            $q->orderByDesc('periode')->orderByDesc('id');
        }, 'inspections.inspector'])->firstOrFail();

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
            'inspected_at'        => 'required|date',
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
        ]);

        $hydrant = Hydrant::findOrFail($hydrantId);

        // Check unique per periode
        $exists = HydrantInspection::where('hydrant_id', $hydrant->id)
            ->where('periode', $validated['periode'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Hydrant ini sudah diinspeksi pada periode tersebut.');
        }

        $validated['hydrant_id'] = $hydrant->id;
        $validated['inspected_by'] = Auth::id();

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

    public function exportInspection(Request $request)
    {
        // TODO: Implement Maatwebsite Excel export
        return back()->with('error', 'Export Excel Inspeksi Hydrant belum diimplementasi.');
    }
}
