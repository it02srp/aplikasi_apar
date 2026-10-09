@extends('layouts.guest')
@section('title', 'Hydrant ' . $hydrant->code)
@section('content')

@php
    $latest     = $hydrant->latestInspection;
    $latestMt   = $hydrant->latestMaintenance;
    $inspYears  = $hydrant->inspections->groupBy(fn($i) => $i->inspected_at->format('Y'))->keys()->sort()->reverse()->values();
    $mtYears    = $hydrant->maintenances->groupBy(fn($m) => $m->maintenance_date->format('Y'))->keys()->sort()->reverse()->values();
@endphp

<div class="min-h-screen bg-gray-50 py-6 px-4">
<div class="max-w-lg mx-auto">

    {{-- Header --}}
    <div class="text-center mb-5">
        <span class="text-4xl">🚒</span>
        <h1 class="text-xl font-bold text-gray-800 mt-1">Hydrant Management</h1>
        <p class="text-gray-500 text-sm">PT Sinar Rimba Pasifik</p>
    </div>

    {{-- Status Badge --}}
    <div class="flex flex-wrap justify-center gap-2 mb-4">
        @if($hydrant->condition == 'Good')
            <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 border border-green-300 font-bold px-4 py-1.5 rounded-full text-sm">
                ✅ KONDISI BAIK
            </span>
        @elseif($hydrant->condition == 'Needs Attention')
            <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 border border-yellow-300 font-bold px-4 py-1.5 rounded-full text-sm">
                ⚠️ PERLU PERHATIAN
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 border border-red-300 font-bold px-4 py-1.5 rounded-full text-sm">
                🔴 RUSAK
            </span>
        @endif
    </div>

    {{-- Tab Navigation --}}
    <div class="flex bg-white rounded-2xl shadow-sm border border-gray-200 mb-4 p-1 gap-1">
        @foreach(['info' => 'Info Hydrant', 'inspeksi' => 'Inspeksi', 'maintenance' => 'Maintenance'] as $tab => $label)
        <button onclick="switchTab('{{ $tab }}')" id="tab-btn-{{ $tab }}"
                class="flex-1 py-2 rounded-xl text-xs font-semibold transition-all
                       {{ $tab === 'info' ? 'bg-green-700 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
            @if($tab === 'inspeksi' && $hydrant->inspections->count())
                <span class="ml-0.5 opacity-70">({{ $hydrant->inspections->count() }})</span>
            @elseif($tab === 'maintenance' && $hydrant->maintenances->count())
                <span class="ml-0.5 opacity-70">({{ $hydrant->maintenances->count() }})</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════ --}}
    {{-- TAB: INFO --}}
    {{-- ═══════════════════════════════════ --}}
    <div id="tab-info">
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-green-700 px-6 py-4 text-white flex items-center justify-between">
                <div>
                    <p class="text-xs text-green-200 uppercase tracking-widest">Kode Hydrant</p>
                    <p class="text-3xl font-mono font-bold mt-0.5">{{ $hydrant->code }}</p>
                </div>
                @auth
                <a href="{{ route('hydrant.edit', $hydrant->code) }}"
                   class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @endauth
            </div>

            <div class="p-5 space-y-4">

                {{-- Flash --}}
                @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-2 text-xs text-green-800">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-2 text-xs text-red-800">{{ session('error') }}</div>
                @endif

                {{-- Lokasi --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Lokasi</p>
                    <p class="text-gray-800 font-semibold">{{ $hydrant->location }}</p>
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Panjang Selang</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $hydrant->hose_length }} Meter</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Kondisi</p>
                        @php $condColor = match($hydrant->condition) {
                            'Good'           => 'bg-green-100 text-green-700',
                            'Needs Attention' => 'bg-yellow-100 text-yellow-700',
                            default           => 'bg-red-100 text-red-700'
                        }; @endphp
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mt-0.5 {{ $condColor }}">{{ $hydrant->condition }}</span>
                    </div>
                    @if($hydrant->responsible_person)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-400 font-medium">Penanggung Jawab</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $hydrant->responsible_person }}</p>
                    </div>
                    @endif
                </div>

                {{-- Inspeksi & Maintenance terakhir --}}
                <div class="border-t border-gray-100 pt-3 grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Inspeksi Terakhir</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">
                            {{ $latest ? $latest->inspected_at->format('d M Y') : '-' }}
                        </p>
                        @if($latest)
                        <p class="text-xs text-gray-400">{{ $latest->inspected_at->diffForHumans() }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium">Maintenance Terakhir</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">
                            {{ $latestMt ? $latestMt->maintenance_date->format('d M Y') : '-' }}
                        </p>
                        @if($latestMt)
                        <p class="text-xs text-gray-400">{{ $latestMt->maintenance_date->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>

                @if($hydrant->notes)
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400 font-medium mb-1">Catatan</p>
                    <p class="text-sm text-gray-700">{{ $hydrant->notes }}</p>
                </div>
                @endif

                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400">Diperbarui: {{ $hydrant->updated_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════ --}}
    {{-- TAB: INSPEKSI --}}
    {{-- ═══════════════════════════════════ --}}
    <div id="tab-inspeksi" class="hidden">

        @auth
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-4">
            <button onclick="toggleForm('form-inspeksi')"
                    class="w-full flex items-center justify-between px-5 py-3 bg-green-700 text-white hover:bg-green-800 transition">
                <span class="font-semibold text-sm">+ Tambah Inspeksi</span>
                <svg id="icon-form-inspeksi" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="form-inspeksi" class="hidden p-5 bg-green-50 border-t border-green-100">
                <form action="{{ route('hydrant.inspeksi.store', $hydrant->code) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Periode *</label>
                            <input type="month" name="periode" required value="{{ old('periode', date('Y-m')) }}"
                                   class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Tanggal *</label>
                            <input type="date" name="inspected_at" required value="{{ old('inspected_at', date('Y-m-d')) }}"
                                   class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                    @php
                    $items = [
                        'item_01_kondisi_box' => '1 Kondisi Box',
                        'item_02_akses_bebas' => '2 Akses Box',
                        'item_03_nozzle'      => '3 Nozzle',
                        'item_04_selang'      => '4 Selang',
                        'item_05_valve'       => '5 Valve/Kran',
                        'item_06_coupling'    => '6 Coupling',
                        'item_07_kunci'       => '7 Kunci/Tuas',
                        'item_08_pillar'      => '8 Hydrant Pillar',
                        'item_09_tekanan'     => '9 Tekanan Air',
                        'item_10_hose_rack'   => '10 Hose Rack',
                        'item_11_pompa'       => '11 Pompa',
                    ];
                    @endphp
                    @foreach($items as $field => $label)
                    <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-3 py-2">
                        <span class="text-sm text-gray-700 font-medium">{{ $label }}</span>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="OK" class="accent-green-600" {{ old($field,'OK')==='OK'?'checked':'' }}>
                                <span class="text-xs font-semibold text-green-700">✓ OK</span>
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="NOT OK" class="accent-red-500" {{ old($field)==='NOT OK'?'checked':'' }}>
                                <span class="text-xs font-semibold text-red-600">✗ NOT OK</span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                    <textarea name="notes" rows="2" placeholder="Catatan (opsional)"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-green-500">{{ old('notes') }}</textarea>
                    <div>
                        <label class="block text-xs font-semibold text-green-700 mb-1">📷 Foto Bukti <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-400 mb-1.5">Wajib ambil foto langsung dari kamera — tidak bisa dari galeri.</p>
                        <input type="file" name="photo" accept="image/*" capture="environment" required
                               class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-100 file:text-green-700 hover:file:bg-green-200"
                               onchange="previewPhoto(this, 'preview-insp-hyd')">
                        <img id="preview-insp-hyd" src="" alt="" class="hidden mt-2 rounded-lg max-h-36 w-auto border border-gray-200">
                    </div>
                    <button type="submit"
                            class="w-full bg-green-700 hover:bg-green-800 text-white text-sm font-semibold py-2 rounded-lg transition">
                        Simpan Inspeksi
                    </button>
                </form>
            </div>
        </div>
        @endauth

        {{-- Riwayat Inspeksi --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-green-700 px-5 py-3 text-white flex items-center justify-between">
                <p class="font-bold text-sm">Riwayat Inspeksi</p>
                <span class="bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $hydrant->inspections->count() }}</span>
            </div>

            @if($hydrant->inspections->isEmpty())
            <div class="py-10 text-center">
                <p class="text-gray-400 text-sm">Belum ada riwayat inspeksi.</p>
            </div>
            @else
            @if($inspYears->count() > 1)
            <div class="px-4 pt-3 pb-1 flex gap-2 flex-wrap">
                <button onclick="filterYear('inspeksi','all')" id="inspeksi-year-all"
                        class="year-btn-inspeksi px-3 py-1 rounded-full text-xs font-semibold bg-green-700 text-white transition">Semua</button>
                @foreach($inspYears as $y)
                <button onclick="filterYear('inspeksi','{{ $y }}')" id="inspeksi-year-{{ $y }}"
                        class="year-btn-inspeksi px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-green-100 transition">{{ $y }}</button>
                @endforeach
            </div>
            @endif

            <div class="p-4 space-y-2" id="list-inspeksi">
                @php $prevPeriode = null; @endphp
                @foreach($hydrant->inspections as $ins)
                @php $yr = substr($ins->periode, 0, 4); @endphp
                @if($ins->periode !== $prevPeriode)
                @php $prevPeriode = $ins->periode; @endphp
                <div class="pt-1 pb-0.5 inspeksi-row" data-year="{{ $yr }}">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $ins->inspected_at->format('F Y') }}</p>
                </div>
                @endif
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden inspeksi-row" data-year="{{ $yr }}">
                    <div class="flex items-center justify-between px-3 py-2 gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $ins->isAllOk() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $ins->isAllOk() ? '✓ Semua OK' : '✗ Ada Masalah' }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $ins->inspected_at->format('d M Y') }}</span>
                        </div>
                        @auth
                        <form action="{{ route('hydrant.inspeksi.destroy', $ins->id) }}" method="POST" onsubmit="return confirm('Hapus?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-gray-300 hover:text-red-500 transition p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        @endauth
                    </div>
                    <div class="grid grid-cols-3 gap-x-2 gap-y-1 px-3 pb-2.5 text-xs">
                        @foreach(['Box'=>$ins->item_01_kondisi_box,'Akses'=>$ins->item_02_akses_bebas,'Nozzle'=>$ins->item_03_nozzle,'Selang'=>$ins->item_04_selang,'Valve'=>$ins->item_05_valve,'Coupling'=>$ins->item_06_coupling,'Kunci'=>$ins->item_07_kunci,'Pillar'=>$ins->item_08_pillar,'Tekanan'=>$ins->item_09_tekanan,'Hose Rack'=>$ins->item_10_hose_rack,'Pompa'=>$ins->item_11_pompa] as $name => $val)
                        <div class="flex items-center gap-1">
                            <span class="font-bold {{ $val==='OK' ? 'text-green-500' : 'text-red-500' }}">{{ $val==='OK' ? '✓' : '✗' }}</span>
                            <span class="text-gray-500">{{ $name }}</span>
                        </div>
                        @endforeach
                    </div>
                    @if($ins->notes || $ins->inspector)
                    <div class="border-t border-gray-100 px-3 py-1.5 flex justify-between gap-2 text-xs text-gray-400">
                        @if($ins->notes)<p class="italic truncate">{{ $ins->notes }}</p>@endif
                        @if($ins->inspector)<p class="whitespace-nowrap shrink-0">{{ $ins->inspector->username }}</p>@endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @guest
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center space-y-3">
            <p class="text-sm text-yellow-800">Login untuk menambah inspeksi.</p>
            <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}"
               class="inline-block bg-green-700 hover:bg-green-800 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition">
               Login
            </a>
        </div>
        @endguest
    </div>

    {{-- ═══════════════════════════════════ --}}
    {{-- TAB: MAINTENANCE --}}
    {{-- ═══════════════════════════════════ --}}
    <div id="tab-maintenance" class="hidden">

        @auth
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-4">
            <button onclick="toggleForm('form-maintenance')"
                    class="w-full flex items-center justify-between px-5 py-3 bg-orange-500 text-white hover:bg-orange-600 transition">
                <span class="font-semibold text-sm">+ Tambah Maintenance</span>
                <svg id="icon-form-maintenance" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="form-maintenance" class="hidden p-5 bg-orange-50 border-t border-orange-100">
                <form action="{{ route('hydrant.maintenance.store', $hydrant->code) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Tanggal *</label>
                            <input type="date" name="maintenance_date" required value="{{ old('maintenance_date', date('Y-m-d')) }}"
                                   class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Jenis *</label>
                            <select name="maintenance_type" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 bg-white">
                                <option value="">-- Pilih --</option>
                                @foreach(\App\Models\HydrantMaintenance::$types as $type)
                                <option value="{{ $type }}" {{ old('maintenance_type')===$type?'selected':'' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="text" name="technician" value="{{ old('technician') }}" placeholder="Teknisi / Petugas (opsional)"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400">
                    <textarea name="notes" rows="2" placeholder="Catatan (opsional)"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-orange-400">{{ old('notes') }}</textarea>
                    <div>
                        <label class="block text-xs font-semibold text-orange-600 mb-1">📷 Foto Bukti <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-400 mb-1.5">Wajib ambil foto langsung dari kamera — tidak bisa dari galeri.</p>
                        <input type="file" name="photo" accept="image/*" capture="environment" required
                               class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200"
                               onchange="previewPhoto(this, 'preview-maint-hyd')">
                        <img id="preview-maint-hyd" src="" alt="" class="hidden mt-2 rounded-lg max-h-36 w-auto border border-gray-200">
                    </div>
                    <button type="submit"
                            class="w-full bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold py-2 rounded-lg transition">
                        Simpan Maintenance
                    </button>
                </form>
            </div>
        </div>
        @endauth

        {{-- Riwayat Maintenance --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="bg-orange-500 px-5 py-3 text-white flex items-center justify-between">
                <p class="font-bold text-sm">Riwayat Maintenance</p>
                <span class="bg-orange-400 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $hydrant->maintenances->count() }}</span>
            </div>
            @if($hydrant->maintenances->isEmpty())
            <div class="py-10 text-center"><p class="text-gray-400 text-sm">Belum ada riwayat maintenance.</p></div>
            @else
            @if($mtYears->count() > 1)
            <div class="px-4 pt-3 pb-1 flex gap-2 flex-wrap">
                <button onclick="filterYear('maintenance','all')" id="maintenance-year-all"
                        class="year-btn-maintenance px-3 py-1 rounded-full text-xs font-semibold bg-orange-500 text-white transition">Semua</button>
                @foreach($mtYears as $y)
                <button onclick="filterYear('maintenance','{{ $y }}')" id="maintenance-year-{{ $y }}"
                        class="year-btn-maintenance px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-orange-100 transition">{{ $y }}</button>
                @endforeach
            </div>
            @endif
            <div class="p-4 space-y-2" id="list-maintenance">
                @php $prevMonth = null; @endphp
                @foreach($hydrant->maintenances as $mt)
                @php $curMonth = $mt->maintenance_date->format('F Y'); $yr = $mt->maintenance_date->format('Y'); @endphp
                @if($curMonth !== $prevMonth)
                @php $prevMonth = $curMonth; @endphp
                <div class="pt-1 pb-0.5 maintenance-row" data-year="{{ $yr }}">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $curMonth }}</p>
                </div>
                @endif
                @php $typeColor = match($mt->maintenance_type) {
                    'Inspeksi Rutin'      => ['dot'=>'bg-blue-500',   'badge'=>'bg-blue-100 text-blue-700'],
                    'Penggantian Selang',
                    'Penggantian Nozzle',
                    'Penggantian Kopling' => ['dot'=>'bg-orange-500', 'badge'=>'bg-orange-100 text-orange-700'],
                    'Perbaikan Valve',
                    'Perbaikan Pompa',
                    'Perbaikan'           => ['dot'=>'bg-red-500',    'badge'=>'bg-red-100 text-red-700'],
                    default               => ['dot'=>'bg-gray-400',   'badge'=>'bg-gray-100 text-gray-600'],
                }; @endphp
                <div class="flex items-start gap-3 maintenance-row" data-year="{{ $yr }}">
                    <div class="shrink-0 w-7 h-7 rounded-full {{ $typeColor['dot'] }} flex items-center justify-center shadow-sm mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="flex-1 bg-gray-50 rounded-xl p-3 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $typeColor['badge'] }}">{{ $mt->maintenance_type }}</span>
                                <span class="text-xs text-gray-500">{{ $mt->maintenance_date->format('d M Y') }}</span>
                            </div>
                            @auth
                            <form action="{{ route('hydrant.maintenance.destroy', $mt->id) }}" method="POST" onsubmit="return confirm('Hapus?')" class="inline shrink-0">
                                @csrf @method('DELETE')
                                <button class="text-gray-300 hover:text-red-500 transition p-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endauth
                        </div>
                        @if($mt->technician)<p class="text-xs text-gray-500 mt-1">Teknisi: {{ $mt->technician }}</p>@endif
                        @if($mt->notes)<p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $mt->notes }}</p>@endif
                        @if($mt->performer)<p class="text-xs text-gray-400 mt-1">Oleh: {{ $mt->performer->username }}</p>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @guest
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center space-y-3">
            <p class="text-sm text-yellow-800">Login untuk menambah maintenance.</p>
            <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}"
               class="inline-block bg-green-700 hover:bg-green-800 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition">Login</a>
        </div>
        @endguest
    </div>

    {{-- Footer --}}
    <div class="text-center mt-6 space-y-1">
        @auth
        <a href="{{ route('hydrant.index') }}" class="text-sm text-green-700 hover:underline">← Daftar Hydrant</a>
        @endauth
        <p class="text-gray-400 text-xs">&copy; {{ date('Y') }} SRP — Hydrant Management</p>
    </div>

