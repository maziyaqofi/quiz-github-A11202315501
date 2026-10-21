
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Pasien\PoliController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;
use App\Http\Controllers\Pasien\DashboardController;
use App\Http\Controllers\Pasien\PembayaranController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\NotificationController;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::resource('polis', AdminPoliController::class);
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class)->except(['show']);

    // Admin payment management
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])
        ->name('admin.pembayaran.index');

    Route::get('/pembayaran/{id}', [AdminPembayaranController::class, 'show'])
        ->name('admin.pembayaran.show');

    Route::patch('/pembayaran/{id}/verify', [AdminPembayaranController::class, 'verify'])
        ->name('admin.pembayaran.verify');
});

// Dokter Routes
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');

    Route::resource('jadwal-periksa', JadwalPeriksaController::class);

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])->name('periksa-pasien.index');
    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])->name('periksa-pasien.store');
    Route::get('/periksa-pasien/{id}', [PeriksaPasienController::class, 'create'])->name('periksa-pasien.create');

    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])->name('riwayat-pasien.show');
    Route::post('/periksa-pasien/{id}/mulai-layani', [PeriksaPasienController::class, 'mulaiLayani'])
        ->name('periksa-pasien.mulai-layani');
});

// Pasien Routes
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('pasien.dashboard');
    
    // Provide current queue data for real-time dashboard updates
    Route::get('/dashboard/queue-status', [DashboardController::class, 'queueStatus'])
        ->name('dashboard.queue-status');

    Route::get('/daftar', [PoliController::class, 'get'])
        ->name('pasien.daftar');

    Route::post('/daftar', [PoliController::class, 'submit'])
        ->name('pasien.daftar.submit');

    Route::get('/riwayat-pendaftaran', [RiwayatPendaftaranController::class, 'index'])
        ->name('pasien.riwayat-pendaftaran.index');

    Route::get('/riwayat-pendaftaran/{id}', [RiwayatPendaftaranController::class, 'show'])
        ->name('pasien.riwayat-pendaftaran.show');

    // Patient payment proof upload
    Route::post('/pembayaran/{id}/upload', [PembayaranController::class, 'upload'])
        ->name('pasien.pembayaran.upload');
});

// Notification management
Route::middleware(['auth'])->group(function () {
    Route::patch('/notifications/{id}/read', [
        NotificationController::class,
        'markAsRead'
    ])->name('notifications.read');

    Route::patch('/notifications/read-all', [
        NotificationController::class,
        'markAllAsRead'
    ])->name('notifications.read-all');
});