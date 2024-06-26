<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    EmailVerificationController,
    ResetPasswordController,
    AuthenticatedController,
    GoogleController,
    ForgotPasswordController,
};





Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('google/signup', [GoogleController::class, 'sign_up'])->name('google_sign_up');
Route::post('google/signin', [GoogleController::class, 'sign_in'])->name('google_sign_in');
Route::post('/singup', [AuthenticatedController::class,'singup']);
Route::post('/login', [AuthenticatedController::class,'login']);

Route::middleware('check_email')->group(function(){
    Route::post('/send/forgotten/password/code', [ForgotPasswordController::class, 'send_forgotten_password_code'])->name('send_forgotten_password_code')->middleware('throttle:forgot_password_code');
    Route::post('/validate/forgotten/password/code', [ForgotPasswordController::class, 'validate_forgotten_password_code'])->name('validate_forgotten_password_code');
    Route::post('/change/forgotten/password', [ForgotPasswordController::class, 'change_forgotting_password'])->name('change_forgotting_password');
});


Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/code/validation', [EmailVerificationController::class, 'verification_code_validation'])->name('code_validation');
    Route::get('/resend/verification/code', [EmailVerificationController::class, 'resend_verification_code'])->name('resend_code')->middleware('throttle:resend_verification_code');
    
     Route::middleware('verified')->group(function(){
        
        Route::middleware('can_change_password')->group(function(){
            Route::get('/send/resetting/code', [ResetPasswordController::class, 'send_code'])->name('send_resetting_code')->middleware('throttle:resetting_code');
            Route::post('/validate/resetting/code', [ResetPasswordController::class, 'validate_code'])->name('validate_resetting_code');
            Route::post('/reset/password', [ResetPasswordController::class, 'reset_password'])->name('reset_password');
        });
      
        Route::delete('/logout',[AuthenticatedController::class,'logout']);
     });
   
});






