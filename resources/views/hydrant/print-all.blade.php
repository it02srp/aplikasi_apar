<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Semua QR Hydrant</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4;
            margin: 1cm;
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
                padding: 9px 20px;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
            }
            .btn-back {
                background: white;
                color: #374151;
                padding: 9px 20px;
                border: 1.5px solid #d1d5db;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
            }
            .label-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 14px;
                max-width: 760px;
                margin: 0 auto;
            }
            .label-card {
                box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            }
        }

        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: #fff; margin: 0; padding: 0; }
            .actions { display: none !important; }

            .label-grid {
                display: grid;
                grid-template-columns: repeat(4, 4.5cm);
                gap: 0.4cm;
                width: fit-content;
                margin: 0 auto;
            }

            .label-wrapper {
                width: 4.5cm;
                height: 5.5cm;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .label-card {
                width: 4.5cm !important;
                height: 5.5cm !important;
                border: 1px solid #16a34a !important;
                border-radius: 4px !important;
                box-shadow: none !important;
            }

            .label-logo {
                height: 6mm !important;
            }

            .label-qr canvas,
            .label-qr img {
                width: 3.5cm !important;
                height: 3.5cm !important;
            }

            .label-company {
                font-size: 7pt !important;
            }

            .label-footer {
                font-size: 9pt !important;
            }
        }

        /* ── Shared ── */
        .label-wrapper {
            display: flex;
            align-items: stretch;
            justify-content: center;
        }

        .label-card {
            width: 100%;
            background: white;
            border: 1.5px solid #16a34a;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .label-top {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 6px 4px;
            border-bottom: 1.5px solid #16a34a;
            gap: 3px;
        }

        .label-logo {
            height: 22px;
            width: auto;
            display: block;
        }

        .label-company {
            font-family: Arial, sans-serif;
            font-weight: 800;
            font-size: 8px;
            color: #16a34a;
            text-align: center;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .label-qr {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
        }

        .label-footer {
            width: 100%;
            text-align: center;
            padding: 4px 4px 7px;
            font-family: Arial, sans-serif;
            font-weight: 800;
            font-size: 13px;
            color: #111;
            border-top: 1px solid #e5e7eb;
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
                    <div class="label-company">PT. SINAR RIMBA PASIFIK</div>
                </div>
                <div class="label-qr">
                    <div id="qr-{{ $i }}"></div>
                </div>
                <div class="label-footer">{{ $hydrant->code }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        @foreach($hydrants as $i => $hydrant)
        new QRCode(document.getElementById("qr-{{ $i }}"), {
            text: "{{ url('/hydrant/' . $hydrant->code) }}",
            width: 110,
            height: 110,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
        @endforeach
    </script>
</body>
</html>
