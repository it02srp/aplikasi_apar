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
            <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab (PIC)</label>
            <select name="responsible_person" class="w-full border-gray-300 border rounded-lg px-4 py-2 text-sm focus:ring-green-500">
                <option value="">-- Pilih PIC --</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->username }}" {{ old('responsible_person', $hydrant->responsible_person) == $admin->username ? 'selected' : '' }}>{{ $admin->username }}</option>
                @endforeach
            </select>
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