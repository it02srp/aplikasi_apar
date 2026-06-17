@extends('layouts.app')

@section('title', 'History Maintenance')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">History Maintenance APAR</h2>
            <p class="text-gray-400 text-sm mt-0.5">{{ $maintenances->total() }} record ditemukan</p>
        </div>
        <button onclick="document.getElementById('modal-maintenance').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Maintenance
        </button>
    </div>

    {{-- Alert: Overdue & Upcoming (collapsible) --}}
    @if($overdueInspection->isNotEmpty() || $upcomingInspection->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

        @if($overdueInspection->isNotEmpty())
        <div class="bg-red-50 border border-red-200 rounded-xl overflow-hidden">
            <button onclick="toggleAlert('alert-overdue')"
                    class="w-full flex items-center justify-between gap-2 px-4 py-3 bg-red-100 hover:bg-red-200 transition-colors text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span class="font-bold text-red-700 text-sm">Terlambat</span>
                    <span class="bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $overdueInspection->count() }}</span>
                </div>
                <svg class="w-4 h-4 text-red-500 transition-transform" id="icon-overdue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="alert-overdue" class="hidden divide-y divide-red-100 max-h-48 overflow-y-auto">
                @foreach($overdueInspection as $apar)
                @php $days = $apar->next_inspection_date->diffInDays(\Carbon\Carbon::today()); @endphp
                <div class="flex items-center justify-between px-4 py-2 gap-3">
                    <div class="min-w-0">
                        <span class="font-mono font-bold text-red-700 text-xs">{{ $apar->code }}</span>
                        <span class="text-gray-500 text-xs ml-2 truncate">{{ $apar->location }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-red-500 whitespace-nowrap">{{ $days }}h lalu</span>
                        <a href="{{ route('apar.show', $apar->code) }}"
                           class="text-xs bg-red-600 hover:bg-red-700 text-white px-2 py-0.5 rounded transition">Lihat</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($upcomingInspection->isNotEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl overflow-hidden">
            <button onclick="toggleAlert('alert-upcoming')"
                    class="w-full flex items-center justify-between gap-2 px-4 py-3 bg-yellow-100 hover:bg-yellow-200 transition-colors text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-bold text-yellow-700 text-sm">Mendatang (30 hari)</span>
                    <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $upcomingInspection->count() }}</span>
                </div>
                <svg class="w-4 h-4 text-yellow-500 transition-transform" id="icon-upcoming" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="alert-upcoming" class="hidden divide-y divide-yellow-100 max-h-48 overflow-y-auto">
                @foreach($upcomingInspection as $apar)
                @php $days = \Carbon\Carbon::today()->diffInDays($apar->next_inspection_date); @endphp
                <div class="flex items-center justify-between px-4 py-2 gap-3">
                    <div class="min-w-0">
                        <span class="font-mono font-bold text-yellow-700 text-xs">{{ $apar->code }}</span>
                        <span class="text-gray-500 text-xs ml-2 truncate">{{ $apar->location }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-yellow-600 whitespace-nowrap">{{ $days }}h lagi</span>
                        <a href="{{ route('apar.show', $apar->code) }}"
                           class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-0.5 rounded transition">Lihat</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('apar.maintenance.index') }}"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
        <div class="flex flex-wrap gap-2 items-end">

            {{-- Tahun --}}
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Tahun</label>
                <select name="year"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cari --}}
            <div class="flex-1 min-w-[160px]">
                <label class="text-xs text-gray-500 font-medium block mb-1">Kode / Lokasi</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="APAR-001, Gudang..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
            </div>

            {{-- Jenis --}}
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Jenis</label>
                <select name="type"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
                    <option value="">Semua Jenis</option>
                    @foreach(['Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya'] as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search','type','year','date_from','date_to']))
                <a href="{{ route('apar.maintenance.index') }}"
                   class="border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($maintenances->isEmpty())
            <div class="text-center py-16">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-500 font-medium">Belum ada record maintenance.</p>
                @if(request()->anyFilled(['search','type','year']))
                <p class="text-gray-400 text-sm mt-1">Coba ubah filter pencarian.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Kode APAR</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider">Lokasi</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Jenis</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Berikutnya</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Teknisi</th>
                            <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider">Catatan</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $prevMonth = null; @endphp
                        @foreach($maintenances as $m)
                        @php
                            $curMonth = $m->maintenance_date->format('F Y');
                            $typeColor = match($m->maintenance_type) {
                                'Inspeksi Rutin'        => 'bg-blue-100 text-blue-700',
                                'Pengisian Ulang'       => 'bg-green-100 text-green-700',
                                'Penggantian Komponen'  => 'bg-orange-100 text-orange-700',
                                'Perbaikan'             => 'bg-red-100 text-red-700',
                                default                 => 'bg-gray-100 text-gray-600',
                            };
                        @endphp

                        {{-- Pemisah bulan --}}
                        @if($curMonth !== $prevMonth)
                        @php $prevMonth = $curMonth; @endphp
                        <tr class="bg-gray-50">
                            <td colspan="8" class="px-4 py-1.5">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $curMonth }}</span>
                            </td>
                        </tr>
                        @endif

                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <span class="font-semibold text-gray-700">{{ $m->maintenance_date->format('d') }}</span>
                                <span class="text-gray-400 text-xs ml-1">{{ $m->maintenance_date->format('M') }}</span>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <a href="{{ route('apar.show', $m->apar->code) }}"
                                   class="font-mono font-bold text-green-700 hover:underline text-xs">
                                    {{ $m->apar->code }}
                                </a>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <p class="text-gray-700 text-xs font-medium truncate max-w-[160px]">{{ $m->apar->location }}</p>
                                @if($m->apar->building || $m->apar->floor)
                                <p class="text-gray-400 text-xs truncate max-w-[160px]">
                                    {{ collect([$m->apar->building, $m->apar->floor ? 'Lt.'.$m->apar->floor : null])->filter()->implode(' / ') }}
                                </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full {{ $typeColor }}">
                                    {{ $m->maintenance_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                @if($m->next_inspection_date)
                                    @php $isPast = $m->next_inspection_date->isPast(); @endphp
                                    <span class="text-xs font-semibold {{ $isPast ? 'text-red-600' : 'text-green-700' }}">
                                        {{ $m->next_inspection_date->format('d M Y') }}
                                    </span>
                                    @if($isPast)
                                    <span class="block text-xs text-red-400">terlambat</span>
                                    @endif
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap text-xs text-gray-600">
                                @if($m->technician)
                                    {{ $m->technician }}
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                                @if($m->performer)
                                <p class="text-gray-400 text-xs">oleh {{ $m->performer->username }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle max-w-[200px]">
                                @if($m->notes)
                                <p class="text-xs text-gray-500 truncate" title="{{ $m->notes }}">{{ $m->notes }}</p>
                                @else
                                <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <form action="{{ route('apar.maintenance.destroy', $m->id) }}?from=list"
                                      method="POST"
                                      onsubmit="return confirm('Hapus record ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-gray-300 hover:text-red-500 transition p-1 rounded hover:bg-red-50"
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

            @if($maintenances->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $maintenances->withQueryString()->links() }}
            </div>
            @endif
        @endif
    </div>

</div>

{{-- Modal Tambah Maintenance --}}
<div id="modal-maintenance"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
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
            <div>
                <label class="text-sm text-gray-700 font-medium block mb-1">APAR <span class="text-red-500">*</span></label>
                <select name="apar_code" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('apar_code') border-red-400 @enderror">
                    <option value="">-- Pilih APAR --</option>
                    @foreach($aparList as $a)
                        <option value="{{ $a->code }}" {{ old('apar_code') === $a->code ? 'selected' : '' }}>
                            {{ $a->code }} — {{ $a->location }}
                        </option>
                    @endforeach
                </select>
                @error('apar_code') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-700 font-medium block mb-1">Tanggal Inspeksi <span class="text-red-500">*</span></label>
                    <input type="date" name="maintenance_date" required
                           value="{{ old('maintenance_date', date('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('maintenance_date') border-red-400 @enderror">
                    @error('maintenance_date') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm text-gray-700 font-medium block mb-1">Inspeksi Berikutnya</label>
                    <input type="date" name="next_inspection_date"
                           value="{{ old('next_inspection_date') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('next_inspection_date') border-red-400 @enderror">
                    @error('next_inspection_date') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-700 font-medium block mb-1">Jenis Maintenance <span class="text-red-500">*</span></label>
                <select name="maintenance_type" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('maintenance_type') border-red-400 @enderror">
                    <option value="">-- Pilih --</option>
                    @foreach(['Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya'] as $t)
                        <option value="{{ $t }}" {{ old('maintenance_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                @error('maintenance_type') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm text-gray-700 font-medium block mb-1">Teknisi / Petugas</label>
                <input type="text" name="technician" value="{{ old('technician') }}"
                       placeholder="Nama teknisi atau petugas"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
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

@push('scripts')
<script>
    @if($errors->any())
        document.getElementById('modal-maintenance').classList.remove('hidden');
    @endif

    function toggleAlert(id) {
        const el = document.getElementById(id);
        el.classList.toggle('hidden');
    }
</script>
@endpush
@endsection
