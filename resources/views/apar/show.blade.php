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

        <div class="text-center mt-6">
            <a href="{{ route('scan') }}"
               class="text-sm text-green-700 hover:underline">← Scan APAR lain</a>
        </div>

        <p class="text-center text-gray-400 text-xs mt-4">&copy; {{ date('Y') }} SRP — APAR Management</p>
    </div>
</div>
@endsection
