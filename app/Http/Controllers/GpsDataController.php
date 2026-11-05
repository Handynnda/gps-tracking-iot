<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GpsDataController extends Controller
{
    /**
     * Memproses data koordinat dari ESP32, mengecek Geofence Haversine, 
     * memperbarui status di Firebase, dan memicu NotificationService jika melanggar batas.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_perangkat' => 'required|string',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
        ]);

        $idPerangkat = $request->input('id_perangkat');
        $currentLat  = (float) $request->input('latitude');
        $currentLng  = (float) $request->input('longitude');

        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');
        $baseUrl       = rtrim($firebaseDbUrl, '/');

        // 1. Simpan Log Koordinat ke History Firebase (/GPS_TRACKING/HISTORY/{device_id}/{date})
        $today = now()->format('Y-m-d');
        try {
            Http::withoutVerifying()->post("{$baseUrl}/GPS_TRACKING/HISTORY/{$idPerangkat}/{$today}.json", [
                'latitude'   => $currentLat,
                'longitude'  => $currentLng,
                'waktu'      => now()->format('H:i:s'),
                'created_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal simpan history GPS ke Firebase: " . $e->getMessage());
        }

        // 2. Ambil Parameter Titik Pusat & Radius Geofence Perangkat dari Firebase
        $safeLat     = -6.853103; // Fallback default
        $safeLng     = 108.577580;
        $radiusMeter = 10;

        try {
            $geofenceResponse = Http::withoutVerifying()->get("{$baseUrl}/GPS_TRACKING/GEOFENCE/{$idPerangkat}.json");

            if ($geofenceResponse->successful() && !empty($geofenceResponse->json())) {
                $geofenceData = $geofenceResponse->json();
                
                // Mendukung format key 'latitude'/'lat' dan 'longitude'/'lng'
                $safeLat     = (float) ($geofenceData['latitude'] ?? $geofenceData['lat'] ?? $safeLat);
                $safeLng     = (float) ($geofenceData['longitude'] ?? $geofenceData['lng'] ?? $safeLng);
                $radiusMeter = (float) ($geofenceData['radius'] ?? $radiusMeter);
            }
        } catch (\Exception $e) {
            Log::error("Gagal ambil setting Geofence dari Firebase: " . $e->getMessage());
        }

        // 3. Hitung Jarak Menggunakan Algoritma Haversine (Hasil dalam Meter)
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($currentLat - $safeLat);
        $dLng = deg2rad($currentLng - $safeLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($safeLat)) * cos(deg2rad($currentLat)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $jarakMeter = $earthRadius * $c;

        $isOutside = $jarakMeter > $radiusMeter;
        $statusTeks = $isOutside ? 'BAHAYA: KENDARAAN DILUAR AREA AMAN' : 'KENDARAAN MASIH DI DALAM AREA AMAN';

        // 4. UPDATE DATA REALTIME KE NODE PERANGKAT DIRAILTIME DATABASE
        // Langkah ini penting agar tampilan web dashboard ter-update secara otomatis
        try {
            Http::withoutVerifying()->patch("{$baseUrl}/GPS_TRACKING/PERANGKAT/{$idPerangkat}.json", [
                'lat'        => $currentLat,
                'lng'        => $currentLng,
                'jarak'      => round($jarakMeter, 2),
                'status'     => $statusTeks,
                'updated_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal update status & jarak di Firebase Realtime DB: " . $e->getMessage());
        }

        // 5. Jika Keluar Radius -> Pemicu (Trigger) NotificationService
        if ($isOutside) {
            $pesanNotifikasi = "Pelanggaran Zona Geofencing! Kendaraan ({$idPerangkat}) terdeteksi keluar dari radius aman. Jarak saat ini: " . round($jarakMeter, 2) . " meter.";
            
            // Mengambil email default alert dari environment karena request ESP32 bersifat stateless
            $userEmail = env('DEFAULT_ALERT_EMAIL', 'handynandaf@gmail.com');

            try {
                NotificationService::send(
                    idPerangkat: $idPerangkat,
                    pesanNotifikasi: $pesanNotifikasi,
                    tipeNotifikasi: 'danger',
                    userEmail: $userEmail,
                    lat: $currentLat,
                    lng: $currentLng
                );
            } catch (\Exception $e) {
                Log::error("Gagal mengirimkan NotificationService: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'       => 'success',
            'id_perangkat' => $idPerangkat,
            'jarak_meter'  => round($jarakMeter, 2),
            'radius_batas' => $radiusMeter,
            'geofence'     => $isOutside ? 'Keluar Area' : 'Aman',
            'status_teks'  => $statusTeks,
        ], 200);
    }
}