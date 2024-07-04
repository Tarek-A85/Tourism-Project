<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use  App\Http\Middleware\CanChangePasswordMiddleware;
use  App\Http\Middleware\CheckEmailMiddleware;
use App\Http\Middleware\UserMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'can_change_password' => CanChangePasswordMiddleware::class,
            'check_email' => CheckEmailMiddleware::class,
            'is_user' => UserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (AuthenticationException $e, Request $request){
            if ($request->wantsJson()) {
                return response()->json([
                    "status" => false,
                    "message" => "You are un authenticated",
                    "data" => null,
                ]);
            }
            });


               




    })->create();
