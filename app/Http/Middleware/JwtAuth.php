<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class JwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Missing token'], 401);
        }

        $decoded = json_decode(base64_decode($token), true);

        if (!$decoded || !isset($decoded['sub']) || $decoded['exp'] < time()) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $user = User::find($decoded['sub']);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        // Attach user to request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
