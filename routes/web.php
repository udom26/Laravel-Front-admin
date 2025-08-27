<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Middleware\CheckAdmin;

// หน้าแรก → ไปหน้า login
Route::get('/', fn () => redirect()->route('login'));

// Login/Logout
Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// กลุ่ม admin (ต้องผ่าน CheckAdmin)
Route::prefix('admin')->middleware(CheckAdmin::class)->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // ===== Jobs (เชื่อม API ผ่าน Controller) =====
    // List ทั้งหมด -> GET /crud
    Route::get('/jobs', [JobController::class, 'index'])->name('admin.jobs.index');

    // รายการเดียว -> GET /crud/:id
    Route::get('/jobs/{id}', [JobController::class, 'show'])->name('admin.jobs.show');

    // ฟอร์มแก้ไข -> GET /crud/:id
    Route::get('/jobs/{id}/edit', [JobController::class, 'edit'])->name('admin.jobs.edit');

    // สร้างงาน -> POST /jobs/ingest
    Route::post('/jobs', [JobController::class, 'store'])->name('admin.jobs.store');

    // อัปเดตงาน -> PATCH /crud/:id
    Route::patch('/jobs/{id}', [JobController::class, 'update'])->name('admin.jobs.update');

    // ลบ -> DELETE /crud/:id
    Route::delete('/jobs/{id}', [JobController::class, 'destroy'])->name('admin.jobs.destroy');

    // Retry (ตัวช่วย) -> PATCH /crud/:id (status = pending)
    Route::patch('/jobs/{id}/retry', [JobController::class, 'retry'])->name('admin.jobs.retry');
});
