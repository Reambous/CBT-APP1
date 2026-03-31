<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;

// 1. Halaman Utama (Sementara kita arahkan ke halaman bawaan Laravel)
Route::get('/', function () {
    return view('welcome');
});
// Rute untuk menampilkan halaman Form (GET)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::get('/register', function () {
    return view('auth.register');
})->name('register');
Route::get('/admin/login', function () {
    return view('auth.admin_login');
})->name('admin.login');

// 2. Rute Proses Autentikasi (Menerima data dari Form)
Route::post('/register-process', [AuthController::class, 'register'])->name('register.process');
Route::post('/login-process', [AuthController::class, 'login'])->name('login.process');
Route::post('/admin/login-process', [AuthController::class, 'adminLogin'])->name('admin.login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Rute Khusus USER (Dilindungi oleh Satpam 'auth:web')
Route::middleware('auth:web')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
});

// 4. Rute Khusus ADMIN (Dilindungi oleh Satpam 'auth:admin')
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    // Tambahkan baris ajaib ini: Route Resource untuk CRUD Kategori
    Route::resource('/admin/categories', App\Http\Controllers\Admin\ExamCategoryController::class)->names('admin.categories');
    Route::resource('/admin/packages', App\Http\Controllers\Admin\ExamPackageController::class)->names('admin.packages');
    Route::resource('/admin/questions', App\Http\Controllers\Admin\QuestionController::class)->names('admin.questions');
});
