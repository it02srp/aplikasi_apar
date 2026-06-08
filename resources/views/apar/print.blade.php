<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print APAR — {{ $apar->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        #qrcode img, #qrcode canvas { display: block; margin: 0 auto; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    {{-- Print Actions --}}
    <div class="no-print fixed top-4 right-4 flex gap-2">
        <button onclick="window.print()"
            class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium shadow transition">
            🖨️ Cetak
        </button>
        <a href="{{ route('apar.index') }}"
           class="bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium shadow transition">
            Kembali
        </a>
    </div>

    {{-- Print Card --}}
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm print:shadow-none print:rounded-none print:max-w-full">
        {{-- Header --}}
        <div class="bg-green-700 text-white rounded-t-2xl print:rounded-none px-6 py-4 text-center">
            <p class="text-xs text-green-200 uppercase tracking-widest font-semibold">Alat Pemadam Api Ringan</p>
            <p class="text-lg font-bold mt-0.5">PT Sinar Rimba Pasifik</p>
        </div>

        <div class="px-6 py-6 flex flex-col items-center gap-4">
            {{-- QR Code --}}
            <div id="qrcode" class="p-2 border border-gray-200 rounded-xl bg-white"></div>

            {{-- Code --}}
            <div class="text-center">
                <p class="text-3xl font-mono font-bold text-gray-800 tracking-widest">{{ $apar->code }}</p>
                <p class="text-xs text-gray-400 mt-1">Scan untuk melihat detail</p>
            </div>

            {{-- Divider --}}
            <div class="w-full border-t border-dashed border-gray-300"></div>

            {{-- Info --}}
            <div class="w-full space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Lokasi</span>
                    <span class="font-semibold text-gray-800 text-right max-w-[60%]">{{ $apar->location }}</span>
                </div>
                @if($apar->building)
                <div class="flex justify-between">
                    <span class="text-gray-500">Gedung/Lantai</span>
                    <span class="font-semibold text-gray-800">
                        {{ $apar->building }}{{ $apar->floor ? ' / Lt.'.$apar->floor : '' }}
                    </span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Jenis</span>
                    <span class="font-semibold text-gray-800">{{ $apar->type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Kapasitas</span>
                    <span class="font-semibold text-gray-800">{{ $apar->capacity }} {{ $apar->capacity_unit }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Kadaluarsa</span>
                    <span class="font-semibold
                        {{ $apar->isExpired() ? 'text-red-600' : ($apar->isNearExpiry() ? 'text-yellow-600' : 'text-green-700') }}">
                        {{ $apar->expiry_date->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="w-full border-t border-dashed border-gray-300"></div>

            {{-- Status --}}
            <div class="text-center">
                @if($apar->isExpired())
                    <span class="bg-red-100 text-red-700 border border-red-300 text-xs font-bold px-4 py-1.5 rounded-full">KADALUARSA</span>
                @elseif($apar->isNearExpiry())
                    <span class="bg-yellow-100 text-yellow-700 border border-yellow-300 text-xs font-bold px-4 py-1.5 rounded-full">HAMPIR KADALUARSA</span>
                @else
                    <span class="bg-green-100 text-green-700 border border-green-300 text-xs font-bold px-4 py-1.5 rounded-full">KONDISI BAIK</span>
                @endif
            </div>
        </div>

        <div class="bg-gray-50 rounded-b-2xl print:rounded-none px-6 py-3 text-center">
            <p class="text-xs text-gray-400">{{ now()->format('d/m/Y') }} &bull; SRP APAR Management</p>
        </div>
    </div>

    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ url('/apar/' . $apar->code) }}",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>
