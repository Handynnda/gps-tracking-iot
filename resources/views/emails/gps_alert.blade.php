<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; }
        .card { background-color: #1e293b; padding: 24px; border-radius: 12px; border-left: 6px solid #ef4444; max-width: 500px; margin: auto; }
        .title { color: #f87171; font-size: 20px; font-weight: bold; margin-bottom: 8px; }
        .desc { color: #cbd5e1; font-size: 14px; line-height: 1.5; }
        .coords { background-color: #0f172a; padding: 10px; border-radius: 8px; margin-top: 12px; font-size: 13px; color: #38bdf8; }
        .btn { display: inline-block; padding: 10px 16px; background-color: #0284c7; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: bold; margin-top: 16px; }
        .footer { margin-top: 20px; font-size: 12px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="title">🚨 {{ $title }}</div>
        <p class="desc">{{ $messageText }}</p>
        
        @if($latitude && $longitude)
            <div class="coords">
                📍 <strong>Koordinat Terakhir:</strong> {{ $latitude }}, {{ $longitude }}
            </div>
            <a href="https://maps.google.com/?q={{ $latitude }},{{ $longitude }}" target="_blank" class="btn">
                Buka Lokasi di Google Maps
            </a>
        @endif

        <div class="footer">
            Sistem Monitoring GPS & Geofencing - Agung Jaya Transport<br>
            <i>Waktu: {{ now()->format('d M Y - H:i:s') }} WIB</i>
        </div>
    </div>
</body>
</html>