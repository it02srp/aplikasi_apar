<?php
$dir = __DIR__ . '/resources/views/hydrant';

$print = <<<'HTML'
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
HTML;

$printAll = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Semua QR Hydrant</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .grid { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .card { border: 1px solid #000; padding: 20px; width: 250px; text-align: center; border-radius: 10px; page-break-inside: avoid; }
        .code { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .qr { margin: 15px 0; }
        .location { font-size: 12px; color: #555; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="grid">
        @foreach($hydrants as $hydrant)
        <div class="card">
            <div class="code">{{ $hydrant->code }}</div>
            <div class="qr">
                {!! QrCode::size(150)->generate(route('hydrant.show', $hydrant->code)) !!}
            </div>
            <div class="location">{{ $hydrant->location }}</div>
        </div>
        @endforeach
    </div>
</body>
</html>
HTML;

file_put_contents($dir . '/print.blade.php', $print);
file_put_contents($dir . '/print-all.blade.php', $printAll);

echo "Print views generated.\n";
