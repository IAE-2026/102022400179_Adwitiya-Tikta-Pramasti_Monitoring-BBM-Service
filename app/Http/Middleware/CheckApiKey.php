<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-IAE-KEY');
        
        // Gunakan nilai dari env, jika kosong default ke NIM Mahasiswa
        $expectedKey = env('IAE_API_KEY') ?: '102022400179';

        // Jika API Key tidak dikirimkan atau tidak cocok dengan expectedKey
        if (!$apiKey || $apiKey !== $expectedKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. X-IAE-KEY tidak valid.',
                'errors' => null,
            ], 401);
        }

        return $next($request);
    }
}