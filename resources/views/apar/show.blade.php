@extends('layouts.guest')

@section('title', 'APAR ' . $apar->code)

@section('content')
@php
    $inspectionYears  = $apar->inspections->groupBy(fn($i) => $i->inspected_at->format('Y'))->keys()->sort()->reverse()->values();
    $maintenanceYears = $apar->maintenances->groupBy(fn($m) => $m->maintenance_date->format('Y'))->keys()->sort()->reverse()->values();
@endphp

<div class="min-h-screen bg-gray-50 py-6 px-4">
    <div class="max-w-lg mx-auto">

        {{-- Header --}}
        <div class="text-center mb-5">
            <span class="text-4xl">🔥</span>
            <h1 class="text-xl font-bold text-gray-800 mt-1">APAR Management</h1>
            <p class="text-gray-500 text-sm">PT Sinar Rimba Pasifik</p>
        </div>

        {{-- Status Badge --}}
        <div class="flex flex-wrap justify-center gap-2 mb-4">
            @if($apar->is_maintenance)
                <span class="inline-flex items-center gap-1.5 bg-orange-100 text-orange-700 border border-orange-300 font-bold px-4 py-1.5 rounded-full text-sm">
                    🔧 SEDANG MAINTENANCE
                </span>
            @elseif($apar->isExpired())
                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 border border-red-300 font-bold px-4 py-1.5 rounded-full text-sm">
                    ⚠️ KADALUARSA
                </span>
            @elseif($apar->isNearExpiry())
                <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 border border-yellow-300 font-bold px-4 py-1.5 rounded-full text-sm">
                    ⏳ HAMPIR KADALUARSA
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 border border-green-300 font-bold px-4 py-1.5 rounded-full text-sm">
                    ✅ KONDISI BAIK
                </span>
            @endif
        </div>

        {{-- ── Tab Navigation ── --}}
        <div class="flex bg-white rounded-2xl shadow-sm border border-gray-200 mb-4 p-1 gap-1">
            @foreach(['info' => 'Info APAR', 'inspeksi' => 'Pemeriksaan', 'maintenance' => 'Maintenance'] as $tab => $label)
            <button onclick="switchTab('{{ $tab }}')" id="tab-btn-{{ $tab }}"
                    class="flex-1 py-2 rounded-xl text-xs font-semibold transition-all tab-btn
                           {{ $tab === 'info' ? 'bg-green-700 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                @if($tab === 'inspeksi' && $apar->inspections->count())
                    <span class="ml-1 text-xs opacity-70">({{ $apar->inspections->count() }})</span>
                @elseif($tab === 'maintenance' && $apar->maintenances->count())
                    <span class="ml-1 text-xs opacity-70">({{ $apar->maintenances->count() }})</span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════ --}}
        {{-- TAB: INFO APAR --}}
        {{-- ════════════════════════════════════════ --}}
        <div id="tab-info">
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                {{-- Code Header --}}
                <div class="bg-green-700 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-200 uppercase tracking-widest">Kode APAR</p>
                        <p class="text-3xl font-mono font-bold mt-0.5">{{ $apar->code }}</p>
                    </div>
                    {{-- Tombol Maintenance (hanya admin) --}}
                    @auth
                    <form action="{{ route('apar.toggle-maintenance', $apar->code) }}" method="POST"
                          onsubmit="return confirm('{{ $apar->is_maintenance ? 'Tandai selesai maintenance?' : 'Tandai sedang maintenance?' }}')">
                        @csrf
                        @if($apar->is_maintenance)
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Selesai
                            </button>
                        @else
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-orange-400 hover:bg-orange-300 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Maintenance
                            </button>
                        @endif
                    </form>
                    @endauth
                </div>

                <div class="p-5 space-y-4">
                    @if($apar->is_maintenance && $apar->maintenance_started_at)
                    <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-2 text-xs text-orange-700">
                        Maintenance sejak: <strong>{{ $apar->maintenance_started_at->format('d M Y H:i') }}</strong>
                    </div>
                    @endif

                    {{-- Flash message --}}
                    @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-2 text-xs text-green-800">
                        {{ session('success') }}
                    </div>
                    @endif

                    {{-- Lokasi --}}
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Lokasi</p>
                        <p class="text-gray-800 font-semibold">{{ $apar->location }}</p>
                        @if($apar->building || $apar->floor || $apar->room)
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ collect([$apar->building, $apar->floor ? 'Lt.'.$apar->floor : null, $apar->room])->filter()->implode(' / ') }}
                        </p>
                        @endif
                    </div>

                    {{-- Info Grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Jenis</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->type }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Kapasitas</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->capacity }} {{ $apar->capacity_unit }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Tgl Produksi</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->manufacture_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Tgl Kadaluarsa</p>
                            <p class="text-sm font-semibold mt-0.5 {{ $apar->isExpired() ? 'text-red-600' : ($apar->isNearExpiry() ? 'text-yellow-600' : 'text-green-700') }}">
                                {{ $apar->expiry_date->format('d M Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Kondisi</p>
                            @php $condColor = match($apar->condition) { 'Good' => 'bg-green-100 text-green-700', 'Needs Attention' => 'bg-yellow-100 text-yellow-700', 'Replace' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mt-0.5 {{ $condColor }}">{{ $apar->condition }}</span>
                        </div>
                        @if($apar->responsible_person)
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Penanggung Jawab</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->responsible_person }}</p>
                        </div>
                        @endif
                    </div>

                    @if($apar->last_inspection_date || $apar->next_inspection_date)
                    <div class="border-t border-gray-100 pt-3 grid grid-cols-2 gap-3">
                        @if($apar->last_inspection_date)
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Inspeksi Terakhir</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->last_inspection_date->format('d M Y') }}</p>
                        </div>
                        @endif
                        @if($apar->next_inspection_date)
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Inspeksi Berikutnya</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->next_inspection_date->format('d M Y') }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($apar->notes)
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400 font-medium mb-1">Catatan</p>
                        <p class="text-sm text-gray-700">{{ $apar->notes }}</p>
                    </div>
                    @endif

                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400">Diperbarui: {{ $apar->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════ --}}
        {{-- TAB: PEMERIKSAAN BERKALA --}}
        {{-- ════════════════════════════════════════ --}}
        <div id="tab-inspeksi" class="hidden">

            {{-- Form tambah (admin, collapsible) --}}
            @auth
            <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-4">
                <button onclick="toggleForm('form-inspeksi')"
                        class="w-full flex items-center justify-between px-5 py-3 bg-yellow-600 text-white hover:bg-yellow-700 transition">
                    <span class="font-semibold text-sm">+ Tambah Pemeriksaan</span>
                    <svg id="icon-form-inspeksi" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="form-inspeksi" class="hidden p-5 bg-yellow-50 border-t border-yellow-100">
                    <p class="text-xs text-yellow-700 mb-3">Tanggal &amp; jam diambil otomatis saat disimpan.</p>
                    <form action="{{ route('apar.inspection.store', $apar->code) }}" method="POST" class="space-y-2">
                        @csrf
                        @php
                            $kondisiItems = ['kondisi_handle' => '1 Handle','kondisi_selang' => '2 Selang','kondisi_pin_kunci' => '3 Pin Kunci','kondisi_indikator' => '4 Indikator','kondisi_tabung' => '5 Tabung','kondisi_masa_apar' => '6 Masa Apar'];
                        @endphp
                        @foreach($kondisiItems as $field => $label)
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
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-yellow-400">{{ old('notes') }}</textarea>
                        <button type="submit"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold py-2 rounded-lg transition">
                            Simpan Pemeriksaan
                        </button>
                    </form>
                </div>
            </div>
            @endauth

            {{-- Riwayat --}}
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="bg-yellow-600 px-5 py-3 text-white flex items-center justify-between">
                    <p class="font-bold text-sm">Riwayat Pemeriksaan</p>
                    <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $apar->inspections->count() }}</span>
                </div>

                @if($apar->inspections->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-gray-400 text-sm">Belum ada riwayat pemeriksaan.</p>
                </div>
                @else

                {{-- Filter Tahun --}}
                @if($inspectionYears->count() > 1)
                <div class="px-4 pt-3 pb-1 flex gap-2 flex-wrap">
                    <button onclick="filterYear('inspeksi', 'all')" id="inspeksi-year-all"
                            class="year-btn-inspeksi px-3 py-1 rounded-full text-xs font-semibold bg-yellow-600 text-white transition">
                        Semua
                    </button>
                    @foreach($inspectionYears as $y)
                    <button onclick="filterYear('inspeksi', '{{ $y }}')" id="inspeksi-year-{{ $y }}"
                            class="year-btn-inspeksi px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-yellow-100 transition">
                        {{ $y }}
                    </button>
                    @endforeach
                </div>
                @endif

                <div class="p-4 space-y-2" id="list-inspeksi">
                    @php $prevMonth = null; @endphp
                    @foreach($apar->inspections as $inspection)
                    @php
                        $allOk = $inspection->isAllOk();
                        $curMonth = $inspection->inspected_at->format('F Y');
                        $yr = $inspection->inspected_at->format('Y');
                    @endphp

                    {{-- Pemisah bulan --}}
                    @if($curMonth !== $prevMonth)
                    @php $prevMonth = $curMonth; @endphp
                    <div class="pt-1 pb-0.5 inspection-row" data-year="{{ $yr }}">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $curMonth }}</p>
                    </div>
                    @endif

                    <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden inspection-row" data-year="{{ $yr }}">
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-3 py-2 gap-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $allOk ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $allOk ? '✓ OK' : '✗ Ada Masalah' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $inspection->inspected_at->format('d M Y') }}
                                    <span class="text-gray-400">{{ $inspection->inspected_at->format('H:i') }}</span>
                                </span>
                            </div>
                            @auth
                            <form action="{{ route('apar.inspection.destroy', $inspection->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 transition p-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endauth
                        </div>
                        {{-- Kondisi --}}
                        <div class="grid grid-cols-3 gap-x-2 gap-y-1 px-3 pb-2.5">
                            @foreach(['1 Handle' => $inspection->kondisi_handle, '2 Selang' => $inspection->kondisi_selang, '3 Pin Kunci' => $inspection->kondisi_pin_kunci, '4 Indikator' => $inspection->kondisi_indikator, '5 Tabung' => $inspection->kondisi_tabung, '6 Masa Apar' => $inspection->kondisi_masa_apar] as $name => $val)
                            <div class="flex items-center gap-1">
                                <span class="text-xs font-bold {{ $val === 'OK' ? 'text-green-500' : 'text-red-500' }}">{{ $val === 'OK' ? '✓' : '✗' }}</span>
                                <span class="text-xs text-gray-500">{{ $name }}</span>
                            </div>
                            @endforeach
                        </div>
                        @if($inspection->notes || $inspection->inspector)
                        <div class="border-t border-gray-100 px-3 py-1.5 flex items-center justify-between gap-2">
                            @if($inspection->notes)
                            <p class="text-xs text-gray-400 italic truncate">{{ $inspection->notes }}</p>
                            @endif
                            @if($inspection->inspector)
                            <p class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">{{ $inspection->inspector->username }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════ --}}
        {{-- TAB: MAINTENANCE --}}
        {{-- ════════════════════════════════════════ --}}
        <div id="tab-maintenance" class="hidden">

            {{-- Form tambah (admin, collapsible) --}}
            @auth
            <div class="bg-white rounded-2xl shadow-md overflow-hidden mb-4">
                <button onclick="toggleForm('form-maintenance')"
                        class="w-full flex items-center justify-between px-5 py-3 bg-green-700 text-white hover:bg-green-800 transition">
                    <span class="font-semibold text-sm">+ Tambah Record Maintenance</span>
                    <svg id="icon-form-maintenance" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="form-maintenance" class="hidden p-5 bg-green-50 border-t border-green-100">
                    <form action="{{ route('apar.maintenance.store', $apar->code) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-600 font-medium">Tanggal *</label>
                                <input type="date" name="maintenance_date" value="{{ old('maintenance_date', date('Y-m-d')) }}"
                                       class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600 font-medium">Berikutnya</label>
                                <input type="date" name="next_inspection_date" value="{{ old('next_inspection_date') }}"
                                       class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Jenis *</label>
                            <select name="maintenance_type" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                                <option value="">-- Pilih --</option>
                                @foreach(['Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya'] as $type)
                                <option value="{{ $type }}" {{ old('maintenance_type')===$type?'selected':'' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="text" name="technician" value="{{ old('technician') }}" placeholder="Teknisi / Petugas"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <textarea name="notes" rows="2" placeholder="Catatan (opsional)"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-green-500">{{ old('notes') }}</textarea>
                        <button type="submit"
                                class="w-full bg-green-700 hover:bg-green-800 text-white text-sm font-semibold py-2 rounded-lg transition">
                            Simpan Maintenance
                        </button>
                    </form>
                </div>
            </div>
            @endauth

            {{-- Riwayat --}}
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="bg-green-700 px-5 py-3 text-white flex items-center justify-between">
                    <p class="font-bold text-sm">Riwayat Maintenance</p>
                    <span class="bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $apar->maintenances->count() }}</span>
                </div>

                @if($apar->maintenances->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-gray-400 text-sm">Belum ada riwayat maintenance.</p>
                </div>
                @else

                {{-- Filter Tahun --}}
                @if($maintenanceYears->count() > 1)
                <div class="px-4 pt-3 pb-1 flex gap-2 flex-wrap">
                    <button onclick="filterYear('maintenance', 'all')" id="maintenance-year-all"
                            class="year-btn-maintenance px-3 py-1 rounded-full text-xs font-semibold bg-green-700 text-white transition">
                        Semua
                    </button>
                    @foreach($maintenanceYears as $y)
                    <button onclick="filterYear('maintenance', '{{ $y }}')" id="maintenance-year-{{ $y }}"
                            class="year-btn-maintenance px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-green-100 transition">
                        {{ $y }}
                    </button>
                    @endforeach
                </div>
                @endif

                <div class="p-4 space-y-2" id="list-maintenance">
                    @php $prevMonth = null; @endphp
                    @foreach($apar->maintenances as $m)
                    @php
                        $curMonth  = $m->maintenance_date->format('F Y');
                        $yr        = $m->maintenance_date->format('Y');
                        $typeColor = match($m->maintenance_type) {
                            'Inspeksi Rutin'       => ['dot'=>'bg-blue-500',   'badge'=>'bg-blue-100 text-blue-700'],
                            'Pengisian Ulang'      => ['dot'=>'bg-green-500',  'badge'=>'bg-green-100 text-green-700'],
                            'Penggantian Komponen' => ['dot'=>'bg-orange-500', 'badge'=>'bg-orange-100 text-orange-700'],
                            'Perbaikan'            => ['dot'=>'bg-red-500',    'badge'=>'bg-red-100 text-red-700'],
                            default                => ['dot'=>'bg-gray-400',   'badge'=>'bg-gray-100 text-gray-600'],
                        };
                    @endphp

                    {{-- Pemisah bulan --}}
                    @if($curMonth !== $prevMonth)
                    @php $prevMonth = $curMonth; @endphp
                    <div class="pt-1 pb-0.5 maintenance-row" data-year="{{ $yr }}">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $curMonth }}</p>
                    </div>
                    @endif

                    <div class="flex items-start gap-3 maintenance-row" data-year="{{ $yr }}">
                        {{-- Dot --}}
                        <div class="flex-shrink-0 w-7 h-7 rounded-full {{ $typeColor['dot'] }} flex items-center justify-center shadow-sm mt-0.5">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 bg-gray-50 rounded-xl p-3 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $typeColor['badge'] }}">{{ $m->maintenance_type }}</span>
                                    <span class="text-xs text-gray-500">{{ $m->maintenance_date->format('d M Y') }}</span>
                                </div>
                                @auth
                                <form action="{{ route('apar.maintenance.destroy', $m->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus?')" class="inline flex-shrink-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition p-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endauth
                            </div>
                            @if($m->technician)
                            <p class="text-xs text-gray-500 mt-1">Teknisi: {{ $m->technician }}</p>
                            @endif
                            @if($m->next_inspection_date)
                            <p class="text-xs mt-1">
                                <span class="text-gray-400">Berikutnya:</span>
                                <span class="font-semibold {{ $m->next_inspection_date->isPast() ? 'text-red-600' : 'text-green-700' }}">
                                    {{ $m->next_inspection_date->format('d M Y') }}
                                </span>
                            </p>
                            @endif
                            @if($m->notes)
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $m->notes }}</p>
                            @endif
                            @if($m->performer)
                            <p class="text-xs text-gray-400 mt-1">Oleh: {{ $m->performer->username }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6 space-y-1">
            <a href="{{ route('scan') }}" class="text-sm text-green-700 hover:underline">← Scan APAR lain</a>
            <p class="text-gray-400 text-xs">&copy; {{ date('Y') }} SRP — APAR Management</p>
        </div>

    </div>
</div>

<script>
// ── Tab switching ─────────────────────────────────────────────────────────────
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
    // Save active tab
    sessionStorage.setItem('apar-tab-{{ $apar->code }}', tab);
}

