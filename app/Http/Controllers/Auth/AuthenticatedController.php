<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        try {
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
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 401);
            }

            $user = User::create($request->all());

            $user->notify(new SendCodeNotification('Please verify your email using the code below'));

            return response()->json([
                'status' => true,
                'message' => 'user registered successfully',
                'data' => ['token' => $user->createToken('userToken')->plainTextToken]
            ]);
        } catch (Exception $E) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => null
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required', Password::min(8)],
                'is_admin' => ['required', 'boolean']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 401);
            };

            if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return response()->json([
                    'status' => true,
                    'message' => 'email and password does not match',
                    'data' => null
                ], 401);
            }

            $user = User::where('email', $request->email)
                ->where('is_admin', $request->is_admin)->first();

            if (($request->is_admin && !$user->is_admin) ||
                (!$request->is_admin && $user->is_admin)) 
            {
                return response()->json([
                    "status" => false,
                    "message" => "you are not authorized",
                    "data" => null
                ], 401);
            }

            if ($user->email_verified_at == null) {
                $user->notify(new SendCodeNotification('Please verify your email using the code below'));

                return response()->json([
                    "status" => true,
                    "message" => "Please verify your email, code is sent to you",
                    "data" => ["token" => $user->createToken('verify_user_token')->plainTextToken],
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'logged in successfully',
                'data' => ['token' => $user->createToken('userToken')->plainTextToken]
            ]);

        } catch (Exception $E) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => null
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {

            $user = auth()->user();
            $user->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'message' => 'logged out successfully',
                'data' => null
            ]);
        } catch (Exception $E) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => null
            ]);
        }
    }
}
