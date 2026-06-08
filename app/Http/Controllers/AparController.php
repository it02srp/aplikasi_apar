<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        return view('apar.index', compact('apars'));
    }

    public function create()
    {
        $nextCode = Apar::generateCode();
        return view('apar.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                  => 'nullable|string|unique:apars,code',
            'location'              => 'required|string|max:255',
            'building'              => 'nullable|string|max:255',
            'floor'                 => 'nullable|string|max:100',
            'room'                  => 'nullable|string|max:100',
            'type'                  => 'required|in:CO2,Dry Powder,Foam,Water,Clean Agent',
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

        if (empty($validated['code'])) {
            $validated['code'] = Apar::generateCode();
        }

        Apar::create($validated);

        return redirect()->route('apar.index')
            ->with('success', "APAR {$validated['code']} berhasil ditambahkan.");
    }

    public function show(string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();
        return view('apar.show', compact('apar'));
    }

    public function edit(string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();
        return view('apar.edit', compact('apar'));
    }

    public function update(Request $request, string $code)
    {
        $apar = Apar::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'location'              => 'required|string|max:255',
            'building'              => 'nullable|string|max:255',
            'floor'                 => 'nullable|string|max:100',
            'room'                  => 'nullable|string|max:100',
            'type'                  => 'required|in:CO2,Dry Powder,Foam,Water,Clean Agent',
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

        $apar->update($validated);

        return redirect()->route('apar.index')
            ->with('success', "APAR {$apar->code} berhasil diperbarui.");
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
