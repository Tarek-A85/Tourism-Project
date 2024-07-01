<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use Carbon\Carbon;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('sanctum:prune-expired')->everyTwoMinutes();
//Schedule::command('otp:clean')->everySecond();

Schedule::call(function () {
    User::where('email_verified_at', null)->where('created_at', '<=', now()->subHour(1))->forceDelete();
})->everySecond();
