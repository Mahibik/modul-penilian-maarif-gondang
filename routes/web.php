<?php

use App\Http\Controllers\Admin\GuruController as AdminGuruController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\KomponenController;
use App\Http\Controllers\Admin\MapelController as AdminMapelController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\Superadmin\AkunController;
use App\Http\Controllers\Superadmin\SekolahController;
use App\Http\Controllers\Superadmin\SiswaController;
use App\Http\Controllers\Superadmin\YayasanController;
use App\Http\Middleware\EnsureAktif;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth', EnsureAktif::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Guru, Admin Sekolah, Superadmin: lihat kelas & gradebook (akses dibatasi Policy)
    Route::middleware(EnsureRole::class . ':guru,admin_sekolah,superadmin')->group(function () {
        Route::get('/kelas', [GradebookController::class, 'kelasIndex'])->name('kelas.index');
        Route::get('/kelas/{kelas}', [GradebookController::class, 'kelasShow'])->name('kelas.show');
        Route::get('/gradebook/{kelasMapel}', [GradebookController::class, 'show'])->name('gradebook.show');
        Route::get('/gradebook/{kelasMapel}/cetak', [GradebookController::class, 'cetak'])->name('gradebook.cetak');
    });

    // Hanya wali kelas yang dapat menyimpan nilai
    Route::put('/gradebook/{kelasMapel}', [GradebookController::class, 'store'])
        ->middleware(EnsureRole::class . ':guru')->name('gradebook.store');

    // Admin Sekolah: komponen & bobot
    Route::middleware(EnsureRole::class . ':admin_sekolah')->prefix('admin')->group(function () {
        Route::get('/komponen', [KomponenController::class, 'index'])->name('komponen.index');
        Route::get('/komponen/{kelasMapel}', [KomponenController::class, 'edit'])->name('komponen.edit');
        Route::put('/komponen/{kelasMapel}', [KomponenController::class, 'update'])->name('komponen.update');
    });

    // Admin Sekolah: kelola guru, siswa, kelas, mata pelajaran (khusus sekolahnya sendiri)
    Route::middleware(EnsureRole::class . ':admin_sekolah')->prefix('admin')->name('adm.')->group(function () {
        Route::get('guru', [AdminGuruController::class, 'index'])->name('guru.index');
        Route::get('guru/baru', [AdminGuruController::class, 'create'])->name('guru.create');
        Route::post('guru', [AdminGuruController::class, 'store'])->name('guru.store');
        Route::get('guru/{user}/ubah', [AdminGuruController::class, 'edit'])->name('guru.edit');
        Route::put('guru/{user}', [AdminGuruController::class, 'update'])->name('guru.update');
        Route::patch('guru/{user}/status', [AdminGuruController::class, 'status'])->name('guru.status');

        Route::get('siswa', [AdminSiswaController::class, 'index'])->name('siswa.index');
        Route::get('siswa/baru', [AdminSiswaController::class, 'create'])->name('siswa.create');
        Route::post('siswa', [AdminSiswaController::class, 'store'])->name('siswa.store');
        Route::get('siswa/{siswa}/ubah', [AdminSiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('siswa/{siswa}', [AdminSiswaController::class, 'update'])->name('siswa.update');

        Route::get('kelas', [AdminKelasController::class, 'index'])->name('kelas.index');
        Route::get('kelas/baru', [AdminKelasController::class, 'create'])->name('kelas.create');
        Route::post('kelas', [AdminKelasController::class, 'store'])->name('kelas.store');
        Route::get('kelas/{kelas}/ubah', [AdminKelasController::class, 'edit'])->name('kelas.edit');
        Route::put('kelas/{kelas}', [AdminKelasController::class, 'update'])->name('kelas.update');

        Route::get('mapel', [AdminMapelController::class, 'index'])->name('mapel.index');
        Route::get('mapel/baru', [AdminMapelController::class, 'create'])->name('mapel.create');
        Route::post('mapel', [AdminMapelController::class, 'store'])->name('mapel.store');
        Route::get('mapel/{mapel}/ubah', [AdminMapelController::class, 'edit'])->name('mapel.edit');
        Route::put('mapel/{mapel}', [AdminMapelController::class, 'update'])->name('mapel.update');
    });

    // Superadmin: kelola data
    Route::middleware(EnsureRole::class . ':superadmin')->prefix('kelola')->name('sa.')->group(function () {
        Route::get('yayasan', [YayasanController::class, 'edit'])->name('yayasan.edit');
        Route::put('yayasan', [YayasanController::class, 'update'])->name('yayasan.update');

        Route::get('sekolah', [SekolahController::class, 'index'])->name('sekolah.index');
        Route::get('sekolah/baru', [SekolahController::class, 'create'])->name('sekolah.create');
        Route::post('sekolah', [SekolahController::class, 'store'])->name('sekolah.store');
        Route::get('sekolah/{school}/ubah', [SekolahController::class, 'edit'])->name('sekolah.edit');
        Route::put('sekolah/{school}', [SekolahController::class, 'update'])->name('sekolah.update');

        // Admin Sekolah & Guru memakai controller yang sama; peran dibawa lewat default 'role'
        foreach ([['admin_sekolah', 'admin-sekolah', 'admin'], ['guru', 'guru', 'guru']] as [$role, $uri, $nama]) {
            Route::get($uri, [AkunController::class, 'index'])->defaults('role', $role)->name("$nama.index");
            Route::get("$uri/baru", [AkunController::class, 'create'])->defaults('role', $role)->name("$nama.create");
            Route::post($uri, [AkunController::class, 'store'])->defaults('role', $role)->name("$nama.store");
            Route::get("$uri/{user}/ubah", [AkunController::class, 'edit'])->defaults('role', $role)->name("$nama.edit");
            Route::put("$uri/{user}", [AkunController::class, 'update'])->defaults('role', $role)->name("$nama.update");
            Route::patch("$uri/{user}/status", [AkunController::class, 'status'])->defaults('role', $role)->name("$nama.status");
        }

        Route::get('siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('siswa/baru', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('siswa/{siswa}/ubah', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    });
});
