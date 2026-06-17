@extends('layouts.app')

@section('title', 'Pemeriksaan Berkala')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Pemeriksaan Berkala</h2>
            <p class="text-gray-400 text-sm mt-0.5">{{ $inspections->total() }} record ditemukan</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="submitExport()"
                    class="inline-flex items-center gap-2 border border-green-700 text-green-700 hover:bg-green-50
                           px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
                <span id="export-count-badge"
                      class="hidden bg-green-700 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center">0</span>
            </button>
            <button onclick="document.getElementById('modal-tambah').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white
                           px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pemeriksaan
            </button>
        </div>
    </div>

    {{-- Hidden form export --}}
    <form id="form-export" method="POST" action="{{ route('apar.inspection.export') }}">@csrf</form>

    {{-- Filter --}}
    <form method="GET" action="{{ route('apar.inspection.index') }}"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-3">
        <div class="flex flex-wrap gap-2 items-end">
            {{-- Tahun --}}
            <div>
                <label class="text-xs text-gray-500 font-medium block mb-1">Tahun</label>
                <select name="year"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Cari --}}
            <div class="flex-1 min-w-[160px]">
                <label class="text-xs text-gray-500 font-medium block mb-1">Kode / Lokasi</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="APAR-001, Gudang..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search','year','date_from','date_to']))
                <a href="{{ route('apar.inspection.index') }}"
                   class="border border-gray-300 hover:bg-gray-50 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($inspections->isEmpty())
            <div class="py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <p class="font-medium">Belum ada data pemeriksaan berkala.</p>
                @if(request()->anyFilled(['search','year']))
                <p class="text-sm mt-1">Coba ubah filter pencarian.</p>
                @endif
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-3 py-3 w-10">
                            <input type="checkbox" id="check-all" class="accent-yellow-600 w-4 h-4 cursor-pointer" title="Pilih semua">
                        </th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Tanggal & Jam</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Kode APAR</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider">Lokasi</th>
                        <th class="text-center px-2 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Handle</th>
                        <th class="text-center px-2 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Selang</th>
                        <th class="text-center px-2 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Pin</th>
                        <th class="text-center px-2 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Indikator</th>
                        <th class="text-center px-2 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Tabung</th>
                        <th class="text-center px-2 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Masa</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="text-left px-4 py-3 text-xs text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Petugas</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $prevMonth = null; @endphp
                    @foreach($inspections as $inspection)
                    @php
                        $allOk    = $inspection->isAllOk();
                        $curMonth = $inspection->inspected_at->format('F Y');
                    @endphp

                    {{-- Pemisah bulan --}}
                    @if($curMonth !== $prevMonth)
                    @php $prevMonth = $curMonth; @endphp
                    <tr class="bg-gray-50">
                        <td colspan="13" class="px-4 py-1.5">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $curMonth }}</span>
                        </td>
                    </tr>
                    @endif

                    <tr class="hover:bg-gray-50 transition-colors row-item">
                        <td class="px-3 py-3 align-middle">
                            <input type="checkbox" name="row_code" value="{{ $inspection->apar->code }}"
                                   class="row-checkbox accent-yellow-600 w-4 h-4 cursor-pointer">
                        </td>
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <span class="font-semibold text-gray-700">{{ $inspection->inspected_at->format('d') }}</span>
                            <span class="text-gray-400 text-xs ml-1">{{ $inspection->inspected_at->format('M Y') }}</span>
                        </td>
                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <a href="{{ route('apar.show', $inspection->apar->code) }}"
                               class="font-mono font-bold text-green-700 hover:underline text-xs">
                                {{ $inspection->apar->code }}
                            </a>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <p class="text-gray-700 text-xs font-medium truncate max-w-[140px]">{{ $inspection->apar->location }}</p>
                        </td>

                        {{-- 6 Kondisi --}}
                        @foreach(['kondisi_handle','kondisi_selang','kondisi_pin_kunci','kondisi_indikator','kondisi_tabung','kondisi_masa_apar'] as $field)
                        <td class="px-2 py-3 align-middle text-center">
                            @if($inspection->$field === 'OK')
                                <span class="text-green-500 font-bold leading-none">✓</span>
                            @else
                                <span class="text-red-500 font-bold leading-none">✗</span>
                            @endif
                        </td>
                        @endforeach

                        <td class="px-4 py-3 align-middle whitespace-nowrap">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $allOk ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $allOk ? 'Semua OK' : 'Ada Masalah' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-gray-500 whitespace-nowrap">
                            {{ $inspection->inspector?->username ?? '—' }}
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <form action="{{ route('apar.inspection.destroy', $inspection->id) . '?from=list' }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus record pemeriksaan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-gray-300 hover:text-red-500 transition p-1 rounded hover:bg-red-50"
                                        title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $inspections->links() }}
        </div>
        @endif
        @endif
    </div>

