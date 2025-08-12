<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\DataAnggotaController;
use App\Http\Controllers\BanpersController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PkbController; // NEW: PKB Controller
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckSmartEscalationAccess;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);

    Route::post('/', [AuthController::class, 'login']);

    // SSO Login Routes

    // SSO Routes - Updated untuk true SSO
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/sso/popup/{token}', [AuthController::class, 'showSSOPopup'])->name('sso.popup');
    Route::post('/sso/auth', [AuthController::class, 'processSSOAuth'])->name('sso.auth');

    // SSO Register Routes

    // Manual registration (optional)
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // API Routes for Register
    Route::post('/api/karyawan-data', [AuthController::class, 'getKaryawanData'])->name('api.karyawan-data');

    // Password Reset Routes (for non-authenticated users)
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/reset', [PasswordResetController::class, 'showRequestForm'])->name('request');
        Route::post('/email', [PasswordResetController::class, 'sendResetLink'])->name('email');
        Route::get('/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('reset');
        Route::post('/reset', [PasswordResetController::class, 'resetPassword'])->name('update');
        Route::get('/success', [PasswordResetController::class, 'showSuccessPage'])->name('success');
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes (accessible from user dropdown)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update-email', [ProfileController::class, 'updateEmail'])->name('profile.update-email');
    Route::post('/profile/update-iuran', [ProfileController::class, 'updateIuranSukarela'])->name('profile.update-iuran');
    Route::delete('/profile/cancel-iuran', [ProfileController::class, 'cancelIuranChange'])->name('profile.cancel-iuran');
    Route::get('/profile/history', [ProfileController::class, 'getIuranHistory'])->name('profile.history'); // Iuran change history
    Route::get('/profile/payment-history', [ProfileController::class, 'getPaymentHistory'])->name('profile.payment-history'); // NEW: Payment history
    Route::post('/profile/resign', [ProfileController::class, 'resign'])->name('profile.resign');

    // Profile Picture Routes
    Route::post('/profile/update-picture', [ProfileController::class, 'updateProfilePicture'])->name('profile.update-picture');
    Route::delete('/profile/delete-picture', [ProfileController::class, 'deleteProfilePicture'])->name('profile.delete-picture');

    // Password Change Routes (for authenticated users)
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/change', [PasswordResetController::class, 'showChangeForm'])->name('change');
        Route::post('/change', [PasswordResetController::class, 'changePassword'])->name('change.update');
    });

    // Data Anggota Routes - Admin only
    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('/data-anggota', [DataAnggotaController::class, 'index'])->name('data-anggota.index');
        Route::get('/data-anggota/export', [DataAnggotaController::class, 'export'])->name('data-anggota.export');

        // CRUD routes for Super Admin (permission checked in controller)
        Route::get('/data-anggota/create', [DataAnggotaController::class, 'create'])->name('data-anggota.create');
        Route::post('/data-anggota', [DataAnggotaController::class, 'store'])->name('data-anggota.store');
        Route::get('/data-anggota/{nik}/edit', [DataAnggotaController::class, 'edit'])->name('data-anggota.edit');
        Route::put('/data-anggota/{nik}', [DataAnggotaController::class, 'update'])->name('data-anggota.update');
        //Route::delete('/data-anggota/{nik}', [DataAnggotaController::class, 'destroy'])->name('data-anggota.destroy');
    });

    // Advokasi & Aspirasi Routes (Enhanced with Smart Escalation)
    Route::prefix('advokasi-aspirasi')->name('konsultasi.')->group(function () {
        // Basic konsultasi routes - accessible by all authenticated users
        Route::get('/', [KonsultasiController::class, 'index'])->name('index');
        Route::get('/create', [KonsultasiController::class, 'create'])->name('create');
        Route::post('/', [KonsultasiController::class, 'store'])->name('store');
        Route::get('/{id}', [KonsultasiController::class, 'show'])->name('show');

        // Comment routes - accessible by konsultasi owner and admins
        Route::post('/{id}/comment', [KonsultasiController::class, 'comment'])->name('comment');

        // Admin only routes with smart escalation validation
        Route::middleware([CheckAdmin::class])->group(function () {
            // Close konsultasi - admin only
            Route::post('/{id}/close', [KonsultasiController::class, 'close'])->name('close');
        });

        // Smart escalation route - requires both admin access AND smart escalation validation
        Route::middleware([CheckAdmin::class, CheckSmartEscalationAccess::class])->group(function () {
            Route::post('/{id}/escalate', [KonsultasiController::class, 'escalate'])->name('escalate');
        });
    });

        // Banpers Routes
        Route::middleware(['check.admin'])->group(function () {
            Route::get('/banpers', [BanpersController::class, 'index'])->name('banpers.index');
            Route::get('/banpers/export', [BanpersController::class, 'export'])->name('banpers.export');

            // Super Admin only routes for editing banpers
            Route::get('/banpers/edit', [BanpersController::class, 'edit'])->name('banpers.edit');
            Route::put('/banpers/update', [BanpersController::class, 'update'])->name('banpers.update');
        });
    // Banpers Routes - Admin only
    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('/banpers', [BanpersController::class, 'index'])->name('banpers.index');
        Route::get('/banpers/export', [BanpersController::class, 'export'])->name('banpers.export');

        // Super Admin only routes for editing banpers (validation in controller)
        Route::get('/banpers/edit', [BanpersController::class, 'edit'])->name('banpers.edit');
        Route::put('/banpers/update', [BanpersController::class, 'update'])->name('banpers.update');
    });

        // Sertifikat Routes (accessible by all authenticated users)
        Route::get('/sertifikat', [SertifikatController::class, 'show'])->name('sertifikat.show');
        Route::get('/sertifikat/download', [SertifikatController::class, 'download'])->name('sertifikat.download');

        // NEW: PKB SEKAR Routes (accessible by all authenticated users)
        Route::get('/pkb-sekar', [PkbController::class, 'show'])->name('pkb.show');
        Route::get('/pkb-sekar/download', [PkbController::class, 'download'])->name('pkb.download');
    // Sertifikat Routes - accessible by all authenticated users
    Route::get('/sertifikat', [SertifikatController::class, 'show'])->name('sertifikat.show');
    Route::get('/sertifikat/download', [SertifikatController::class, 'download'])->name('sertifikat.download');

    // Setting Routes - Admin only
    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
        Route::post('/setting/pkb', [SettingController::class, 'updatePkbOnly'])->name('setting.pkb.update');
    });

    // Notification routes - accessible by all authenticated users
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

    // Additional API-style routes for AJAX calls - Admin only
    Route::middleware([CheckAdmin::class])->prefix('api')->name('api.')->group(function () {
        // Future API endpoints for admin functions
        // Route::get('/wilayah/dpd/{dpw}', [ApiController::class, 'getDPDByDPW'])->name('dpd.by.dpw');
        // Route::get('/escalation/options/{konsultasi}', [ApiController::class, 'getEscalationOptions'])->name('escalation.options');
    });
});