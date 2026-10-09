<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Hydrant {{ $hydrant->code }}</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 20px; }
        .card { border: 2px solid #000; padding: 20px; width: 300px; margin: 0 auto; border-radius: 10px; }
        .code { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .qr { margin: 20px 0; }
        .location { font-size: 14px; color: #555; }
        @media print {
            body { padding: 0; }
            .card { border: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="card">
        <div class="code">{{ $hydrant->code }}</div>
        <div class="qr">
            {!! QrCode::size(200)->generate(route('hydrant.show', $hydrant->code)) !!}
        </div>
        <div class="location">{{ $hydrant->location }}</div>
    </div>
</body>
</html>