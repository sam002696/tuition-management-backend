<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

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

        // Generate custom SID/TID from phone
        if ($user->phone) {
            $prefix = $user->role === 'student' ? 'S' : 'T';
            $user->custom_id = $prefix . $user->phone;
            $user->save();
        }

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


    // Password reset methods
    public function sendResetLink(Request $request): void
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email')); // generic response
    }

    // Handle the password reset
    public function resetPassword(Request $request): void
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                    ->setRememberToken(Str::random(60));
                $user->save();
                // Optional: revoke all Sanctum tokens on reset
                if (method_exists($user, 'tokens')) $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            abort(400, 'Unable to reset password.');
        }
    }


    // change password service

    public function changePassword(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // it  validates against the authenticated user
            'new_password'     => ['required', 'string', 'min:6', 'max:255', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $user = $request->user();


        // Updating password
        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        // Revoking all other tokens (keeping the one used for this request)
        if (method_exists($user, 'tokens')) {
            $current = $user->currentAccessToken();
            $user->tokens()
                ->when($current, fn($q) => $q->where('id', '!=', $current->id))
                ->delete();
        }
    }
}
