@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan status APAR per {{ now()->format('d F Y') }}</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total APAR</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $total }}</p>
            <p class="text-xs text-gray-400 mt-1">unit terdaftar</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kondisi Baik</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $good }}</p>
            <p class="text-xs text-gray-400 mt-1">tidak kadaluarsa</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-yellow-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hampir Kadaluarsa</p>
            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $nearExpiry }}</p>
            <p class="text-xs text-gray-400 mt-1">&le; 30 hari lagi</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kadaluarsa</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ $expired }}</p>
            <p class="text-xs text-gray-400 mt-1">perlu penggantian</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('apar.create') }}"
           class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow">
            + Tambah APAR
        </a>
        <a href="{{ route('apar.index') }}"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
            Lihat Semua APAR
        </a>
        <a href="{{ route('scan') }}" target="_blank"
           class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
            Scan APAR
        </a>
    </div>

    {{-- Recent APAR --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">APAR Terbaru Diperbarui</h2>
            <a href="{{ route('apar.index') }}" class="text-green-700 text-sm hover:underline">Lihat semua</a>
        </div>
        @if($recentApars->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada data APAR.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Kode</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Lokasi</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Jenis</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Kadaluarsa</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentApars as $apar)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono font-semibold text-gray-800">
                            <a href="{{ route('apar.show', $apar->code) }}" class="hover:text-green-700">{{ $apar->code }}</a>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $apar->location }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $apar->type }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $apar->expiry_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            @if($apar->isExpired())
                                <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">Kadaluarsa</span>
                            @elseif($apar->isNearExpiry())
                                <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-1 rounded-full">Hampir Habis</span>
                            @else
                                <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">Baik</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="mt-8">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Hydrant</h1>
        <p class="text-gray-500 text-sm mt-1">Ringkasan status Hydrant per {{ now()->format('F Y') }}</p>
    </div>

    {{-- Hydrant Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Hydrant</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">{{ $hydrantTotal }}</p>
            <p class="text-xs text-gray-400 mt-1">unit terdaftar</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kondisi Baik</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $hydrantGood }}</p>
            <p class="text-xs text-gray-400 mt-1">semua komponen OK</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-yellow-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Perhatian (Needs Attention)</p>
            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $hydrantNeedsAttention }}</p>
            <p class="text-xs text-gray-400 mt-1">ada komponen minor rusak</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rusak (Damaged)</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ $hydrantDamaged }}</p>
            <p class="text-xs text-gray-400 mt-1">komponen kritis bermasalah</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-indigo-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Inspeksi Bulan Ini</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $hydrantSudahInspeksi }} / {{ $hydrantTotal }}</p>
            <p class="text-xs text-gray-400 mt-1">hydrant sudah diinspeksi</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-orange-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Belum Inspeksi</p>
            <p class="text-3xl font-bold text-orange-600 mt-1">{{ $hydrantBelumInspeksi }}</p>
            <p class="text-xs text-gray-400 mt-1">hydrant menunggu inspeksi</p>
        </div>
    </div>

    {{-- Recent Hydrants --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Hydrant Terbaru Diperbarui</h2>
            <a href="{{ route('hydrant.index') }}" class="text-green-700 text-sm hover:underline">Lihat semua</a>
        </div>
        @if($recentHydrants->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada data Hydrant.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Kode</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Lokasi</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Selang</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 text-xs uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentHydrants as $h)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono font-semibold text-gray-800">
                            <a href="{{ route('hydrant.show', $h->code) }}" class="hover:text-green-700">{{ $h->code }}</a>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $h->location }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $h->hose_length }} m</td>
                        <td class="px-5 py-3">
                            @if($h->condition == 'Good')
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Good</span>
                            @elseif($h->condition == 'Needs Attention')
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Needs Attention</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Damaged</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
