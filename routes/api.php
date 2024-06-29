<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    EmailVerificationController,
    ResetPasswordController,
};
use App\Http\Controllers\User\{
    GoogleController,
};


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('google/register', [GoogleController::class, 'sign_up'])->name('google_register');

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/code/validation', [EmailVerificationController::class, 'verification_code_validation'])->name('code_validation');
    Route::get('/resend/verification/code', [EmailVerificationController::class, 'resend_verification_code'])->name('resend_code');
    Route::get('/send/resetting/code', [ResetPasswordController::class, 'send_code'])->name('send_resetting_code');
   
});