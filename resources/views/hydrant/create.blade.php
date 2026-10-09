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
            <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab (PIC)</label>
            <select name="responsible_person" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                <option value="">-- Pilih PIC --</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->username }}" {{ old('responsible_person') == $admin->username ? 'selected' : '' }}>{{ $admin->username }}</option>
                @endforeach
            </select>
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