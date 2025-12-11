<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validated = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role'     => 'required|in:user,admin', // validate role
    ]);

    $user = User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role'     => $validated['role'], // save role from request
    ]);

    return response()->json([
        'message' => 'Registered successfully',
        'user' => $user
    ], 201);
}


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::guard('web')->attempt($credentials)) {
    return response()->json(['message' => 'Invalid credentials'], 401);
}

        $user = User::where('email', $request->email)->first();

        // Sanctum token
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
{
    // Delete the current token if it exists
    $request->user()?->currentAccessToken()?->delete();

    // Return success always
    return response()->json(['message' => 'Logged out successfully']);
}

}
