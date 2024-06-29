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

class AuthenticatedController extends Controller
{
    public function singup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:50','regex:/^[a-zA-Z ]+$/'],
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'password' => ['required', 'confirmed', Password::min(8)],
                'phone_number' => [Rule::unique('users', 'phone_number'),'digits_between:10,15'],
                'birth_date' => ['date'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first() ,
                    'data' => null
                ], 401);
            }

            $user = User::create($request->all());

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
                ]);
            }

            $user = User::where('email', $request->email)->first();

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
        try{

            $user = auth()->user();
            $user->tokens()->delete();
            return response()->json([
                'status' => true,
                'message' => 'logged out successfully',
                'data' => null
            ]);

        }catch(Exception $E){

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => null
            ]);
        }
        
    }
}
