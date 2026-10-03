<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerangkatController extends Controller
{
    private string $firebasePath = 'GPS_TRACKING/PERANGKAT';
    private string $geofencePath = 'GPS_TRACKING/GEOFENCE';

    private function getFirebaseUrl(): string
    {
        return rtrim(env('FIREBASE_DATABASE_URL'), '/');
    }

    public function index()
    {
        $baseUrl = $this->getFirebaseUrl();
        $perangkatList = [];
        $activeId = session('id_perangkat', 'GPS001');

        try {
            // 1. Ambil data dari node PERANGKAT dan node GEOFENCE sekaligus
            $responsePerangkat = Http::withoutVerifying()->get("{$baseUrl}/{$this->firebasePath}.json");
            $responseGeofence  = Http::withoutVerifying()->get("{$baseUrl}/{$this->geofencePath}.json");

            $rawPerangkat = $responsePerangkat->successful() ? ($responsePerangkat->json() ?? []) : [];
            $rawGeofence  = $responseGeofence->successful() ? ($responseGeofence->json() ?? []) : [];

            if (!empty($rawPerangkat) && is_array($rawPerangkat)) {
                foreach ($rawPerangkat as $key => $value) {
                    if ($key === 'AKUN' || !is_array($value)) {
                        continue;
                    }

                    $value['id_perangkat'] = $key;

                    // Prioritaskan radius dari node GEOFENCE agar sama dengan menu Ubah Koordinat
                    if (isset($rawGeofence[$key]['radius'])) {
                        $value['radius_geofencing'] = $rawGeofence[$key]['radius'];
                    } else {
                        $value['radius_geofencing'] = $value['radius_geofencing'] ?? 100;
                    }

                    $perangkatList[] = $value;
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengambil data perangkat Firebase: " . $e->getMessage());
        }

        return view('kelola_perangkat', compact('perangkatList', 'activeId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_perangkat'      => 'required|string|alpha_dash|max:50',
            'nama_kendaraan'    => 'required|string|max:100',
            'plat_nomor'        => 'required|string|max:20',
            'radius_geofencing' => 'required|numeric|min:10',
        ]);

        $baseUrl = $this->getFirebaseUrl();
        $idPerangkat = trim($request->input('id_perangkat'));
        $urlTargetPerangkat = "{$baseUrl}/{$this->firebasePath}/{$idPerangkat}.json";
        $urlTargetGeofence  = "{$baseUrl}/{$this->geofencePath}/{$idPerangkat}.json";

        try {
            $existingData = Http::withoutVerifying()->get($urlTargetPerangkat)->json();
            $radius = (float) $request->input('radius_geofencing');

            $payloadPerangkat = [
                'nama_kendaraan'    => $request->input('nama_kendaraan'),
                'plat_nomor'        => strtoupper($request->input('plat_nomor')),
                // 'radius_geofencing' => $radius,
            ];

            if (empty($existingData)) {
                $payloadPerangkat['jarak']   = 0;
                $payloadPerangkat['lat']     = 0.0;
                $payloadPerangkat['lng']     = 0.0;
                $payloadPerangkat['relay']   = "OFF";
                $payloadPerangkat['satelit'] = 0;
                $payloadPerangkat['status']  = "MEMPROSES LOKASI";
            }

            // Simpan ke node PERANGKAT
            $response = Http::withoutVerifying()->patch($urlTargetPerangkat, $payloadPerangkat);

            // Inisialisasi/Sinkronkan ke node GEOFENCE
            Http::withoutVerifying()->patch($urlTargetGeofence, [
                'radius'     => $radius,
                'updated_at' => now()->toISOString(),
            ]);

            if ($response->successful()) {
                session(['id_perangkat' => $idPerangkat]);
                return back()->with('status', "Perangkat '{$idPerangkat}' berhasil dikonfigurasi!");
            }

            return back()->with('error', 'Gagal menyimpan data ke Firebase.');
        } catch (\Exception $e) {
            Log::error("Error Store Perangkat: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update(Request $request, string $idPerangkat)
    {
        $request->validate([
            'nama_kendaraan'    => 'required|string|max:100',
            'plat_nomor'        => 'required|string|max:20',
            'radius_geofencing' => 'required|numeric|min:10',
        ]);

        $baseUrl = $this->getFirebaseUrl();
        $urlTargetPerangkat = "{$baseUrl}/{$this->firebasePath}/{$idPerangkat}.json";
        $urlTargetGeofence  = "{$baseUrl}/{$this->geofencePath}/{$idPerangkat}.json";

        $radius = (float) $request->input('radius_geofencing');

        $payloadPerangkat = [
            'nama_kendaraan'    => $request->input('nama_kendaraan'),
            'plat_nomor'        => strtoupper($request->input('plat_nomor')),
            // 'radius_geofencing' => $radius,
        ];

        try {
            // Update node PERANGKAT
            $response = Http::withoutVerifying()->patch($urlTargetPerangkat, $payloadPerangkat);

            // Sinkronkan juga nilai radius ke node GEOFENCE
            Http::withoutVerifying()->patch($urlTargetGeofence, [
                'radius'     => $radius,
                'updated_at' => now()->toISOString(),
            ]);

            if ($response->successful()) {
                return back()->with('status', "Data perangkat '{$idPerangkat}' berhasil diperbarui!");
            }

            return back()->with('error', 'Gagal memperbarui data di Firebase.');
        } catch (\Exception $e) {
            Log::error("Error Update Perangkat: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy(string $idPerangkat)
    {
        $baseUrl = $this->getFirebaseUrl();
        $urlTarget = "{$baseUrl}/{$this->firebasePath}/{$idPerangkat}.json";

        try {
            $response = Http::withoutVerifying()->delete($urlTarget);

            if ($response->successful()) {
                if (session('id_perangkat') === $idPerangkat) {
                    session()->forget('id_perangkat');
                }
                return back()->with('status', "Perangkat '{$idPerangkat}' berhasil dihapus!");
            }

            return back()->with('error', 'Gagal menghapus perangkat dari Firebase.');
        } catch (\Exception $e) {
            Log::error("Error Delete Perangkat: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function setActive(Request $request)
    {
        $request->validate([
            'id_perangkat' => 'required|string',
        ]);

        $idPerangkat = $request->input('id_perangkat');
        session(['id_perangkat' => $idPerangkat]);

        return back()->with('status', "Perangkat aktif diubah ke '{$idPerangkat}'.");
    }
}