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