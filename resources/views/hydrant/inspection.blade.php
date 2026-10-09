@extends('layouts.app')
@section('title', 'Semua Inspeksi Hydrant')
@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <h2 class="text-lg font-semibold text-gray-800">Riwayat Inspeksi Semua Hydrant</h2>
        <div class="flex gap-2">
            <button onclick="document.getElementById('tambahModal').classList.remove('hidden')"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Inspeksi
            </button>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import Excel
            </button>
        </div>
    </div>

    {{-- Modal Tambah Inspeksi --}}
    <div id="tambahModal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-5 border-b flex justify-between items-center sticky top-0 bg-white z-10">
                <h3 class="font-semibold text-gray-800">Tambah Inspeksi Hydrant</h3>
                <button onclick="document.getElementById('tambahModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('hydrant.inspeksi.store.admin') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hydrant <span class="text-red-500">*</span></label>
                        <select name="hydrant_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 @error('hydrant_id') border-red-400 @enderror">
                            <option value="">-- Pilih Hydrant --</option>
                            @foreach($hydrants as $h)
                                <option value="{{ $h->id }}" {{ old('hydrant_id') == $h->id ? 'selected' : '' }}>
                                    {{ $h->code }} — {{ $h->location }}
                                </option>
                            @endforeach
                        </select>
                        @error('hydrant_id') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode <span class="text-red-500">*</span></label>
                        <input type="month" name="periode" required value="{{ old('periode', date('Y-m')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 @error('periode') border-red-400 @enderror">
                        @error('periode') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Inspeksi <span class="text-red-500">*</span></label>
                        <input type="date" name="inspected_at" required value="{{ old('inspected_at', date('Y-m-d')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 @error('inspected_at') border-red-400 @enderror">
                        @error('inspected_at') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Item Pemeriksaan <span class="text-red-500">*</span></p>
                    @php
                    $inspItems = [
                        'item_01_kondisi_box' => '1. Kondisi fisik Box Hydrant bersih & terawat',
                        'item_02_akses_bebas' => '2. Akses menuju Box bebas/tidak terhalang',
                        'item_03_nozzle'      => '3. Nozzle kondisi baik, bersih, tidak retak',
                        'item_04_selang'      => '4. Selang (Fire Hose) rapi & tidak bocor',
                        'item_05_valve'       => '5. Kran (Valve) berfungsi baik & tidak macet',
                        'item_06_coupling'    => '6. Coupling selang presisi & karet seal utuh',
                        'item_07_kunci'       => '7. Kunci pembuka/Tuas tersedia di tempat',
                        'item_08_pillar'      => '8. Hydrant Pillar tidak berkarat & tidak bocor',
                        'item_09_tekanan'     => '9. Tekanan air stabil (Min. 4.42 Bar / 100 PSI)',
                        'item_10_hose_rack'   => '10. Kondisi fisik Hose Rack baik & fungsional',
                        'item_11_pompa'       => '11. Pompa otomatis (Jockey, Electric, Diesel) standby & normal',
                    ];
                    @endphp
                    <div class="space-y-1.5">
                        @foreach($inspItems as $field => $label)
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                            <div class="flex items-center gap-4 shrink-0 ml-3">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="radio" name="{{ $field }}" value="OK" class="accent-green-600"
                                        {{ old($field, 'OK') === 'OK' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-green-700">OK</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="radio" name="{{ $field }}" value="NOT OK" class="accent-red-500"
                                        {{ old($field) === 'NOT OK' ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-red-600">NOT OK</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        Simpan Inspeksi
                    </button>
                    <button type="button" onclick="document.getElementById('tambahModal').classList.add('hidden')"
                        class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Alert success/error --}}
    @if(session('success'))
        <div class="mx-4 mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mx-4 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Modal Import --}}
    <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-5 border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Import Inspeksi dari Excel</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('hydrant.inspeksi.import') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
                @csrf
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
                    <p class="font-medium mb-1">Format Excel yang didukung:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Sheet bernama <strong>sunmary hydrant</strong></li>
                        <li>Baris 12 berisi tanggal inspeksi (format: dd-mm-yyyy)</li>
                        <li>Kolom C berisi nama lokasi hydrant</li>
                        <li>Setiap bulan terdiri dari 4 kolom: Nozel, Selang, Kopling, Pompa</li>
                        <li>Hydrant baru akan dibuat otomatis jika lokasi belum ada</li>
                    </ul>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih File Excel <span class="text-red-500">*</span></label>
                    <input type="file" name="file" accept=".xlsx,.xls" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                        Import Sekarang
                    </button>
                </div>
            </form>
        </div>
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
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('tambahModal').classList.remove('hidden');
    });
</script>
@endif
@endsection