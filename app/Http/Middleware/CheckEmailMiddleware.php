<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
class CheckEmailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->email){
            return response()->json([
                "status" => false,
                "message" => "Email is required",
                "data" => null,
            ]);
        }
        $user = User::where('email', $request->email)->first();

        if(!$user){
            return response()->json([
                "status" => false,
                "message" => "There is no user with this email",
                "data" => null,
            ]);
        }

        if($user->password == null){
            return response()->json([
                "status" => false,
                "message" => "This is a google email, you can't change the password",
                "data" => null,
            ]);
        }
        return $next($request);
    }
}