</div>

{{-- Modal Tambah Pemeriksaan --}}
<div id="modal-tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="font-bold text-gray-800 text-lg">Tambah Pemeriksaan Berkala</h3>
            <button onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('apar.inspection.store.admin') }}" class="p-6 space-y-4">
            @csrf
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2 text-xs text-yellow-800">
                Tanggal &amp; jam pemeriksaan akan diambil otomatis saat disimpan.
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode APAR <span class="text-red-500">*</span></label>
                <select name="apar_code"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-yellow-400
                               @error('apar_code') border-red-400 @enderror">
                    <option value="">-- Pilih APAR --</option>
                    @foreach($aparList as $apar)
                        <option value="{{ $apar->code }}" {{ old('apar_code') === $apar->code ? 'selected' : '' }}>
                            {{ $apar->code }} — {{ $apar->location }}
                        </option>
                    @endforeach
                </select>
                @error('apar_code') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
            </div>
            @php
                $kondisiItems = [
                    'kondisi_handle'    => '1 : Handle',
                    'kondisi_selang'    => '2 : Selang',
                    'kondisi_pin_kunci' => '3 : Pin Kunci',
                    'kondisi_indikator' => '4 : Indikator',
                    'kondisi_tabung'    => '5 : Tabung',
                    'kondisi_masa_apar' => '6 : Masa Apar',
                ];
            @endphp
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">Kondisi Pemeriksaan <span class="text-red-500">*</span></p>
                <div class="space-y-2">
                    @foreach($kondisiItems as $field => $label)
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="OK" class="accent-green-600"
                                       {{ old($field, 'OK') === 'OK' ? 'checked' : '' }}>
                                <span class="text-xs font-semibold text-green-700">✓ OK</span>
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="NOT OK" class="accent-red-500"
                                       {{ old($field) === 'NOT OK' ? 'checked' : '' }}>
                                <span class="text-xs font-semibold text-red-600">✗ NOT OK</span>
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                 focus:ring-2 focus:ring-yellow-400 resize-none">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit"
                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Simpan Pemeriksaan
                </button>
                <button type="button"
                    onclick="document.getElementById('modal-tambah').classList.add('hidden')"
                    class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    @if($errors->any())
        document.getElementById('modal-tambah').classList.remove('hidden');
    @endif

    const checkAll = document.getElementById('check-all');
    const badge    = document.getElementById('export-count-badge');

    function updateBadge() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const count   = checked.length;
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
        const all = document.querySelectorAll('.row-checkbox');
        checkAll.indeterminate = count > 0 && count < all.length;
        checkAll.checked = all.length > 0 && count === all.length;
    }

    checkAll?.addEventListener('change', function () {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        updateBadge();
    });

    document.querySelectorAll('.row-checkbox').forEach(cb => cb.addEventListener('change', updateBadge));

    function submitExport() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) { alert('Centang minimal satu baris untuk diexport.'); return; }
        const form = document.getElementById('form-export');
        form.querySelectorAll('input[name="codes[]"]').forEach(el => el.remove());
        const codes = [...new Set([...checked].map(cb => cb.value))];
        codes.forEach(code => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'codes[]'; input.value = code;
            form.appendChild(input);
        });
        form.submit();
    }
</script>
@endpush
@endsection