</div>
</div>

<script>
function switchTab(tab) {
    ['info','inspeksi','maintenance'].forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tab-btn-' + t);
        if (t === tab) {
            btn.classList.add('bg-green-700','text-white','shadow-sm');
            btn.classList.remove('text-gray-500');
        } else {
            btn.classList.remove('bg-green-700','text-white','shadow-sm');
            btn.classList.add('text-gray-500');
        }
    });
    sessionStorage.setItem('hydrant-tab-{{ $hydrant->code }}', tab);
}
(function() {
    const saved = sessionStorage.getItem('hydrant-tab-{{ $hydrant->code }}');
    if (saved && saved !== 'info') switchTab(saved);
})();

function toggleForm(id) {
    const el   = document.getElementById(id);
    const icon = document.getElementById('icon-form-' + id.replace('form-',''));
    el.classList.toggle('hidden');
    icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

function previewPhoto(input, previewId) {
    const img = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; img.classList.remove('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}

function filterYear(section, year) {
    document.querySelectorAll('.' + section + '-row').forEach(row => {
        row.style.display = (year === 'all' || row.dataset.year === year) ? '' : 'none';
    });
    const active   = section === 'inspeksi' ? ['bg-green-700','text-white'] : ['bg-orange-500','text-white'];
    const inactive = ['bg-gray-100','text-gray-600'];
    document.querySelectorAll('.year-btn-' + section).forEach(btn => {
        btn.classList.remove(...active, ...inactive);
        btn.classList.add(...inactive);
    });
    const activeBtn = document.getElementById(section + '-year-' + year);
    if (activeBtn) { activeBtn.classList.remove(...inactive); activeBtn.classList.add(...active); }
}
</script>
@endsection
