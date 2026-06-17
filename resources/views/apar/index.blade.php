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
        <div class="flex flex-wrap items-center gap-2">
            {{-- Print Semua --}}
            <button type="button" onclick="printAll()"
               class="inline-flex items-center gap-2 border border-gray-400 text-gray-600 hover:bg-gray-50
                      px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Semua
            </button>
            {{-- Download Template --}}
            <a href="{{ route('apar.template') }}"
               class="inline-flex items-center gap-2 border border-green-700 text-green-700 hover:bg-green-50
                      px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Template Excel
            </a>
            {{-- Import Button --}}
            <button onclick="document.getElementById('modal-import').classList.remove('hidden')"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white
                      px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Excel
            </button>
            {{-- Tambah Manual --}}
            <a href="{{ route('apar.create') }}"
               class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white
                      px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah APAR
            </a>
        </div>
    </div>

    {{-- Import Errors --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="font-semibold text-yellow-800 text-sm">Detail baris yang dilewati:</p>
        </div>
        <ul class="space-y-1 max-h-40 overflow-y-auto">
            @foreach(session('import_errors') as $err)
                <li class="text-xs text-yellow-700 flex items-start gap-1">
                    <span class="mt-0.5">•</span><span>{{ $err }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Modal Import --}}
    <div id="modal-import" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="font-bold text-gray-800 text-lg">Import Data APAR</h3>
                <button onclick="document.getElementById('modal-import').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('apar.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                {{-- Step guide --}}
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 space-y-1.5">
                    <p class="font-semibold text-green-900 mb-2">Cara import:</p>
                    <div class="flex items-start gap-2">
                        <span class="bg-green-700 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                        <span>Download template Excel dengan tombol <strong>Template Excel</strong></span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="bg-green-700 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                        <span>Isi data di sheet <strong>Data APAR</strong>, hapus baris contoh hijau</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="bg-green-700 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                        <span>Upload file di sini, lalu klik <strong>Import Sekarang</strong></span>
                    </div>
                </div>

                {{-- File input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                    <div id="drop-zone"
                         class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center
                                hover:border-green-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('excel-file').click()">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500" id="file-label">Klik untuk pilih file .xlsx / .xls</p>
                        <p class="text-xs text-gray-400 mt-1">Maksimal 5MB</p>
                    </div>
                    <input type="file" id="excel-file" name="file" accept=".xlsx,.xls" class="hidden"
                           onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Klik untuk pilih file'">
                    @error('file')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                        class="flex-1 bg-green-700 hover:bg-green-800 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        Import Sekarang
                    </button>
                    <button type="button"
                        onclick="document.getElementById('modal-import').classList.add('hidden')"
                        class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
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
                            <div class="flex flex-col gap-1">
                                @if($apar->is_maintenance)
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                                        🔧 Maintenance
                                    </span>
                                @endif
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
                            </div>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('apar.edit', $apar->code) }}"
                                   class="inline-block px-2.5 py-1 rounded text-xs font-medium
                                          bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                    Edit
                                </a>
                                <button type="button"
                                        onclick="printApar('{{ $apar->code }}')"
                                        class="inline-block px-2.5 py-1 rounded text-xs font-medium
                                               bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                    Print
                                </button>
                                <button type="button"
                                        onclick="confirmDelete('{{ $apar->code }}')"
                                        class="inline-block px-2.5 py-1 rounded text-xs font-medium
                                               bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                    Hapus
                                </button>
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

{{-- Modal Konfirmasi Hapus --}}
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
        <div class="p-6 text-center">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Hapus APAR</h3>
            <p class="text-sm text-gray-500 mb-1">Yakin ingin menghapus APAR</p>
            <p class="text-sm font-semibold text-gray-800 mb-4" id="delete-code-label"></p>
            <p class="text-xs text-red-500 mb-6">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-delete').classList.add('hidden')"
                        class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Batal
                </button>
                <form id="form-delete" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 px-6 rounded-lg text-sm font-semibold transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Hidden iframe untuk print --}}
<iframe id="print-frame" style="display:none;"></iframe>

@push('scripts')
<script>
function printApar(code) {
    const frame = document.getElementById('print-frame');
    frame.src = '/apar/' + code + '/print';
    frame.onload = function () {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    };
}

function printAll() {
    const frame = document.getElementById('print-frame');
    frame.src = '{{ route('apar.print-all') }}';
    frame.onload = function () {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    };
}

function confirmDelete(code) {
    document.getElementById('delete-code-label').textContent = code + '?';
    document.getElementById('form-delete').action = '/apar/' + code;
    document.getElementById('modal-delete').classList.remove('hidden');
}
</script>
@endpush
@endsection