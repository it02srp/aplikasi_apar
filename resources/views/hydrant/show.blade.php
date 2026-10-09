@extends('layouts.app')
@section('title', 'Hydrant ' . $hydrant->code)
@section('content')

@php $latest = $hydrant->latestInspection; @endphp

<div class="max-w-3xl mx-auto space-y-4">

    {{-- Flash --}}
    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Color bar status --}}
        <div class="h-1.5 w-full
            @if($hydrant->condition == 'Good') bg-green-500
            @elseif($hydrant->condition == 'Needs Attention') bg-yellow-400
            @else bg-red-500 @endif">
        </div>

        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4 justify-between">
                {{-- Info kiri --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $hydrant->code }}</h1>
                        @if($hydrant->condition == 'Good')
                            <span class="px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Good</span>
                        @elseif($hydrant->condition == 'Needs Attention')
                            <span class="px-2.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Needs Attention</span>
                        @else
                            <span class="px-2.5 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">Damaged</span>
                        @endif
                    </div>
                    <p class="text-gray-500 text-sm flex items-center gap-1.5 mb-3">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $hydrant->location }}
                    </p>

                    {{-- Inspeksi terakhir --}}
                    <div class="inline-flex flex-wrap items-center gap-2 text-sm
                        @if(!$latest) text-orange-600
                        @elseif($latest->isAllOk()) text-green-700
                        @else text-red-700 @endif">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @if($latest)
                            <span>Inspeksi terakhir: <strong>{{ $latest->inspected_at->format('d/m/Y') }}</strong></span>
                            <span class="text-gray-400 text-xs">({{ $latest->inspected_at->diffForHumans() }})</span>
                            @if($latest->isAllOk())
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full font-medium">Semua OK</span>
                            @else
                                <span class="bg-red-100 text-red-700 text-xs px-2 py-0.5 rounded-full font-medium">Ada Kendala</span>
                            @endif
                        @else
                            <span class="font-medium">Belum pernah diinspeksi</span>
                        @endif
                    </div>
                </div>

                {{-- QR + actions kanan --}}
                <div class="flex sm:flex-col items-center gap-3 sm:gap-2 sm:shrink-0">
                    <div class="p-2 border-2 border-dashed border-gray-200 rounded-xl">
                        {!! QrCode::size(100)->generate(route('hydrant.show', $hydrant->code)) !!}
                    </div>
                    <div class="flex flex-col gap-1.5 w-full sm:w-auto">
                        <a href="{{ route('hydrant.print', $hydrant->code) }}" target="_blank"
                            class="text-center text-xs text-blue-600 hover:text-blue-800 font-medium py-1 px-3 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100">
                            Print QR
                        </a>
                        @auth
                        <a href="{{ route('hydrant.edit', $hydrant->code) }}"
                            class="text-center text-xs text-yellow-700 hover:text-yellow-900 font-medium py-1 px-3 border border-yellow-200 rounded-lg bg-yellow-50 hover:bg-yellow-100">
                            Edit
                        </a>
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Spesifikasi --}}
            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-xs text-gray-400">Panjang Selang</p>
                    <p class="font-semibold text-gray-800 text-sm">{{ $hydrant->hose_length }} m</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400">PIC</p>
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $hydrant->responsible_person ?? '-' }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-400">Catatan</p>
                    <p class="text-gray-700 text-sm truncate">{{ $hydrant->notes ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Tambah Inspeksi --}}
    @auth
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <button type="button" onclick="toggleForm()" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="font-semibold text-gray-800">Tambah Inspeksi Baru</span>
            </div>
            <svg id="formIcon" class="w-5 h-5 text-gray-400 transition-transform duration-200" style="transform:rotate(180deg)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div id="inspForm" class="border-t border-gray-100">
            <form action="{{ route('hydrant.inspeksi.store', $hydrant->code) }}" method="POST" class="p-4 sm:p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Periode <span class="text-red-500">*</span></label>
                        <input type="month" name="periode" required value="{{ old('periode', date('Y-m')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="inspected_at" required value="{{ old('inspected_at', date('Y-m-d')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                @php
                $items = [
                    'item_01_kondisi_box' => 'Kondisi fisik Box Hydrant bersih & terawat',
                    'item_02_akses_bebas' => 'Akses menuju Box bebas/tidak terhalang',
                    'item_03_nozzle'      => 'Nozzle kondisi baik, bersih, tidak retak',
                    'item_04_selang'      => 'Selang (Fire Hose) rapi & tidak bocor',
                    'item_05_valve'       => 'Kran (Valve) berfungsi baik & tidak macet',
                    'item_06_coupling'    => 'Coupling selang presisi & karet seal utuh',
                    'item_07_kunci'       => 'Kunci pembuka/Tuas tersedia di tempat',
                    'item_08_pillar'      => 'Hydrant Pillar tidak berkarat & tidak bocor',
                    'item_09_tekanan'     => 'Tekanan air stabil (Min. 4.42 Bar / 100 PSI)',
                    'item_10_hose_rack'   => 'Kondisi fisik Hose Rack baik & fungsional',
                    'item_11_pompa'       => 'Pompa otomatis (Jockey, Electric, Diesel) standby',
                ];
                @endphp

                <div class="space-y-2">
                    @foreach($items as $key => $label)
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 sm:px-4 py-3">
                        <span class="text-sm text-gray-700 flex-1 pr-3">{{ $loop->iteration }}. {{ $label }}</span>
                        <div class="flex items-center gap-4 shrink-0">
                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="radio" name="{{ $key }}" value="OK" {{ old($key, 'OK') === 'OK' ? 'checked' : '' }}
                                    class="w-4 h-4 accent-green-600">
                                <span class="text-xs font-bold text-green-600">OK</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="radio" name="{{ $key }}" value="NOT OK" {{ old($key) === 'NOT OK' ? 'checked' : '' }}
                                    class="w-4 h-4 accent-red-500">
                                <span class="text-xs font-bold text-red-500">NOT OK</span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold py-3 rounded-lg text-sm transition-colors">
                    Simpan Inspeksi
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center space-y-3">
        <p class="text-sm text-gray-600">Login untuk melakukan inspeksi pada hydrant ini.</p>
        <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}"
            class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
            Login untuk Inspeksi
        </a>
    </div>
    @endauth

    {{-- History Maintenance --}}
    @auth
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <button type="button" onclick="toggleMaintenance()" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="font-semibold text-gray-800">History Maintenance</span>
                @if($hydrant->latestMaintenance)
                    <span class="text-xs text-gray-400">— Terakhir: {{ $hydrant->latestMaintenance->maintenance_date->format('d/m/Y') }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($hydrant->maintenances->count() > 0)
                    <span class="bg-orange-100 text-orange-600 text-xs font-medium px-2 py-0.5 rounded-full">{{ $hydrant->maintenances->count() }}</span>
                @endif
                <svg id="maintenanceIcon" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div id="maintenanceSection" class="hidden border-t border-gray-100">
            {{-- Form tambah maintenance --}}
            <form action="{{ route('hydrant.maintenance.store', $hydrant->code) }}" method="POST" class="p-4 sm:p-5 space-y-3 bg-gray-50 border-b border-gray-100">
                @csrf
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tambah Catatan Maintenance</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="maintenance_date" required value="{{ old('maintenance_date', date('Y-m-d')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis <span class="text-red-500">*</span></label>
                        <select name="maintenance_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 bg-white">
                            <option value="">-- Pilih --</option>
                            @foreach(\App\Models\HydrantMaintenance::$types as $type)
                                <option value="{{ $type }}" {{ old('maintenance_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Teknisi</label>
                    <input type="text" name="technician" value="{{ old('technician') }}" placeholder="Nama teknisi (opsional)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Detail pekerjaan maintenance..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 resize-none">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white font-medium px-5 py-2 rounded-lg text-sm transition-colors">
                    Simpan Maintenance
                </button>
            </form>

            {{-- Daftar riwayat maintenance --}}
            <div class="divide-y divide-gray-50">
                @forelse($hydrant->maintenances as $mt)
                <div class="flex items-start justify-between px-4 sm:px-5 py-3.5">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-orange-400 mt-2 shrink-0"></div>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">{{ $mt->maintenance_type }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $mt->maintenance_date->format('d/m/Y') }}
                                @if($mt->technician) &bull; {{ $mt->technician }} @endif
                                @if($mt->performer) &bull; oleh {{ $mt->performer->username }} @endif
                            </p>
                            @if($mt->notes)
                                <p class="text-xs text-gray-600 mt-1 bg-gray-50 rounded px-2 py-1">{{ $mt->notes }}</p>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('hydrant.maintenance.destroy', $mt->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')" class="shrink-0 ml-2">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-sm text-gray-400">Belum ada catatan maintenance.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endauth

    {{-- Riwayat Inspeksi --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <button type="button" onclick="toggleRiwayat()" class="w-full flex items-center justify-between p-4 sm:p-5 text-left hover:bg-gray-50 transition-colors">
            <span class="font-semibold text-gray-800">Riwayat Inspeksi</span>
            <div class="flex items-center gap-2">
                @if($hydrant->inspections->count() > 0)
                    <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-full">{{ $hydrant->inspections->count() }}</span>
                @endif
                <svg id="riwayatIcon" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div id="riwayatList" class="hidden border-t border-gray-100 divide-y divide-gray-50">
            @forelse($hydrant->inspections as $ins)
            <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                <div>
                    <p class="font-medium text-gray-800 text-sm">{{ $ins->inspected_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Periode {{ $ins->periode }} &bull; {{ $ins->inspector->username ?? '-' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($ins->isAllOk())
                        <span class="text-xs font-medium text-green-700 bg-green-100 px-2 py-0.5 rounded-full">Semua OK</span>
                    @else
                        <span class="text-xs font-medium text-red-700 bg-red-100 px-2 py-0.5 rounded-full">Ada Kendala</span>
                    @endif
                    @auth
                    <form action="{{ route('hydrant.inspeksi.destroy', $ins->id) }}" method="POST" onsubmit="return confirm('Hapus inspeksi ini?')">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endauth
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Belum ada riwayat inspeksi.</div>
            @endforelse
        </div>
    </div>

    @auth
    <div class="flex gap-2 pb-2">
        <a href="{{ route('hydrant.index') }}" class="flex-1 text-center px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 font-medium">
            ← Kembali
        </a>
        <a href="{{ route('hydrant.print-all') }}" target="_blank" class="flex-1 text-center px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 font-medium">
            Print QR Semua
        </a>
    </div>
    @endauth

</div>

<script>
function toggleForm() {
    const el = document.getElementById('inspForm');
    const icon = document.getElementById('formIcon');
    const hidden = el.classList.toggle('hidden');
    icon.style.transform = hidden ? '' : 'rotate(180deg)';
}
function toggleMaintenance() {
    const el = document.getElementById('maintenanceSection');
    const icon = document.getElementById('maintenanceIcon');
    const hidden = el.classList.toggle('hidden');
    icon.style.transform = hidden ? '' : 'rotate(180deg)';
}
function toggleRiwayat() {
    const el = document.getElementById('riwayatList');
    const icon = document.getElementById('riwayatIcon');
    const hidden = el.classList.toggle('hidden');
    icon.style.transform = hidden ? '' : 'rotate(180deg)';
}
@if($errors->any() || session('error'))
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('inspForm');
    if (el && el.classList.contains('hidden')) toggleForm();
});
@endif
</script>
@endsection
