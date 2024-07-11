<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Otp', Ichtrojan\Otp\Otp::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('forgot_password_code', function (Request $request) {
             return Limit::perMinutes(15,1)->by($request->user()?->id ?: $request->ip());//->response(function(){
            //     return response()->json([
            //         "status" => false,
            //         "message" => "Too many attempts, please try after 15 minutes",
            //         "data" => null,
            //     ]);
            // });
        });

        RateLimiter::for('resend_verification_code', function (Request $request) {
             return Limit::perMinutes(15,1)->by($request->user()?->id ?: $request->ip());//->response(function(){
            //     return response()->json([
            //         "status" => false,
            //         "message" => "Too many attempts, please try after 15 minutes",
            //         "data" => null,
            //     ]);
            // });
        });

        RateLimiter::for('resetting_code', function (Request $request) {
             return Limit::perMinutes(15,1)->by($request->user()?->id ?: $request->ip());
       });
    }
}
