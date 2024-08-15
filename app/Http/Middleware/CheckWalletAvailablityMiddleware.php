<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWalletAvailablityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->user()->wallet){
            
            return response()->json([
                'status' => 'false',
                'message' => 'Please create a wallet to continue',
                'data' => null,
            ]);
        }
        return $next($request);
    }
}
