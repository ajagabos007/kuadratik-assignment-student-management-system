<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RefreshAuthTokenController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

 /*
|--------------------------------------------------------------------------
| Registiration and Authentication Routes
|--------------------------------------------------------------------------
*/

Route::controller(\App\Http\Controllers\Auth\LoginController::class)->group(function() {
    Route::post('login',  'login')->name('login');
    Route::post('logout',  'logout')->name('logout')->middleware('auth:api');
});

Route::controller(\App\Http\Controllers\Auth\RegisterController::class)->group(function() {
    Route::post('register',  'register')->name('register');
});

Route::match(['PUT','PATCH'], 'change-password', \App\Http\Controllers\Auth\ChangePasswordController::class)
->middleware('auth:api');

Route::controller(\App\Http\Controllers\Auth\ResetPasswordController::class)->group(function() {
    Route::post('forget-password',  'sendPasswordResetToken')->name('password-reset-token.send');
    Route::match(['PUT','PATCH'],'verify-password-reset-token',  'verifyPasswordResetToken')->name('password-reset-token.verify');
    Route::match(['PUT','PATCH'],'reset-password', 'resetPassword')->name('reset-password');
});

/*
|--------------------------------------------------------------------------
| Auth Users' Routes : for only logged in users
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {
        
    Route::post('refresh-auth-token', RefreshAuthTokenController::class);


    Route::post('email/verification-token', [VerificationController::class, 'sendEmailVerificationToken'])
    ->withoutMiddleware('verified')
    ->name('email.verification-token');

    Route::post('email/verify', [VerificationController::class, 'verifyEmail'])
    ->withoutMiddleware('verified')
    ->name('email.verify');

    /**
     * User routes
     */
    Route::prefix('user')->name('user.')->middleware(['filter.merge.user_id', 'input.merge.user_id'])->group(function () {
        Route::get('profile', [UserController::class, 'profile'])->name('profile.show');
        Route::post('profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::apiResource('attendances', AttendanceController::class)->only(['index','store','show', 'destroy']);
        Route::match(['PUT','PATCH'],'attendances/{attendance}/sign-out', [AttendanceController::class, 'signOut'])
        ->name('attendances.sign-out');

    });

    /**
     * Admin routes
     */
    Route::prefix('teacher')->name('teacher.')->middleware(['teacher', 'filter.merge.attendance.student'])->group(function () {
        Route::apiResource('students', StudentController::class);
        Route::apiResource('attendances', AttendanceController::class)
        ->only('index', 'show');
    });

    /**
     * Admin routes
     */
    Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate']);
        Route::post('users/{user}/reactivate', [UserController::class, 'reactivate']);
        Route::apiResource('teachers', TeacherController::class);
        Route::apiResource('students', StudentController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::match(['PUT','PATCH'],'attendances/{attendance}/sign-out', [AttendanceController::class, 'signOut'])
        ->name('attendances.sign-out');
    });

});
