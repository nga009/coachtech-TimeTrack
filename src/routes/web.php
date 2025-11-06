<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// 管理者用ログイン画面
Route::middleware(['web','guest'])->group(function () {
    Route::view('/admin/login', 'auth.login', [
        'title' => '管理者ログイン',
        'loginType' => 'admin',
    ])->name('admin.login');
});

// 一般ユーザー用（ログイン後の遷移先）
Route::middleware(['auth', 'role:user', 'verified'])->group(function () {
    // 勤怠登録
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break-start');
    Route::post('/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break-end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'monthly'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/{id}/request', [RequestController::class, 'requestEdit'])->name('attendance.request');

    Route::get('/', function () {
        return auth()->check()
            ? redirect('/attendance')   // ログイン済みの一般ユーザー想定
            : redirect('/login');       // 未ログインはログイン画面へ
    });

});

// 管理者用（ログイン後の遷移先）
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/attendance/list', [AttendanceController::class, 'daily'])->name('admin.attendance.daily');

    // 勤怠詳細
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/attendance/{id}', [AttendanceController::class, 'update'])->name('admin.attendance.update');

    // 申請
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [RequestController::class, 'approveRequest'])->name('admin.request.approve');

    // スタッフ一覧
    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/attendance/staff/{id}', [StaffController::class, 'monthly'])->name('admin.staff.monthly');
    Route::post('/attendance/staff/{id}/export', [StaffController::class, 'export'])->name('admin.staff.monthly.export');
});

Route::middleware(['auth'])->group(function () {
    // 申請一覧
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('request.index');
    // 申請詳細
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [RequestController::class, 'requestShow'])->name('request.show');
});




