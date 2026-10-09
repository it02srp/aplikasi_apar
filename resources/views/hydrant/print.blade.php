<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print QR — {{ $hydrant->code }}</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: 3cm auto;
            margin: 0 0 0 0.2cm;
        }

        /* ── Screen preview ── */
        @media screen {
            body {
                background: #e5e7eb;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 16px;
                padding: 24px;
                font-family: Arial, sans-serif;
            }
            .actions {
                display: flex;
                gap: 8px;
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
            .label-card {
                box-shadow: 0 6px 24px rgba(0,0,0,0.15);
            }
        }

        /* ── Print — Zebra GT800 ── */
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            body { background: #fff; margin: 0; padding: 0; }

            .actions { display: none !important; }

            .label-card {
                width: 3cm !important;
                height: 4cm !important;
                border: 0.5px solid #ccc !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                overflow: hidden !important;
            }

            .label-header {
                background: #000 !important;
                color: #fff !important;
                font-size: 5pt !important;
                padding: 2px 3px !important;
                width: 100% !important;
                text-align: center !important;
                font-weight: 700 !important;
                letter-spacing: 0.3px !important;
                flex-shrink: 0 !important;
            }

            .label-logo {
                height: 3mm !important;
            }

            .label-qr {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.05cm 0 0 3cm !important;
                width: 100% !important;
            }

            .label-qr canvas,
            .label-qr img {
                width: 2.55cm !important;
                height: 2.55cm !important;
            }

            .label-footer {
                font-size: 8pt !important;
                font-weight: 700 !important;
                color: #000 !important;
                text-align: center !important;
                padding: 0 0.1cm 0.4cm !important;
                letter-spacing: 0 !important;
                width: 100% !important;
            }
        }

        /* ── Shared ── */
        .label-card {
            width: 3cm;
            height: 4cm;
            background: white;
            border: 1px solid #374151;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .label-header {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 3px 4px;
            font-family: Arial, sans-serif;
            font-weight: 800;
            font-size: 9px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            width: 100%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        .label-logo {
            height: 11px;
            width: auto;
            display: block;
        }

        .label-qr {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.05cm 0.1cm 0 !important;
        }

        .label-footer {
            text-align: center;
            padding: 4px 4px 6px;
            font-family: Arial, sans-serif;
            font-weight: 700;
            font-size: 12px;
            color: #000;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="actions">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak</button>
        <a class="btn-back" href="{{ route('hydrant.index') }}">← Kembali</a>
    </div>

    <div class="label-card">
        <div class="label-header">
            <img src="{{ asset('logo_SRP.png') }}" class="label-logo" alt="SRP">
            PT. SINAR RIMBA PASIFIK
        </div>
        <div class="label-qr">
            <div id="qrcode"></div>
        </div>
        <div class="label-footer">{{ $hydrant->code }}</div>
    </div>

    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ url('/hydrant/' . $hydrant->code) }}",
            width: 83,
            height: 83,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>
