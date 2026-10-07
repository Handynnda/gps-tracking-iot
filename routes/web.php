<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PerangkatController;
use Illuminate\Http\Request;
use App\Http\Middleware\NoCache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\NotificationController;
use App\Services\NotificationService;
use App\Http\Controllers\GpsDataController;
use Illuminate\Support\Facades\Http;

// Route::post('/api/notifikasi/respon', function (Request $request) {
//     $id = $request->input('id_perangkat', 'GPS001');
//     Http::put("https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/GPS_TRACKING/{$id}/CONTROL/respon_admin.json", true);
//     return back()->with('success', 'Notifikasi ditandai sudah ditangani. Cut-off dibatalkan.');
// })->name('notif.respon');

Route::post('/api/notifikasi/respon', function (Request $request) {
    $id = $request->input('id_perangkat', 'GPS001');
    
    // Update respon_admin jadi true DAN paksa relay jadi ON agar mesin hidup kembali
    Http::withoutVerifying()->patch("https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/GPS_TRACKING/PERANGKAT/{$id}.json", [
        'relay' => 'ON',
        'updated_at' => now()->toIso8601String(),
    ]);

    Http::withoutVerifying()->put("https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/GPS_TRACKING/{$id}/CONTROL/respon_admin.json", true);

    return back()->with('success', 'Peringatan ditandai sudah ditangani dan mesin diaktifkan kembali.');
})->name('notif.respon');

Route::post('/api/notifikasi/cutoff-manual', function (Request $request) {
    $id = $request->input('id_perangkat', 'GPS001');
    Http::put("https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/GPS_TRACKING/{$id}/CONTROL/cutoff_manual.json", true);
    return back()->with('success', 'Perintah cut-off manual terkirim ke kendaraan.');
})->name('notif.cutoffManual');

// Rute untuk Cut-off Mesin (Ubah relay jadi OFF)
Route::post('/api/relay/off', function (Request $request) {
    $id = $request->input('id_perangkat', 'GPS001');
    Http::withoutVerifying()->patch("https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/GPS_TRACKING/PERANGKAT/{$id}.json", [
        'relay' => 'OFF',
        'updated_at' => now()->toIso8601String(),
    ]);
    return back()->with('success', 'Perintah Cut-off berhasil! Relay dimatikan.');
})->name('relay.off');

// Rute untuk Nyalakan Kembali Mesin (Ubah relay jadi ON)
Route::post('/api/relay/on', function (Request $request) {
    $id = $request->input('id_perangkat', 'GPS001');
    Http::withoutVerifying()->patch("https://gps-tracking-6ba6f-default-rtdb.asia-southeast1.firebasedatabase.app/GPS_TRACKING/PERANGKAT/{$id}.json", [
        'relay' => 'ON',
        'updated_at' => now()->toIso8601String(),
    ]);
    return back()->with('success', 'Mesin berhasil dinyalakan kembali! Relay dihidupkan.');
})->name('relay.on');

Route::post('/gps/update', [GpsDataController::class, 'store']);

Route::post('/api/notifikasi/pelanggaran', function (Request $request) {
    $idPerangkat = $request->input('id_perangkat', 'GPS001');
    $lat = $request->input('lat');
    $lng = $request->input('lng');
    $pesan = "Pelanggaran Zona Geofencing! {$idPerangkat} terdeteksi keluar dari radius lokasi aman.";

    NotificationService::send(
        idPerangkat: $idPerangkat,
        pesanNotifikasi: $pesan,
        tipeNotifikasi: 'danger',
        userEmail: 'handynandaf@gmail.com',
        lat: (float) $lat,
        lng: (float) $lng
    );

    return response()->json(['status' => 'ok']);
});


Route::get('/test-notif', function () {
    $idPerangkat = 'GPS001'; // Ganti dengan ID perangkat yang sesuai
    $pesan = "Pelanggaran Zona Geofencing! {$idPerangkat} terdeteksi keluar dari radius lokasi aman.";

    NotificationService::send(
        idPerangkat: $idPerangkat,
        pesanNotifikasi: $pesan,
        tipeNotifikasi: 'danger',
        userEmail: 'handynandaf@gmail.com', // ganti dengan email kamu
        lat: -6.9772,
        lng: 108.4831
    );

    return 'Notifikasi berhasil dikirim!';
});

Route::middleware([NoCache::class])->group(function () {
    
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/dashboard', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        
        $user = session('user_data');
        
        // Ambil ID Perangkat dari session aktif atau dari data user, fallback ke 'GPS001'
        $idPerangkat = session('id_perangkat') ?? ($user['id_perangkat'] ?? 'GPS001');

        return view('dashboard', compact('user', 'idPerangkat'));
    })->name('dashboard');

    Route::get('/notifikasi', function () {
        $idPerangkat = session('id_perangkat', 'perangkat_1'); 
        $notifications = [];

        return view('notifikasi', [
            'notifications' => $notifications ?? [],
            'idPerangkat'   => $idPerangkat,
        ]);
    })->name('notifikasi.index');

    // --- RUTE KELOLA PERANGKAT ---
    Route::get('/kelola-perangkat', [PerangkatController::class, 'index'])->name('perangkat.index');
    Route::post('/kelola-perangkat', [PerangkatController::class, 'store'])->name('perangkat.store');
    Route::put('/kelola-perangkat/{idPerangkat}', [PerangkatController::class, 'update'])->name('perangkat.update');
    Route::delete('/kelola-perangkat/{idPerangkat}', [PerangkatController::class, 'destroy'])->name('perangkat.destroy');
    Route::post('/kelola-perangkat/pilih', [PerangkatController::class, 'setActive'])->name('perangkat.setActive');

    Route::get('/history', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        return response()
            ->view('history')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    })->name('history.index');

    Route::get('/lokasi-aman', function () {
        if (!session()->has('is_logged_in')) {
            return redirect()->route('login');
        }
        return view('lokasi-aman');
    })->name('lokasi.aman');

    Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

    Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

    // Rute untuk menampilkan halaman form pembuatan password baru (DARI LINK EMAIL)
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

    // Route untuk aksi notifikasi
    Route::get('/notifikasi/{idPerangkat?}', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{idPerangkat}/read/{pushId}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifikasi/{idPerangkat}/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::delete('/notifikasi/{idPerangkat}/delete/{pushId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');    

    // Rute untuk memproses/menyimpan password baru ke database
    Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->middleware('guest')
        ->name('password.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('gps', GpsDataController::class);

    // --- MANAJEMEN USER (REGISTER, UPDATE, DELETE) ---
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::put('/users/{id}', [RegisteredUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [RegisteredUserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';