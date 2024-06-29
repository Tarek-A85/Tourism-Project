<?php

use App\Http\Controllers\Auth\AuthenticatedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/singup',[AuthenticatedController::class,'singup']);
Route::post('/login',[AuthenticatedController::class,'login']);

Route::middleware('auth:sanctum')->group(function (){

    Route::delete('/logout',[AuthenticatedController::class,'logout']);

});
