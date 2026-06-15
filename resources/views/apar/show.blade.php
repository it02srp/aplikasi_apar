@extends('layouts.guest')

@section('title', 'APAR ' . $apar->code)

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-lg mx-auto">
        {{-- Header --}}
        <div class="text-center mb-6">
            <span class="text-4xl">🔥</span>
            <h1 class="text-xl font-bold text-gray-800 mt-1">APAR Management</h1>
            <p class="text-gray-500 text-sm">PT Sinar Rimba Pasifik</p>
        </div>

        {{-- Status Badge --}}
        <div class="text-center mb-4">
            @if($apar->isExpired())
                <span class="inline-block bg-red-100 text-red-700 border border-red-300 text-base font-bold px-5 py-2 rounded-full">
                    ⚠️ KADALUARSA
                </span>
            @elseif($apar->isNearExpiry())
                <span class="inline-block bg-yellow-100 text-yellow-700 border border-yellow-300 text-base font-bold px-5 py-2 rounded-full">
                    ⏳ HAMPIR KADALUARSA
                </span>
            @else
                <span class="inline-block bg-green-100 text-green-700 border border-green-300 text-base font-bold px-5 py-2 rounded-full">
                    ✅ KONDISI BAIK
                </span>
            @endif
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            {{-- Code Header --}}
            <div class="bg-green-700 px-6 py-4 text-white">
                <p class="text-xs text-green-200 uppercase tracking-widest">Kode APAR</p>
                <p class="text-3xl font-mono font-bold mt-0.5">{{ $apar->code }}</p>
            </div>

            <div class="p-5 space-y-4">
                {{-- Lokasi --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Lokasi</p>
                    <p class="text-gray-800 font-semibold">{{ $apar->location }}</p>
                    @if($apar->building || $apar->floor || $apar->room)
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ collect([$apar->building, $apar->floor ? 'Lt.'.$apar->floor : null, $apar->room])->filter()->implode(' / ') }}
                    </p>
                    @endif
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Jenis</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->type }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Kapasitas</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->capacity }} {{ $apar->capacity_unit }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Tanggal Produksi</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->manufacture_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Tanggal Kadaluarsa</p>
                        <p class="text-sm font-semibold mt-0.5
                            {{ $apar->isExpired() ? 'text-red-600' : ($apar->isNearExpiry() ? 'text-yellow-600' : 'text-green-700') }}">
                            {{ $apar->expiry_date->format('d M Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Kondisi</p>
                        @php
                            $condColor = match($apar->condition) {
                                'Good'           => 'bg-green-100 text-green-700',
                                'Needs Attention'=> 'bg-yellow-100 text-yellow-700',
                                'Replace'        => 'bg-red-100 text-red-700',
                                default          => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mt-0.5 {{ $condColor }}">
                            {{ $apar->condition }}
                        </span>
                    </div>
                    @if($apar->responsible_person)
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Penanggung Jawab</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->responsible_person }}</p>
                    </div>
                    @endif
                </div>

                @if($apar->last_inspection_date || $apar->next_inspection_date)
                <div class="border-t border-gray-100 pt-3 grid grid-cols-2 gap-3">
                    @if($apar->last_inspection_date)
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Inspeksi Terakhir</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->last_inspection_date->format('d M Y') }}</p>
                    </div>
                    @endif
                    @if($apar->next_inspection_date)
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Inspeksi Berikutnya</p>
                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $apar->next_inspection_date->format('d M Y') }}</p>
                    </div>
                    @endif
                </div>
                @endif

                @if($apar->notes)
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-500 font-medium mb-1">Catatan</p>
                    <p class="text-sm text-gray-700">{{ $apar->notes }}</p>
                </div>
                @endif

                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-400">Terakhir diperbarui: {{ $apar->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Maintenance History --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden mt-4">
            <div class="bg-green-700 px-6 py-3 text-white flex items-center justify-between">
                <p class="font-bold text-sm uppercase tracking-wide">History Maintenance</p>
                <span class="bg-green-600 text-green-100 text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ $apar->maintenances->count() }} record
                </span>
            </div>

            {{-- Form tambah maintenance (hanya untuk admin yang login) --}}
            @auth
            <div class="p-5 border-b border-gray-100 bg-green-50">
                @if(session('success'))
                    <div class="mb-3 bg-green-100 border border-green-300 text-green-800 text-sm rounded-lg px-4 py-2">
                        {{ session('success') }}
                    </div>
                @endif
                <p class="text-xs text-green-800 font-semibold uppercase tracking-wide mb-3">Tambah Record Maintenance</p>
                <form action="{{ route('apar.maintenance.store', $apar->code) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Tanggal Maintenance *</label>
                            <input type="date" name="maintenance_date"
                                   value="{{ old('maintenance_date', date('Y-m-d')) }}"
                                   class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('maintenance_date') border-red-400 @enderror">
                            @error('maintenance_date')
                                <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 font-medium">Jenis Maintenance *</label>
                            <select name="maintenance_type"
                                    class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('maintenance_type') border-red-400 @enderror">
                                <option value="">-- Pilih --</option>
                                @foreach(['Inspeksi Rutin','Pengisian Ulang','Penggantian Komponen','Perbaikan','Lainnya'] as $type)
                                    <option value="{{ $type }}" {{ old('maintenance_type') === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('maintenance_type')
                                <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 font-medium">Teknisi / Petugas</label>
                        <input type="text" name="technician" value="{{ old('technician') }}"
                               placeholder="Nama teknisi atau petugas"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 font-medium">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Deskripsi pekerjaan, temuan, dll."
                                  class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-green-700 hover:bg-green-800 text-white text-sm font-semibold py-2 rounded-lg transition">
                        + Simpan Record Maintenance
                    </button>
                </form>
            </div>
            @endauth

            {{-- Timeline history --}}
            <div class="p-5">
                @if($apar->maintenances->isEmpty())
                    <div class="text-center py-6">
                        <span class="text-3xl">🔧</span>
                        <p class="text-gray-500 text-sm mt-2">Belum ada history maintenance.</p>
                    </div>
                @else
                    <div class="relative">
                        {{-- Garis vertikal timeline --}}
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                        <div class="space-y-4">
                            @foreach($apar->maintenances as $maintenance)
                            @php
                                $typeColor = match($maintenance->maintenance_type) {
                                    'Inspeksi Rutin'        => ['dot' => 'bg-blue-500',   'badge' => 'bg-blue-100 text-blue-700'],
                                    'Pengisian Ulang'       => ['dot' => 'bg-green-500',  'badge' => 'bg-green-100 text-green-700'],
                                    'Penggantian Komponen'  => ['dot' => 'bg-orange-500', 'badge' => 'bg-orange-100 text-orange-700'],
                                    'Perbaikan'             => ['dot' => 'bg-red-500',    'badge' => 'bg-red-100 text-red-700'],
                                    default                 => ['dot' => 'bg-gray-400',   'badge' => 'bg-gray-100 text-gray-600'],
                                };
                            @endphp
                            <div class="flex items-start gap-4 pl-0">
                                {{-- Dot timeline --}}
                                <div class="relative z-10 flex-shrink-0 w-8 h-8 rounded-full {{ $typeColor['dot'] }} flex items-center justify-center shadow-sm">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 bg-gray-50 rounded-xl p-3 min-w-0">
                                    <div class="flex items-start justify-between gap-2 flex-wrap">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold {{ $typeColor['badge'] }} px-2 py-0.5 rounded-full">
                                                {{ $maintenance->maintenance_type }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500 font-medium whitespace-nowrap">
                                                {{ $maintenance->maintenance_date->format('d M Y') }}
                                            </span>
                                            @auth
                                            <form action="{{ route('apar.maintenance.destroy', $maintenance->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Hapus record ini?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-400 hover:text-red-600 transition"
                                                        title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            @endauth
                                        </div>
                                    </div>

                                    @if($maintenance->technician)
                                    <p class="text-xs text-gray-600 mt-1.5">
                                        <span class="font-medium">Teknisi:</span> {{ $maintenance->technician }}
                                    </p>
                                    @endif

                                    @if($maintenance->notes)
                                    <p class="text-xs text-gray-700 mt-1 leading-relaxed">{{ $maintenance->notes }}</p>
                                    @endif

                                    @if($maintenance->performer)
                                    <p class="text-xs text-gray-400 mt-1.5">Dicatat oleh: {{ $maintenance->performer->username }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('scan') }}"
               class="text-sm text-green-700 hover:underline">← Scan APAR lain</a>
        </div>

        <p class="text-center text-gray-400 text-xs mt-4">&copy; {{ date('Y') }} SRP — APAR Management</p>
    </div>
</div>
@endsection
