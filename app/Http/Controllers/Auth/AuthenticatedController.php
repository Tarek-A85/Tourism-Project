<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendingCodeJob;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Notifications\SendCodeNotification;

class AuthenticatedController extends Controller
{
    public function singup(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50', 'regex:/^[a-zA-Z ]+$/'],
            'email' => [
                'required', 'email',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('is_admin', false))
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone_number' => [
                'digits_between:10,15',
                Rule::unique('users', 'phone_number')
                    ->where(fn ($query) => $query->where('is_admin', false))
            ],
            'birth_date' => ['date'],
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $user = User::create($request->all());
        $user->notify(new SendCodeNotification('Please verify your email using the code below'));

        return $this->success(
            'user registered successfully',
            ['token' => $user->createToken('userToken')->plainTextToken]
        );

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', Password::min(8)],
            'is_admin' => ['required', 'boolean']
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if (!Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => $request->is_admin
        ])) {
            return $this->fail('email and password does not match');
        }

        $user = User::where('email', $request->email)
            ->where('is_admin', $request->is_admin)->first();


        if (($request->is_admin && !$user->is_admin) || (!$request->is_admin && $user->is_admin)) {
            return $this->fail("you are not authorized");
        }

        if ($user->email_verified_at == null) {
            $user->notify(new SendCodeNotification('Please verify your email using the code below'));

            return $this->success(
                "Please verify your email, code is sent to you",
                ['token' => $user->createToken('userToken')->plainTextToken]
            );
        }

        return $this->success(
            'logged in successfully',
            ['token' => $user->createToken('userToken')->plainTextToken]
        );
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();
        return $this->success('logged out successfully');
    }
}