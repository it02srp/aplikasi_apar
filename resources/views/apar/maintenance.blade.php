@extends('layouts.app')

@section('title', 'History Maintenance')

@section('content')
<div class="space-y-4">

    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">History Maintenance APAR</h2>
            <p class="text-gray-400 text-sm mt-0.5">{{ $maintenances->total() }} record maintenance</p>
        </div>
        <button onclick="document.getElementById('modal-maintenance').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Maintenance
        </button>
    </div>

    {{-- Modal Tambah Maintenance --}}
    <div id="modal-maintenance"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Tambah Record Maintenance</h3>
                <button onclick="document.getElementById('modal-maintenance').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('apar.maintenance.store.admin') }}" method="POST" class="p-6 space-y-4">
                @csrf

                {{-- Pilih APAR --}}
                <div>
                    <label class="text-sm text-gray-700 font-medium block mb-1">APAR <span class="text-red-500">*</span></label>
                    <select name="apar_code" required id="select-apar"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('apar_code') border-red-400 @enderror">
                        <option value="">-- Pilih APAR --</option>
                        @foreach($aparList as $a)
                            <option value="{{ $a->code }}" {{ old('apar_code') === $a->code ? 'selected' : '' }}>
                                {{ $a->code }} — {{ $a->location }}
                            </option>
                        @endforeach
                    </select>
                    @error('apar_code')
                        <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Tanggal --}}
                    <div>
                        <label class="text-sm text-gray-700 font-medium block mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="maintenance_date" required
                               value="{{ old('maintenance_date', date('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('maintenance_date') border-red-400 @enderror">
                        @error('maintenance_date')
                            <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis --}}
                    <div>
                        <label class="text-sm text-gray-700 font-medium block mb-1">Jenis <span class="text-red-500">*</span></label>
                        <select name="maintenance_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('maintenance_type') border-red-400 @enderror">
                            <option value="">-- Pilih --</option>
                            @foreach(['Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya'] as $t)
                                <option value="{{ $t }}" {{ old('maintenance_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('maintenance_type')
                            <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Teknisi --}}
                <div>
                    <label class="text-sm text-gray-700 font-medium block mb-1">Teknisi / Petugas</label>
                    <input type="text" name="technician" value="{{ old('technician') }}"
                           placeholder="Nama teknisi atau petugas"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="text-sm text-gray-700 font-medium block mb-1">Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Deskripsi pekerjaan, temuan, hasil inspeksi, dll."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 bg-green-700 hover:bg-green-800 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                        Simpan
                    </button>
                    <button type="button"
                            onclick="document.getElementById('modal-maintenance').classList.add('hidden')"
                            class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium py-2.5 rounded-lg text-sm transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Alert: Overdue --}}
    @if($overdueInspection->isNotEmpty())
    <div class="bg-red-50 border border-red-200 rounded-xl overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-red-100 border-b border-red-200">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="font-bold text-red-700 text-sm">Inspeksi Terlambat — {{ $overdueInspection->count() }} APAR</p>
        </div>
        <div class="divide-y divide-red-100">
            @foreach($overdueInspection as $apar)
            @php $days = $apar->next_inspection_date->diffInDays(\Carbon\Carbon::today()); @endphp
            <div class="flex items-center justify-between px-4 py-2.5 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="font-mono font-bold text-red-700 text-sm whitespace-nowrap">{{ $apar->code }}</span>
                    <span class="text-gray-600 text-sm truncate">{{ $apar->location }}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-xs text-red-600 font-semibold whitespace-nowrap">
                        {{ $apar->next_inspection_date->format('d M Y') }}
                        <span class="text-red-400">({{ $days }} hari lalu)</span>
                    </span>
                    <a href="{{ route('apar.show', $apar->code) }}"
                       class="text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg transition whitespace-nowrap">
                        Lihat
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Alert: Upcoming (30 hari) --}}
    @if($upcomingInspection->isNotEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-yellow-100 border-b border-yellow-200">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="font-bold text-yellow-700 text-sm">Jadwal Inspeksi Mendatang (30 hari) — {{ $upcomingInspection->count() }} APAR</p>
        </div>
        <div class="divide-y divide-yellow-100">
            @foreach($upcomingInspection as $apar)
            @php $days = \Carbon\Carbon::today()->diffInDays($apar->next_inspection_date); @endphp
            <div class="flex items-center justify-between px-4 py-2.5 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="font-mono font-bold text-yellow-700 text-sm whitespace-nowrap">{{ $apar->code }}</span>
                    <span class="text-gray-600 text-sm truncate">{{ $apar->location }}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-xs text-yellow-700 font-semibold whitespace-nowrap">
                        {{ $apar->next_inspection_date->format('d M Y') }}
                        <span class="text-yellow-500">({{ $days }} hari lagi)</span>
                    </span>
                    <a href="{{ route('apar.show', $apar->code) }}"
                       class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg transition whitespace-nowrap">
                        Lihat
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('apar.maintenance.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-36">
                <label class="text-xs text-gray-500 font-medium block mb-1">Cari Kode / Lokasi</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="APAR-001, Gudang..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="min-w-40">
                <label class="text-xs text-gray-500 font-medium block mb-1">Jenis Maintenance</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Jenis</option>
                    @foreach(['Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya'] as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-36">
                <label class="text-xs text-gray-500 font-medium block mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="min-w-36">
                <label class="text-xs text-gray-500 font-medium block mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Filter
                </button>
                @if(request()->hasAny(['search','type','date_from','date_to']))
                <a href="{{ route('apar.maintenance.index') }}"
                   class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($maintenances->isEmpty())
            <div class="text-center py-16">
                <span class="text-5xl">🔧</span>
                <p class="text-gray-500 mt-3">Belum ada record maintenance.</p>
                <p class="text-gray-400 text-sm mt-1">Klik tombol "Tambah Maintenance" di atas untuk menambah record baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Tanggal</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Kode APAR</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Lokasi</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Jenis</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Teknisi</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Catatan</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wide">Dicatat Oleh</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($maintenances as $m)
                        @php
                            $typeColor = match($m->maintenance_type) {
                                'Inspeksi Rutin'        => 'bg-blue-100 text-blue-700',
                                'Pengisian Ulang'       => 'bg-green-100 text-green-700',
                                'Penggantian Komponen'  => 'bg-orange-100 text-orange-700',
                                'Perbaikan'             => 'bg-red-100 text-red-700',
                                default                 => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700 font-medium whitespace-nowrap">
                                {{ $m->maintenance_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('apar.show', $m->apar->code) }}"
                                   class="font-mono font-bold text-green-700 hover:underline">
                                    {{ $m->apar->code }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $m->apar->location }}
                                @if($m->apar->building || $m->apar->floor)
                                <span class="text-gray-400 text-xs block">
                                    {{ collect([$m->apar->building, $m->apar->floor ? 'Lt.'.$m->apar->floor : null])->filter()->implode(' / ') }}
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full {{ $typeColor }}">
                                    {{ $m->maintenance_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $m->technician ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500 max-w-xs">
                                <span class="line-clamp-2">{{ $m->notes ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ $m->performer?->username ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <form action="{{ route('apar.maintenance.destroy', $m->id) }}?from=list"
                                      method="POST"
                                      onsubmit="return confirm('Hapus record maintenance ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-400 hover:text-red-600 transition p-1 rounded hover:bg-red-50"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($maintenances->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $maintenances->withQueryString()->links() }}
            </div>
            @endif
        @endif
    </div>

</div>

@push('scripts')
<script>
    // Buka modal otomatis jika ada validation error dari form modal
    @if($errors->any())
        document.getElementById('modal-maintenance').classList.remove('hidden');
    @endif

    // Tutup modal saat klik backdrop
    document.getElementById('modal-maintenance').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endpush
@endsection
