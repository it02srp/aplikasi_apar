<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Semua QR APAR</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: 10cm auto;
            margin: 0;
        }

        /* ── Screen preview ── */
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
                background: #166534;
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
                grid-template-columns: repeat(3, 148px);
                gap: 12px;
                justify-content: center;
            }
            .label-card {
                box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            }
        }

        /* ── Print — Zebra GT800, lebar kertas 10cm ── */
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            body { background: #fff; margin: 0; padding: 0; }

            .actions { display: none !important; }

            .label-grid {
                display: grid;
                grid-template-columns: repeat(3, 3cm);
                gap: 0.3cm;
                width: fit-content;
                margin: 0 0.28cm;
            }

            .label-wrapper {
                width: 3cm;
                height: 4cm;
                padding: 0;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .label-card {
                width: 3cm;
                height: 4cm;
                border: 0.5px solid #ccc !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                display: flex;
                flex-direction: column;
                align-items: center;
                overflow: hidden;
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

            .label-qr {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0.3cm 0 0.15cm 0 !important;  /* ubah nilai top (0.05cm) untuk jarak dari header */
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
                padding: 0 0.1cm 1.5cm !important;
                letter-spacing: 0 !important;
                width: 100% !important;
            }
        }

        /* ── Shared (screen + print base) ── */
        .label-wrapper {
            display: flex;
            align-items: stretch;
            justify-content: center;
        }

        .label-card {
            width: 100%;
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
            padding: 4px 4px;
            font-family: Arial, sans-serif;
            font-weight: 800;
            font-size: 9px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            width: 100%;
        }

        .label-qr {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            padding: 6px;
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
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Semua ({{ $apars->count() }} label)</button>
        <a class="btn-back" href="{{ route('apar.index') }}">← Kembali</a>
    </div>

    <div class="label-grid">
        @foreach($apars as $i => $apar)
        <div class="label-wrapper">
            <div class="label-card">
                <div class="label-header">PT. SINAR RIMBA PASIFIK</div>
                <div class="label-qr">
                    <div id="qr-{{ $i }}"></div>
                </div>
                <div class="label-footer">{{ $apar->code }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        @foreach($apars as $i => $apar)
        new QRCode(document.getElementById("qr-{{ $i }}"), {
            text: "{{ url('/apar/' . $apar->code) }}",
            width: 83,
            height: 83,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
        @endforeach
    </script>
</body>
</html>
