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