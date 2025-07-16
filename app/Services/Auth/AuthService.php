<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AuthService
{
    /**
     * Registering a new user.
     */
    public function registerUser(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['teacher', 'student'])],
        ]);

        // Creating user
        // Hashing the password before storing it
        // in the database
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return $user;
    }

    /**
     * Authenticating user and generating sanctum token.
     */
    public function loginUser(Request $request)
    {
        // Validate incoming request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Attempting to authenticate user
        if (!Auth::attempt($credentials)) {
            return null;
        }


        $user = User::where('email', $request->email)->firstOrFail();
        // Generating sanctum token
        $token = $user->createToken('auth_token', ['*'], Carbon::now()->addDays(5))->plainTextToken;

        // Returning user and token
        return ['user' => $user, 'token' => $token];
    }
}
