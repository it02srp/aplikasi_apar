@extends('layouts.app')

@section('title', 'Daftar APAR')

@section('content')
<div class="space-y-4">

    {{-- Page Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar APAR</h2>
            <p class="text-gray-400 text-sm mt-0.5">{{ $apars->total() }} unit terdaftar</p>
        </div>
        <a href="{{ route('apar.create') }}"
           class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white
                  px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah APAR
        </a>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('apar.index') }}"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
        <div class="flex flex-wrap gap-2">
            <div class="flex-1 min-w-[180px]">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode atau lokasi..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
            </div>
            <select name="condition"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                       focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
                <option value="">Semua Kondisi</option>
                @foreach(['Good','Needs Attention','Replace'] as $c)
                    <option value="{{ $c }}" @selected(request('condition') === $c)>{{ $c }}</option>
                @endforeach
            </select>
            <select name="type"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                       focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent">
                <option value="">Semua Jenis</option>
                @foreach(['CO2','Dry Powder','Foam','Water','Clean Agent'] as $t)
                    <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Filter
            </button>
            @if(request()->anyFilled(['search','condition','type']))
                <a href="{{ route('apar.index') }}"
                   class="border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2
                          rounded-lg text-sm font-medium transition-colors">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($apars->isEmpty())
            <div class="py-20 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-medium">Tidak ada data APAR ditemukan.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kode</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Jenis</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kapasitas</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kadaluarsa</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kondisi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($apars as $apar)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- Kode --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <a href="{{ route('apar.show', $apar->code) }}"
                               class="font-mono font-semibold text-green-700 hover:text-green-900 hover:underline">
                                {{ $apar->code }}
                            </a>
                        </td>

                        {{-- Lokasi --}}
                        <td class="px-4 py-3 align-middle max-w-[200px]">
                            <p class="font-medium text-gray-800 truncate">{{ $apar->location }}</p>
                            @if($apar->building || $apar->floor || $apar->room)
                                <p class="text-xs text-gray-400 truncate">
                                    {{ collect([$apar->building, $apar->floor ? 'Lt.'.$apar->floor : null, $apar->room])->filter()->implode(' · ') }}
                                </p>
                            @endif
                        </td>

                        {{-- Jenis --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap text-gray-600">
                            {{ $apar->type }}
                        </td>

                        {{-- Kapasitas --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap text-gray-600">
                            {{ $apar->capacity }} {{ $apar->capacity_unit }}
                        </td>

                        {{-- Kadaluarsa --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <span class="font-medium {{ $apar->isExpired() ? 'text-red-600' : ($apar->isNearExpiry() ? 'text-yellow-600' : 'text-gray-600') }}">
                                {{ $apar->expiry_date->format('d/m/Y') }}
                            </span>
                        </td>

                        {{-- Kondisi --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            @php
                                $condStyle = match($apar->condition) {
                                    'Good'            => 'bg-green-100 text-green-700',
                                    'Needs Attention' => 'bg-yellow-100 text-yellow-700',
                                    'Replace'         => 'bg-red-100 text-red-700',
                                    default           => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $condStyle }}">
                                {{ $apar->condition }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            @if($apar->isExpired())
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    Kadaluarsa
                                </span>
                            @elseif($apar->isNearExpiry())
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    Hampir Habis
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    Baik
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('apar.edit', $apar->code) }}"
                                   class="inline-block px-2.5 py-1 rounded text-xs font-medium
                                          bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                    Edit
                                </a>
                                <a href="{{ route('apar.print', $apar->code) }}" target="_blank"
                                   class="inline-block px-2.5 py-1 rounded text-xs font-medium
                                          bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    Print
                                </a>
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($apars->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $apars->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
