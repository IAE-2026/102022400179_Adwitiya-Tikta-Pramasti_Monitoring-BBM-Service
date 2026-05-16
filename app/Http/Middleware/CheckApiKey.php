<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-IAE-KEY');

        if ($key !== env('IAE_API_KEY')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. X-IAE-KEY header tidak valid.',
                'errors' => null
            ], 401);
        }

        return $next($request);
    }
}