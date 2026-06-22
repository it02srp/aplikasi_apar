@extends('layouts.app')

@section('title', 'Tambah APAR')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div>
        <a href="{{ route('apar.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-1">Tambah APAR Baru</h1>
    </div>

    <form method="POST" action="{{ route('apar.store') }}" class="space-y-5">
        @csrf

        {{-- Identitas --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-4">
            <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide border-b pb-2">Identitas APAR</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kode APAR
                        <span class="text-gray-400 font-normal">(kosongkan untuk otomatis)</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code', $nextCode) }}"
                        placeholder="{{ $nextCode }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('code') border-red-500 @enderror">
                    @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis <span class="text-red-500">*</span></label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('type') border-red-500 @enderror">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(old('type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="number" name="capacity" value="{{ old('capacity') }}" step="0.01" min="0"
                            placeholder="6"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('capacity') border-red-500 @enderror">
                        <select name="capacity_unit" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            <option value="kg" @selected(old('capacity_unit','kg') === 'kg')>kg</option>
                            <option value="liter" @selected(old('capacity_unit') === 'liter')>liter</option>
                        </select>
                    </div>
                    @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi <span class="text-red-500">*</span></label>
                    <select name="condition" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('condition') border-red-500 @enderror">
                        @foreach(['Good','Needs Attention','Replace'] as $c)
                            <option value="{{ $c }}" @selected(old('condition','Good') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                    @error('condition')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Lokasi --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-4">
            <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide border-b pb-2">Informasi Lokasi</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                <input type="text" name="location" value="{{ old('location') }}"
                    placeholder="Contoh: Lobby Utama, Area Produksi"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('location') border-red-500 @enderror">
                @error('location')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gedung</label>
                    <input type="text" name="building" value="{{ old('building') }}"
                        placeholder="Gedung A"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                    <input type="text" name="floor" value="{{ old('floor') }}"
                        placeholder="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                    <input type="text" name="room" value="{{ old('room') }}"
                        placeholder="Ruang Server"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
                <input type="text" name="responsible_person" value="{{ old('responsible_person') }}"
                    placeholder="Nama penanggung jawab"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>
        </div>

        {{-- Tanggal --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-4">
            <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide border-b pb-2">Tanggal & Inspeksi</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="manufacture_date" value="{{ old('manufacture_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('manufacture_date') border-red-500 @enderror">
                    @error('manufacture_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kadaluarsa <span class="text-red-500">*</span></label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 @error('expiry_date') border-red-500 @enderror">
                    @error('expiry_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Inspeksi Terakhir</label>
                    <input type="date" name="last_inspection_date" value="{{ old('last_inspection_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Inspeksi Berikutnya</label>
                    <input type="date" name="next_inspection_date" value="{{ old('next_inspection_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
            </div>
        </div>

        {{-- Catatan --}}
        <div class="bg-white rounded-xl shadow p-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="3"
                placeholder="Catatan tambahan (opsional)"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="bg-green-700 hover:bg-green-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow">
                Simpan APAR
            </button>
            <a href="{{ route('apar.index') }}"
               class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2.5 rounded-lg text-sm font-semibold transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
