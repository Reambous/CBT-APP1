<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\User\ContactController;
use Livewire\Volt\Volt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\GoogleAuthController;
// Pastikan ini ada di bagian paling atas file web.php





// 1. Halaman Utama (Sementara kita arahkan ke halaman bawaan Laravel)
Route::get('/', function () {
    return redirect()->route('login');
});
// Rute untuk menampilkan halaman Form (GET)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
// Route::get('/register', function () {
//     return view('auth.register');
// })->name('register');
Route::get('/admin/login', function () {
    return view('auth.admin_login');
})->name('admin.login');

// 2. Rute Proses Autentikasi (Menerima data dari Form)
// Route::post('/register-process', [AuthController::class, 'register'])->name('register.process');
Route::post('/login-process', [AuthController::class, 'login'])->name('login.process');
Route::post('/admin/login-process', [AuthController::class, 'adminLogin'])->name('admin.login.process');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Rute Khusus USER (Dilindungi oleh Satpam 'auth:web')
Route::middleware('auth:web')->group(function () {
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    // Rute Ujian
    Route::post('/exam/{package}/start', [\App\Http\Controllers\User\ExamController::class, 'startExam'])->name('exam.start');

    // Rute Halaman Pengerjaan Ujian
    Route::get('/exam/play/{result_id}', [\App\Http\Controllers\User\ExamController::class, 'play'])->name('exam.play');
    Route::get('/user/history/{id}/review', [UserDashboardController::class, 'review'])->name('user.review');
    Route::get('/user/exams', [UserDashboardController::class, 'exams'])->name('user.exams');
    Route::get('/user/history', [UserDashboardController::class, 'history'])->name('user.history');
    Route::get('/user/history/{id}/review', [UserDashboardController::class, 'review'])->name('user.review');
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('user.profile');
    Route::get('/user/upgrade', [UserDashboardController::class, 'upgrade'])->name('user.upgrade');
    Route::get('/contact', [ContactController::class, 'index'])->name('user.contact');
    // Rute untuk memproses klik tombol beli
    Route::post('/checkout', [UserDashboardController::class, 'checkout'])->name('user.checkout');
    // Rute untuk menampilkan halaman tagihan
    Route::get('/invoice/{id}', [UserDashboardController::class, 'invoice'])->name('user.invoice');
    Route::post('/invoice/{id}/cancel', [UserDashboardController::class, 'cancelInvoice'])->name('user.invoice.cancel');
    Route::post('/api/exam/save-answer', [\App\Http\Controllers\Api\ExamApiController::class, 'saveAnswer'])->name('api.exam.save');
    // RUTE KHUSUS LOAD TESTING k6 (HAPUS JIKA APLIKASI SUDAH RILIS ONLINE)
    // Route::get('/k6-test-ujian', function () {
    //     // 1. BEBAN BACA: Memaksa database mencari dan mengacak 50 soal
    //     // (Sesuaikan nama model 'Question' dengan model soal milikmu jika berbeda)
    //     $soal = \App\Models\Question::inRandomOrder()->limit(50)->get();

    //     // 2. BEBAN TULIS: Memaksa database menyimpan hasil pengerjaan fiktif
    //     \App\Models\UserResult::create([
    //         'user_id' => 1, // Pakai user ID sembarang yang ada di database
    //         'exam_package_id' => 1,
    //         'attempt_number' => rand(1, 10000), // Agar tidak bentrok
    //         'score' => rand(40, 100),
    //         'finished_at' => now(),
    //     ]);

    //     return response()->json(['status' => 'Simulasi Pengerjaan & Simpan Nilai Sukses!']);
    // });
});

// 4. Rute Khusus ADMIN (Dilindungi oleh Satpam 'auth:admin')
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    // Tambahkan baris ajaib ini: Route Resource untuk CRUD Kategori
    Route::resource('/admin/categories', App\Http\Controllers\Admin\ExamCategoryController::class)->names('admin.categories');
    Route::resource('/admin/packages', App\Http\Controllers\Admin\ExamPackageController::class)->names('admin.packages');
    Route::resource('/admin/questions', App\Http\Controllers\Admin\QuestionController::class)->names('admin.questions');
    // Manajemen User (Siswa/Peserta)
    Route::resource('/admin/users', App\Http\Controllers\Admin\UserController::class)->names('admin.users');

    Route::get('/admin/profile', [AdminDashboardController::class, 'profile'])->name('admin.profile');
    // Route Transactions (Ini sudah benar formatnya)
    Route::get('/admin/transactions', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'transactions'])->name('admin.transactions');

    // Route Leaderboard (Sekarang sudah pakai Controller!)
    Route::get('/admin/leaderboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'leaderboard'])->name('admin.leaderboard');
});

// ==========================================
// ⚠️ ROUTE KHUSUS TESTING K6 (HAPUS SAAT RILIS)
// ==========================================


// Route::get('/k6-bypass/{id}', function ($id) {
//     if (app()->environment('local')) {
//         // Cek apakah user dengan ID tersebut benar-benar ada
//         $user = User::find($id);

//         if (!$user) {
//             return response("Gagal: User dengan ID $id tidak ditemukan. Cek tabel users kamu.", 404);
//         }

//         Auth::login($user);
//         return response("Berhasil login sebagai: " . $user->email, 200);
//     }
//     abort(404);
// });
