<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Semua QR Hydrant</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4 portrait;
            margin: 0.8cm;
        }

        @media screen {
            body {
                background: #e5e7eb;
                min-height: 100vh;
                padding: 24px;
                font-family: Arial, sans-serif;
            }
            .actions {
                display: flex;
                gap: 8px;
                margin-bottom: 20px;
                justify-content: center;
            }
            .btn-print {
                background: #1d4ed8;
                color: white;
                padding: 10px 24px;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
            }
            .btn-back {
                background: white;
                color: #374151;
                padding: 10px 24px;
                border: 1.5px solid #d1d5db;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
            }
            .label-grid {
                display: grid;
                grid-template-columns: repeat(2, 320px);
                gap: 16px;
                justify-content: center;
            }
        }

        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: #fff; margin: 0; padding: 0; }
            .actions { display: none !important; }

            .label-grid {
                display: grid;
                grid-template-columns: repeat(2, 8.5cm);
                gap: 0.5cm;
                width: fit-content;
                margin: 0 auto;
            }

            .label-wrapper {
                width: 8.5cm;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .label-card {
                width: 8.5cm !important;
                height: auto !important;
                min-height: 5.8cm !important;
                border: 1.5px solid #16a34a !important;
                border-radius: 4px !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .label-top {
                padding: 4px 8px 3px !important;
                gap: 6px !important;
            }

            .label-logo { height: 8mm !important; }

            .label-company { font-size: 8pt !important; }

            .label-qr { padding: 3px 4px !important; }

            .label-qr canvas,
            .label-qr img {
                width: 3.2cm !important;
                height: 3.2cm !important;
            }

            .label-footer {
                font-size: 10pt !important;
                padding: 3px 8px 1px !important;
            }

            .label-location {
                font-size: 7pt !important;
                padding: 1px 8px 4px !important;
            }
        }

        /* ── Shared ── */
        .label-wrapper {
            display: flex;
            justify-content: center;
        }

        .label-card {
            width: 100%;
            background: white;
            border: 2px solid #16a34a;
            border-radius: 10px;
            overflow: visible;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .label-top {
            width: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            padding: 10px 12px 8px;
            border-bottom: 2px solid #16a34a;
            gap: 8px;
        }

        .label-logo {
            height: 36px;
            width: auto;
            display: block;
            flex-shrink: 0;
        }

        .label-company {
            font-family: Arial, sans-serif;
            font-weight: 900;
            font-size: 11px;
            color: #16a34a;
            text-align: left;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .label-qr {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .label-footer {
            width: 100%;
            text-align: center;
            padding: 5px 8px 2px;
            font-family: Arial, sans-serif;
            font-weight: 900;
            font-size: 16px;
            color: #111;
            border-top: 1.5px solid #16a34a;
        }

        .label-location {
            width: 100%;
            text-align: center;
            padding: 1px 8px 8px;
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #444;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="actions">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Semua ({{ $hydrants->count() }} label)</button>
        <a class="btn-back" href="{{ route('hydrant.index') }}">← Kembali</a>
    </div>

    <div class="label-grid">
        @foreach($hydrants as $i => $hydrant)
        <div class="label-wrapper">
            <div class="label-card">
                <div class="label-top">
                    <img src="{{ asset('logo_SRP.png') }}" class="label-logo" alt="SRP"
                         onerror="this.style.display='none'">
                    <div class="label-company">PT. SINAR<br>RIMBA PASIFIK</div>
                </div>
                <div class="label-qr">
                    <div id="qr-{{ $i }}"></div>
                </div>
                <div class="label-footer">{{ $hydrant->code }}</div>
                <div class="label-location">{{ $hydrant->location }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        @foreach($hydrants as $i => $hydrant)
        new QRCode(document.getElementById("qr-{{ $i }}"), {
            text: "{{ url('/hydrant/' . $hydrant->code) }}",
            width: 160,
            height: 160,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        @endforeach
    </script>
</body>
</html>
