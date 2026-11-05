<?php

namespace App\Services;

use App\Mail\GpsAlertMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public static function send(
        string $idPerangkat,
        string $pesanNotifikasi,
        string $tipeNotifikasi = 'danger',
        ?string $userEmail = null,
        string|float|null $lat = null,
        string|float|null $lng = null
    ): void {
        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');

        if ($firebaseDbUrl && $idPerangkat) {
            try {
                Http::post(rtrim($firebaseDbUrl, '/') . "/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}.json", [
                    'waktu'            => now()->format('H:i:s'),
                    'pesan_notifikasi' => $pesanNotifikasi,
                    'tipe_notifikasi'  => $tipeNotifikasi,
                    'created_at'       => now()->toIso8601String(), // Opsional: Tambahan tanggal ISO untuk pengurutan
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal simpan NOTIFIKASI_LOG ke Firebase: ' . $e->getMessage());
            }
        }

        if ($userEmail) {
            try {
                Mail::to($userEmail)->send(
                    new GpsAlertMail(
                        'Peringatan GPS',
                        $pesanNotifikasi,
                        $lat !== null ? (string) $lat : null,
                        $lng !== null ? (string) $lng : null
                    )
                );
            } catch (\Exception $e) {
                Log::error('Gagal kirim email: ' . $e->getMessage());
            }
        }
    }
}