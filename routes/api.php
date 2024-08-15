<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    EmailVerificationController,
    ResetPasswordController,
    AuthenticatedController,
    GoogleController,
    ForgotPasswordController,
};
use App\Http\Controllers\Both\{
    AirportController,
    CompanyController,
    FlightController,
    FlightTransactionController,
    RegionController,
    HotelController,
    PackageController,
    ReviewController,
    PackageTypeController,
    TripController,
    TrackingController,
    HotelTransactionController,
    TransactionController,
};
use App\Http\Controllers\User\{
    FavoriteController,
    PackageTransactionController,
    WalletController
};


Route::post('google/signup', [GoogleController::class, 'sign_up'])->name('google_sign_up');
Route::post('google/signin', [GoogleController::class, 'sign_in'])->name('google_sign_in');
Route::post('/singup', [AuthenticatedController::class, 'singup']);
Route::post('/login', [AuthenticatedController::class, 'login']);

Route::middleware('check_email')->group(function () {
    Route::post('/send/forgotten/password/code', [ForgotPasswordController::class, 'send_forgotten_password_code'])->name('send_forgotten_password_code')->middleware('throttle:forgot_password_code');
    Route::post('/validate/forgotten/password/code', [ForgotPasswordController::class, 'validate_forgotten_password_code'])->name('validate_forgotten_password_code');
    Route::post('/change/forgotten/password', [ForgotPasswordController::class, 'change_forgotting_password'])->name('change_forgotting_password');
});


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/code/validation', [EmailVerificationController::class, 'verification_code_validation'])->name('code_validation');
    Route::get('/resend/verification/code', [EmailVerificationController::class, 'resend_verification_code'])->name('resend_code')->middleware('throttle:resend_verification_code');




    Route::middleware('verified')->group(function () {

        Route::middleware('is_user')->group(function () {
            Route::apiResource('favorite', FavoriteController::class)->except(['edite', 'create', 'destroy']);
            Route::delete('favorite', [FavoriteController::class, 'destroy']);
            Route::post('favorite/add/item', [FavoriteController::class, 'add_to_list']);
            Route::post('favorite/remvoe/item', [FavoriteController::class, 'remove_from_list']);
            Route::post('add/review', [ReviewController::class, 'store_review']);
            Route::post('add/rating', [ReviewController::class, 'store_rating']);
            Route::put('update/reviews/{review}', [ReviewController::class, 'update_review']);
            Route::put('update/rating/{review}', [ReviewController::class, 'update_rating']);

            Route::middleware('has_wallet')->group(function () {
                Route::post('check/availablity', [HotelTransactionController::class, 'check_availablity']);
                Route::post('add/hotel/transaction', [HotelTransactionController::class, 'store']);
                Route::put('update/hotel/transaction/{id}', [HotelTransactionController::class, 'update']);
                Route::post('delete/hotel/transaction/{id}', [HotelTransactionController::class, 'destroy']);
                Route::get('all/transactions', [TransactionController::class, 'index']);
                Route::get('transactions/{id}', [TransactionController::class, 'show']);

                Route::post('/package/transaction', [PackageTransactionController::class, 'store']);
                Route::put('/package/transaction/{packageTransaction}', [PackageTransactionController::class, 'update']);
                Route::delete('/package/transaction/{packageTransaction}', [PackageTransactionController::class, 'destroy']);
                Route::get('/package/transaction/{packageTransaction}',[PackageTransactionController::class,'show']);

                Route::post('add/flight/transaction', [FlightTransactionController::class, 'store']);
                Route::post('available/updates/for/flight/transaction/{id}', [FlightTransactionController::class, 'available_flights_to_update']);
                Route::post('update/flight/transaction/{id}', [FlightTransactionController::class, 'update']);
                Route::post('delete/flight/transaction/{id}', [FlightTransactionController::class, 'destroy']);
            });

            Route::prefix('/wallet')->group(function () {
                Route::middleware('has_wallet')->group(function () {
                    Route::get('/', [WalletController::class, 'show']);
                    Route::get('/send/resetting/code', [ResetPasswordController::class, 'send_code'])->name('send_resetting_code')->middleware('throttle:resetting_code');
                    Route::post('/validate/resetting/code', [ResetPasswordController::class, 'validate_code'])->name('validate_resetting_code');
                    Route::post('/reset/password', [WalletController::class, 'restePassword'])->name('reset_wallet_password');
                });
                Route::post('/create', [WalletController::class, 'create'])->name('create_wallet');
            });
        });

        Route::apiResource('companies', CompanyController::class)->only(['index', 'show']);
        Route::post('flights/search', [FlightController::class, 'search']);
        Route::post('reviews/{id}', [ReviewController::class, 'index']);
        Route::get('can/review/{id}', [ReviewController::class, 'can_review_package']);
        Route::get('reviews/{review}', [ReviewController::class, 'show']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy_review']);
        Route::delete('rating/{review}', [ReviewController::class, 'destroy_rating']);
        Route::get('search/hotels', [HotelController::class, 'search']);
        Route::get('show/hotel/transaction/{id}', [HotelTransactionController::class, 'show']);
        Route::get('show/flight/transaction/{id}', [FlightTransactionController::class, 'show']);
        Route::get('airport', [AirportController::class, 'index']);




        Route::middleware('can_change_password')->group(function () {

            Route::get('/send/resetting/code', [ResetPasswordController::class, 'send_code'])->name('send_resetting_code')->middleware('throttle:resetting_code');
            Route::post('/validate/resetting/code', [ResetPasswordController::class, 'validate_code'])->name('validate_resetting_code');
            Route::post('/reset/password', [ResetPasswordController::class, 'reset_password'])->name('reset_password');
        });

        Route::prefix('admin')->middleware('check_admin')->group(function () {
            Route::delete('regions/archive/{region}', [RegionController::class, 'archive']);
            Route::get('regions/archived', [RegionController::class, 'index_archived']);
            Route::get('regions/show/archived/{id}', [RegionController::class, 'show_archived']);
            Route::get('regions/restore/archived/{id}', [RegionController::class, 'restore_archived']);
            Route::get('regions/cities', [RegionController::class, 'cities']);
            Route::apiResource('regions', RegionController::class)->only(['store', 'update', 'destroy']);
            Route::delete('hotels/archive/{hotel}', [HotelController::class, 'archive']);
            Route::get('hotels/archived', [HotelController::class, 'index_archived']);
            Route::get('hotels/restore/archived/{id}', [HotelController::class, 'restore_archived']);
            Route::apiResource('hotels', HotelController::class)->only(['store', 'update', 'destroy']);

            Route::apiResource('package', PackageController::class)->only(['store', 'update', 'destroy']);
            Route::delete('package/archive/{package}', [PackageController::class, 'archive']);
            Route::get('package/archived', [PackageController::class, 'index_archived']);
            Route::get('package/restore/archived/{id}', [PackageController::class, 'restore_archived']);
            Route::apiResource('package/trip', TripController::class)->only(['store', 'update', 'destroy']);

            Route::get('companies/restore/archived/{id}', [CompanyController::class, 'restore']);
            Route::get('companies/archived', [CompanyController::class, 'index_archived']);
            Route::delete('companies/archive/{company}', [CompanyController::class, 'archive']);
            Route::apiResource('companies', CompanyController::class)->only(['store', 'update', 'destroy']);

            Route::post('trip/tracking/{trip}', [TrackingController::class, 'update']);

            Route::post('add/to/wallet', [WalletController::class, 'add_balance_to_wallet']);
        });

        Route::delete('/logout', [AuthenticatedController::class, 'logout']);
        Route::apiResource('regions', RegionController::class)->only(['index', 'show']);
        Route::apiResource('hotels', HotelController::class)->only(['index', 'show']);
        Route::apiResource('package', PackageController::class)->only(['index', 'show']);
        Route::get('packageTypes', PackageTypeController::class);
        Route::get('package/trip/{package}', [TripController::class, 'index']);
        //check transaction
        Route::get('trip/tracking/{trip}', [TrackingController::class, 'tracking']);
    });
});
