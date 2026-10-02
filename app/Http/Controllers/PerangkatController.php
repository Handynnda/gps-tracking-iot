<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerangkatController extends Controller
{
    private string $firebasePath = 'GPS_TRACKING/PERANGKAT';

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
            $response = Http::withoutVerifying()->get("{$baseUrl}/{$this->firebasePath}.json");

            if ($response->successful() && !empty($response->json())) {
                $raw = $response->json();
                foreach ($raw as $key => $value) {
                    if ($key === 'AKUN' || !is_array($value)) {
                        continue;
                    }
                    $value['id_perangkat'] = $key;
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
        $urlTarget = "{$baseUrl}/{$this->firebasePath}/{$idPerangkat}.json";

        try {
            $existingData = Http::withoutVerifying()->get($urlTarget)->json();

            $payload = [
                'nama_kendaraan'    => $request->input('nama_kendaraan'),
                'plat_nomor'        => strtoupper($request->input('plat_nomor')),
                'radius_geofencing' => (float) $request->input('radius_geofencing'),
            ];

            if (empty($existingData)) {
                $payload['jarak']   = 0;
                $payload['lat']     = 0.0;
                $payload['lng']     = 0.0;
                $payload['relay']   = "OFF";
                $payload['satelit'] = 0;
                $payload['status']  = "MEMPROSES LOKASI";
            }

            $response = Http::withoutVerifying()->patch($urlTarget, $payload);

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

    // Tambahkan string $idPerangkat di bawah ini:
    public function update(Request $request, string $idPerangkat)
    {
        $request->validate([
            'nama_kendaraan'    => 'required|string|max:100',
            'plat_nomor'        => 'required|string|max:20',
            'radius_geofencing' => 'required|numeric|min:10',
        ]);

        $baseUrl = $this->getFirebaseUrl();
        $urlTarget = "{$baseUrl}/{$this->firebasePath}/{$idPerangkat}.json";

        $payload = [
            'nama_kendaraan'    => $request->input('nama_kendaraan'),
            'plat_nomor'        => strtoupper($request->input('plat_nomor')),
            'radius_geofencing' => (float) $request->input('radius_geofencing'),
        ];

        try {
            $response = Http::withoutVerifying()->patch($urlTarget, $payload);

            if ($response->successful()) {
                return back()->with('status', "Data perangkat '{$idPerangkat}' berhasil diperbarui!");
            }

            return back()->with('error', 'Gagal memperbarui data di Firebase.');
        } catch (\Exception $e) {
            Log::error("Error Update Perangkat: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    // Tambahkan string $idPerangkat di bawah ini:
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