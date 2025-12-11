<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    // Update profile info and handle photo upload/removal
    public function update(Request $request)
    {
        $user = $request->user();

        // Validate input
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|max:255|unique:users,email," . $user->id,
            "profile_photo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048"
        ]);

        // Update name and email
        $user->name = $request->name;
        $user->email = $request->email;

        // Handle profile photo removal
        if ($request->has('remove_image') && $request->remove_image) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');

            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Generate unique filename
            $filename = 'profile_' . $user->id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Store in 'public/profile_photos'
            $path = $file->storeAs('profile_photos', $filename, 'public');

            // Save path to user
            $user->profile_photo_path = $path;
        }

        $user->save();

        // Generate full URL for frontend (always points to Laravel backend)
        $profileUrl = $user->profile_photo_path ? url(Storage::url($user->profile_photo_path)) : null;

        return response()->json([
            "success" => true,
            "message" => "Profile updated",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
                "profile_photo_url" => $profileUrl,
            ]
        ]);
    }

    // Fetch logged-in user info
    public function fetchUser(Request $request)
    {
        $user = $request->user();
        $profileUrl = $user->profile_photo_path ? url(Storage::url($user->profile_photo_path)) : null;

        return response()->json([
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "role" => $user->role,
            "profile_photo_url" => $profileUrl,
        ]);
    }
}
