<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar log notifikasi dari SELURUH perangkat GPS di Firebase.
     */
    public function index(?string $idPerangkat = null)
    {
        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');
        $notifications = [];

        if ($firebaseDbUrl) {
            $baseUrl = rtrim($firebaseDbUrl, '/');
            $urlTarget = "{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG.json";

            try {
                $response = Http::withoutVerifying()->get($urlTarget);

                if ($response->successful() && !empty($response->json())) {
                    $rawAll = $response->json();

                    if (is_array($rawAll)) {
                        foreach ($rawAll as $key1 => $value1) {
                            if (!is_array($value1)) continue;

                            // Deteksi Struktur 1: Notifikasi tersimpan LANGSUNG di bawah NOTIFIKASI_LOG
                            if (isset($value1['title']) || isset($value1['pesan_notifikasi']) || isset($value1['message'])) {
                                $value1['id'] = $key1;
                                $value1['device_id'] = $value1['id_perangkat'] ?? $value1['device_id'] ?? 'GPS001';
                                $notifications[] = $value1;
                            } 
                            // Deteksi Struktur 2: Notifikasi tersimpan di dalam SUB-FOLDER ID Perangkat (GPS001, GPS002, dll)
                            else {
                                foreach ($value1 as $pushId => $item) {
                                    if (is_array($item)) {
                                        $item['id'] = $pushId;
                                        $item['device_id'] = $key1;
                                        $notifications[] = $item;
                                    }
                                }
                            }
                        }

                        // Urutkan dari notifikasi terbaru
                        usort($notifications, function ($a, $b) {
                            $timeA = strtotime($a['created_at'] ?? $a['waktu'] ?? $a['timestamp'] ?? 0);
                            $timeB = strtotime($b['created_at'] ?? $b['waktu'] ?? $b['timestamp'] ?? 0);
                            return $timeB <=> $timeA;
                        });
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal mengambil notifikasi Firebase: " . $e->getMessage());
            }
        }

        return view('notifikasi', compact('notifications', 'idPerangkat'));
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca di Firebase.
     */
    public function markAsRead(string $idPerangkat, string $pushId)
    {
        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');

        if ($firebaseDbUrl && $idPerangkat && $pushId) {
            $baseUrl = rtrim($firebaseDbUrl, '/');
            Http::withoutVerifying()->patch("{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}/{$pushId}.json", [
                'is_read' => true,
            ]);
        }

        return back()->with('status', 'Notifikasi berhasil ditandai telah dibaca.');
    }

    /**
     * Tandai semua log notifikasi seluruh perangkat sebagai telah dibaca di Firebase.
     */
    public function markAllRead(?string $idPerangkat = null)
    {
        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');

        if ($firebaseDbUrl) {
            $baseUrl = rtrim($firebaseDbUrl, '/');

            if ($idPerangkat && $idPerangkat !== 'all') {
                $response = Http::withoutVerifying()->get("{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}.json");
                if ($response->successful() && $response->json()) {
                    $raw = $response->json();
                    $updates = [];
                    foreach ($raw as $pushId => $item) {
                        $updates["{$pushId}/is_read"] = true;
                    }
                    if (!empty($updates)) {
                        Http::withoutVerifying()->patch("{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}.json", $updates);
                    }
                }
            } else {
                // Tandai dibaca untuk SELURUH perangkat
                $response = Http::withoutVerifying()->get("{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG.json");
                if ($response->successful() && $response->json()) {
                    $rawAll = $response->json();
                    $updates = [];
                    foreach ($rawAll as $devId => $logs) {
                        if (is_array($logs)) {
                            foreach ($logs as $pushId => $item) {
                                $updates["{$devId}/{$pushId}/is_read"] = true;
                            }
                        }
                    }
                    if (!empty($updates)) {
                        Http::withoutVerifying()->patch("{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG.json", $updates);
                    }
                }
            }
        }

        return back()->with('status', 'Semua notifikasi berhasil ditandai telah dibaca.');
    }

    /**
     * Hapus satu log notifikasi dari Firebase.
     */
    public function destroy(string $idPerangkat, string $pushId)
    {
        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');

        if ($firebaseDbUrl && $idPerangkat && $pushId) {
            $baseUrl = rtrim($firebaseDbUrl, '/');
            Http::withoutVerifying()->delete("{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}/{$pushId}.json");
        }

        return back()->with('status', 'Notifikasi berhasil dihapus.');
    }
}