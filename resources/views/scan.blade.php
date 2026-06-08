@extends('layouts.guest')

@section('title', 'Scan APAR')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col items-center py-10 px-4">
    <div class="w-full max-w-md">
        {{-- Header --}}
        <div class="text-center mb-6">
            <span class="text-4xl">📷</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Scan QR APAR</h1>
            <p class="text-gray-500 text-sm mt-1">Arahkan kamera ke QR code pada tabung APAR</p>
        </div>

        {{-- Scanner Card --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div id="reader" class="w-full"></div>

            <div id="scan-status" class="px-5 py-4 text-center">
                <p class="text-sm text-gray-500" id="status-text">Memulai kamera...</p>
            </div>
        </div>

        {{-- Manual Input --}}
        <div class="mt-5 bg-white rounded-xl shadow p-5">
            <p class="text-sm font-medium text-gray-700 mb-3">Atau masukkan kode manual:</p>
            <form id="manual-form" class="flex gap-2">
                <input type="text" id="manual-code" placeholder="Contoh: APAR-001"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 uppercase">
                <button type="submit"
                    class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Cari
                </button>
            </form>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-gray-600">Login ke Sistem</a>
        </div>
    </div>
</div>

@push('head')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

@push('scripts')
<script>
    const statusText = document.getElementById('status-text');

    function navigate(decodedText) {
        const trimmed = decodedText.trim();
        // If QR contains a full URL, navigate directly; otherwise treat as APAR code
        if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
            window.location.href = trimmed;
        } else {
            window.location.href = '/apar/' + encodeURIComponent(trimmed);
        }
    }

    function onScanSuccess(decodedText) {
        statusText.textContent = 'QR terdeteksi: ' + decodedText;
        html5QrCode.stop().then(() => navigate(decodedText))
                         .catch(() => navigate(decodedText));
    }

    function onScanFailure(error) {
        // Silent — continuous scan
    }

    const html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 220, height: 220 },
                    aspectRatio: 1.0,
                },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                statusText.textContent = 'Kamera aktif — arahkan ke QR code APAR';
            }).catch(err => {
                statusText.textContent = 'Gagal memulai kamera: ' + err;
            });
        } else {
            statusText.textContent = 'Tidak ada kamera ditemukan.';
        }
    }).catch(err => {
        statusText.textContent = 'Tidak dapat mengakses kamera: ' + err;
    });

    document.getElementById('manual-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const code = document.getElementById('manual-code').value.trim();
        if (code) {
            window.location.href = '/apar/' + encodeURIComponent(code);
        }
    });
</script>
@endpush
@endsection
