<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar log notifikasi dari Firebase berdasarkan ID Perangkat.
     */
    public function index(?string $idPerangkat = null)
    {
        // 1. Ambil ID Perangkat dari parameter URL, Session, atau fallback default
        if (!$idPerangkat) {
            $userData = session('user_data');

            if (is_array($userData)) {
                $idPerangkat = $userData['id_perangkat'] ?? session('id_perangkat', 'GPS001');
            } elseif (is_object($userData)) {
                $idPerangkat = $userData->id_perangkat ?? session('id_perangkat', 'GPS001');
            } else {
                $idPerangkat = session('id_perangkat', 'GPS001');
            }
        }

        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');
        $notifications = [];

        // 2. Ambil log notifikasi dari Firebase (/GPS_TRACKING/NOTIFIKASI_LOG/{id_perangkat}.json)
        if ($firebaseDbUrl && $idPerangkat) {
            $baseUrl = rtrim($firebaseDbUrl, '/');
            $urlTarget = "{$baseUrl}/GPS_TRACKING/NOTIFIKASI_LOG/{$idPerangkat}.json";

            try {
                $response = Http::withoutVerifying()->get($urlTarget);

                if ($response->successful() && !empty($response->json())) {
                    $raw = $response->json();

                    if (is_array($raw)) {
                        foreach ($raw as $pushId => $item) {
                            if (is_array($item)) {
                                $item['id'] = $pushId;
                                $notifications[] = $item;
                            }
                        }

                        // Urutkan dari notifikasi yang terbaru
                        $notifications = array_reverse($notifications);
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
     * Tandai semua log notifikasi perangkat ini sebagai telah dibaca di Firebase.
     */
    public function markAllRead(string $idPerangkat)
    {
        $firebaseDbUrl = env('FIREBASE_DATABASE_URL');

        if ($firebaseDbUrl && $idPerangkat) {
            $baseUrl = rtrim($firebaseDbUrl, '/');
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