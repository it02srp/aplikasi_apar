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