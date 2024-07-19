<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use  App\Http\Middleware\CanChangePasswordMiddleware;
use  App\Http\Middleware\CheckEmailMiddleware;
use  App\Http\Middleware\CheckAdminMiddleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            'check_admin' => CheckAdminMiddleware::class,
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

        $exceptions->render(function (NotFoundHttpException $e, Request $request){
            if ($request->wantsJson()) {
                return response()->json([
                    "status" => false,
                    "message" => "There is no object like that",
                    "data" => null,
                ]);
            }
            });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request){
            if ($request->wantsJson()) {
                return response()->json([
                    "status" => false,
                    "message" => "Too many attempts, please try after 15 minutes",
                    "data" => null,
                ]);
            }
            });


        $exceptions->render(function (Exception $e, Request $request){
            if ($request->wantsJson()) {
                return response()->json([
                    "status" => false,
                    "message" => "Something went wrong",
                    "data" => null,
                ]);
            }
            });

  




    })->create();
