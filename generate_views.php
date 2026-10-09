<?php

$dir = __DIR__ . '/resources/views/hydrant';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$index = <<<'HTML'
@extends('layouts.app')
@section('title', 'Data Hydrant')
@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <h2 class="text-lg font-semibold text-gray-800">Daftar Hydrant</h2>
        <a href="{{ route('hydrant.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            + Tambah Hydrant
        </a>
    </div>
    
    <div class="p-4 sm:p-6 bg-gray-50 border-b border-gray-200">
        <form method="GET" action="{{ route('hydrant.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau lokasi..." class="px-4 py-2 border rounded-lg text-sm w-full sm:w-64">
            <select name="condition" class="px-4 py-2 border rounded-lg text-sm w-full sm:w-48">
                <option value="">Semua Kondisi</option>
                <option value="Good" {{ request('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                <option value="Needs Attention" {{ request('condition') == 'Needs Attention' ? 'selected' : '' }}>Needs Attention</option>
                <option value="Damaged" {{ request('condition') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
            </select>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
            @if(request('search') || request('condition'))
                <a href="{{ route('hydrant.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50 text-center">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 border-b text-gray-700">
                <tr>
                    <th class="p-4 font-semibold">Kode</th>
                    <th class="p-4 font-semibold">Lokasi</th>
                    <th class="p-4 font-semibold">Selang (m)</th>
                    <th class="p-4 font-semibold">Kondisi</th>
                    <th class="p-4 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($hydrants as $h)
                <tr class="hover:bg-gray-50">
                    <td class="p-4 font-medium text-gray-900">{{ $h->code }}</td>
                    <td class="p-4">{{ $h->location }}</td>
                    <td class="p-4">{{ $h->hose_length }} m</td>
                    <td class="p-4">
                        @if($h->condition == 'Good')
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Good</span>
                        @elseif($h->condition == 'Needs Attention')
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Needs Attention</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Damaged</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <div class="flex gap-2">
                            <a href="{{ route('hydrant.show', $h->code) }}" class="text-blue-600 hover:text-blue-800" title="Detail">Lihat</a>
                            <a href="{{ route('hydrant.edit', $h->code) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($hydrants->hasPages())
    <div class="p-4 border-t">
        {{ $hydrants->links() }}
    </div>
    @endif
</div>
@endsection
HTML;

$create = <<<'HTML'
@extends('layouts.app')
@section('title', 'Tambah Hydrant')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Tambah Data Hydrant</h2>
    </div>
    <form action="{{ route('hydrant.store') }}" method="POST" class="p-4 sm:p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Hydrant</label>
            <input type="text" value="{{ $nextCode }}" disabled class="w-full border-gray-300 bg-gray-100 rounded-lg px-4 py-2 text-sm text-gray-500">
            <p class="text-xs text-gray-400 mt-1">Kode otomatis di-generate.</p>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
            <input type="text" name="location" value="{{ old('location') }}" required class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Panjang Selang <span class="text-red-500">*</span></label>
                <select name="hose_length" required class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                    <option value="30" {{ old('hose_length') == '30' ? 'selected' : '' }}>30 Meter</option>
                    <option value="20" {{ old('hose_length') == '20' ? 'selected' : '' }}>20 Meter</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Awal <span class="text-red-500">*</span></label>
                <select name="condition" required class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                    <option value="Good" {{ old('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                    <option value="Needs Attention" {{ old('condition') == 'Needs Attention' ? 'selected' : '' }}>Needs Attention</option>
                    <option value="Damaged" {{ old('condition') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
            <input type="text" name="responsible_person" value="{{ old('responsible_person') }}" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-4 flex justify-end gap-3">
            <a href="{{ route('hydrant.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">Batal</a>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">Simpan Data</button>
        </div>
    </form>
</div>
@endsection
HTML;

$edit = <<<'HTML'
@extends('layouts.app')
@section('title', 'Edit Hydrant')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Edit Data Hydrant</h2>
    </div>
    <form action="{{ route('hydrant.update', $hydrant->code) }}" method="POST" class="p-4 sm:p-6 space-y-4">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Hydrant</label>
            <input type="text" value="{{ $hydrant->code }}" disabled class="w-full border-gray-300 bg-gray-100 rounded-lg px-4 py-2 text-sm text-gray-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
            <input type="text" name="location" value="{{ old('location', $hydrant->location) }}" required class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500 focus:border-green-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Panjang Selang <span class="text-red-500">*</span></label>
                <select name="hose_length" required class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                    <option value="30" {{ old('hose_length', $hydrant->hose_length) == '30' ? 'selected' : '' }}>30 Meter</option>
                    <option value="20" {{ old('hose_length', $hydrant->hose_length) == '20' ? 'selected' : '' }}>20 Meter</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Awal <span class="text-red-500">*</span></label>
                <select name="condition" required class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                    <option value="Good" {{ old('condition', $hydrant->condition) == 'Good' ? 'selected' : '' }}>Good</option>
                    <option value="Needs Attention" {{ old('condition', $hydrant->condition) == 'Needs Attention' ? 'selected' : '' }}>Needs Attention</option>
                    <option value="Damaged" {{ old('condition', $hydrant->condition) == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
            <input type="text" name="responsible_person" value="{{ old('responsible_person', $hydrant->responsible_person) }}" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="3" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">{{ old('notes', $hydrant->notes) }}</textarea>
        </div>

        <div class="pt-4 flex justify-between gap-3 border-t mt-4">
            <button type="button" onclick="if(confirm('Yakin hapus hydrant ini?')) document.getElementById('form-del').submit()" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium transition-colors">Hapus</button>
            <div class="flex gap-2">
                <a href="{{ route('hydrant.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">Update Data</button>
            </div>
        </div>
    </form>

    <form id="form-del" action="{{ route('hydrant.destroy', $hydrant->code) }}" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
</div>
@endsection
HTML;

$show = <<<'HTML'
@extends('layouts.app')
@section('title', 'Detail Hydrant')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-2xl font-bold text-gray-900">{{ $hydrant->code }}</h1>
                @if($hydrant->condition == 'Good')
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Good</span>
                @elseif($hydrant->condition == 'Needs Attention')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Needs Attention</span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Damaged</span>
                @endif
            </div>
            <p class="text-gray-600 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ $hydrant->location }}
            </p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <a href="{{ route('hydrant.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50 flex-1 text-center">Kembali</a>
            <a href="{{ route('hydrant.edit', $hydrant->code) }}" class="px-4 py-2 border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-sm font-medium flex-1 text-center">Edit</a>
        </div>
    </div>

    <div x-data="{ tab: sessionStorage.getItem('hydrantTab') || 'info' }" x-init="$watch('tab', val => sessionStorage.setItem('hydrantTab', val))">
        <div class="flex gap-4 border-b border-gray-200 mb-6">
            <button @click="tab = 'info'" :class="tab === 'info' ? 'border-green-600 text-green-700 font-medium' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-3 border-b-2 transition-colors">
                Info Hydrant
            </button>
            <button @click="tab = 'inspeksi'" :class="tab === 'inspeksi' ? 'border-green-600 text-green-700 font-medium' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-3 border-b-2 transition-colors">
                Riwayat Inspeksi
            </button>
        </div>

        <div x-show="tab === 'info'" class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-gray-800 font-medium mb-4 pb-2 border-b">Spesifikasi Unit</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex">
                        <dt class="w-1/3 text-gray-500">Panjang Selang</dt>
                        <dd class="w-2/3 font-medium text-gray-900">{{ $hydrant->hose_length }} Meter</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-1/3 text-gray-500">PIC</dt>
                        <dd class="w-2/3 font-medium text-gray-900">{{ $hydrant->responsible_person ?? '-' }}</dd>
                    </div>
                    <div class="flex">
                        <dt class="w-1/3 text-gray-500">Catatan</dt>
                        <dd class="w-2/3 text-gray-900">{{ $hydrant->notes ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div x-show="tab === 'inspeksi'" x-cloak class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b font-medium text-gray-700">Input Inspeksi Baru</div>
                <form action="{{ route('hydrant.inspeksi.store', $hydrant->code) }}" method="POST" class="p-4 sm:p-6 space-y-4">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode (Bulan-Tahun) <span class="text-red-500">*</span></label>
                            <input type="month" name="periode" required value="{{ date('Y-m') }}" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Inspeksi <span class="text-red-500">*</span></label>
                            <input type="date" name="inspected_at" required value="{{ date('Y-m-d') }}" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                        </div>
                    </div>

                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-700 border-b">
                                <tr>
                                    <th class="px-4 py-3">Item Pemeriksaan / Kriteria</th>
                                    <th class="px-4 py-3 w-32 text-center">OK</th>
                                    <th class="px-4 py-3 w-32 text-center">NOT OK</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @php
                                $items = [
                                    'item_01_kondisi_box' => '1. Kondisi fisik Box Hydrant bersih & terawat',
                                    'item_02_akses_bebas' => '2. Akses menuju Box bebas/tidak terhalang',
                                    'item_03_nozzle' => '3. Nozzle kondisi baik, bersih, tidak retak',
                                    'item_04_selang' => '4. Selang (Fire Hose) rapi & tidak bocor',
                                    'item_05_valve' => '5. Kran (Valve) berfungsi baik & tidak macet',
                                    'item_06_coupling' => '6. Coupling selang presisi & karet seal utuh',
                                    'item_07_kunci' => '7. Kunci pembuka/Tuas tersedia di tempat',
                                    'item_08_pillar' => '8. Hydrant Pillar tidak berkarat & tidak bocor',
                                    'item_09_tekanan' => '9. Tekanan air stabil pada pressure gauge (Min. 4.42 Bar / 100 PSI)',
                                    'item_10_hose_rack' => '10. Kondisi fisik Hose Rack baik & fungsional',
                                    'item_11_pompa' => '11. Pompa otomatis (Jockey, Electric, Diesel) standby & normal'
                                ];
                                @endphp
                                @foreach($items as $key => $label)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $label }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="radio" name="{{ $key }}" value="OK" checked class="text-green-600 focus:ring-green-500 w-4 h-4">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="radio" name="{{ $key }}" value="NOT OK" class="text-red-600 focus:ring-red-500 w-4 h-4">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
                        <textarea name="notes" rows="2" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500"></textarea>
                    </div>

                    <div class="text-right pt-2">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Simpan Inspeksi</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-4 border-b">
                    <h3 class="font-medium text-gray-800">Riwayat Inspeksi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="p-4">Periode</th>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Kondisi (11 Item)</th>
                                <th class="p-4">Inspektor</th>
                                <th class="p-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($hydrant->inspections as $ins)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 font-medium">{{ $ins->periode }}</td>
                                <td class="p-4">{{ $ins->inspected_at->format('d/m/Y') }}</td>
                                <td class="p-4">
                                    @if($ins->isAllOk())
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">Semua OK</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">Ada NOT OK</span>
                                    @endif
                                </td>
                                <td class="p-4">{{ $ins->inspector->username ?? '-' }}</td>
                                <td class="p-4">
                                    <form action="{{ route('hydrant.inspeksi.destroy', $ins->id) }}" method="POST" onsubmit="return confirm('Hapus inspeksi ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="p-8 text-center text-gray-500">Belum ada data inspeksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Alpine JS for Tabs -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
HTML;

$inspection = <<<'HTML'
@extends('layouts.app')
@section('title', 'Semua Inspeksi Hydrant')
@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Riwayat Inspeksi Semua Hydrant</h2>
    </div>
    
    <div class="p-4 sm:p-6 bg-gray-50 border-b border-gray-200">
        <form method="GET" action="{{ route('hydrant.inspeksi.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/lokasi hydrant..." class="px-4 py-2 border rounded-lg text-sm w-full sm:w-64">
            <input type="month" name="periode" value="{{ request('periode') }}" class="px-4 py-2 border rounded-lg text-sm">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
            @if(request('search') || request('periode'))
                <a href="{{ route('hydrant.inspeksi.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50 text-center">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 border-b text-gray-700">
                <tr>
                    <th class="p-4 font-semibold">Kode Hydrant</th>
                    <th class="p-4 font-semibold">Lokasi</th>
                    <th class="p-4 font-semibold">Periode</th>
                    <th class="p-4 font-semibold">Tanggal Inspeksi</th>
                    <th class="p-4 font-semibold">Hasil</th>
                    <th class="p-4 font-semibold">Inspektor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($inspections as $ins)
                <tr class="hover:bg-gray-50">
                    <td class="p-4 font-medium text-gray-900"><a href="{{ route('hydrant.show', $ins->hydrant->code) }}" class="text-blue-600 hover:underline">{{ $ins->hydrant->code }}</a></td>
                    <td class="p-4">{{ $ins->hydrant->location }}</td>
                    <td class="p-4">{{ $ins->periode }}</td>
                    <td class="p-4">{{ $ins->inspected_at->format('d/m/Y') }}</td>
                    <td class="p-4">
                        @if($ins->isAllOk())
                            <span class="text-green-600 font-medium">Semua OK</span>
                        @else
                            <span class="text-red-600 font-medium">Ada Kendala</span>
                        @endif
                    </td>
                    <td class="p-4">{{ $ins->inspector->username ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">Data inspeksi tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($inspections->hasPages())
    <div class="p-4 border-t">
        {{ $inspections->links() }}
    </div>
    @endif
</div>
@endsection
HTML;

file_put_contents($dir . '/index.blade.php', $index);
file_put_contents($dir . '/create.blade.php', $create);
file_put_contents($dir . '/edit.blade.php', $edit);
file_put_contents($dir . '/show.blade.php', $show);
file_put_contents($dir . '/inspection.blade.php', $inspection);

echo "View files generated successfully.\n";