// Restore tab on load
(function() {
    const saved = sessionStorage.getItem('apar-tab-{{ $apar->code }}');
    if (saved && saved !== 'info') switchTab(saved);
})();

// ── Collapsible form ──────────────────────────────────────────────────────────
function toggleForm(id) {
    const el   = document.getElementById(id);
    const icon = document.getElementById('icon-form-' + id.replace('form-',''));
    el.classList.toggle('hidden');
    icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// ── Year filter ───────────────────────────────────────────────────────────────
function filterYear(section, year) {
    const rows = document.querySelectorAll('.' + section + '-row');
    rows.forEach(row => {
        row.style.display = (year === 'all' || row.dataset.year === year) ? '' : 'none';
    });
    // Update button styles
    const activeColor  = section === 'inspeksi' ? ['bg-yellow-600','text-white'] : ['bg-green-700','text-white'];
    const inactiveColor = ['bg-gray-100','text-gray-600'];
    document.querySelectorAll('.year-btn-' + section).forEach(btn => {
        btn.classList.remove(...activeColor, ...inactiveColor);
        btn.classList.add(...inactiveColor);
    });
    const activeBtn = document.getElementById(section + '-year-' + year);
    if (activeBtn) {
        activeBtn.classList.remove(...inactiveColor);
        activeBtn.classList.add(...activeColor);
    }
}

@if(session('success'))
    // Buka form jika ada redirect dari submit inspeksi
    const url = new URLSearchParams(window.location.search);
@endif
</script>
@endsection
