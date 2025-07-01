<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RoleSelectionRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Mail\VerificationMail;
use App\Models\User;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => null,
            'status' => 'active',
            'is_approved' => false,
        ]);


        $code = rand(100000, 999999);

        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => 'email',
            'expires_at' => Carbon::now()->addMinutes(10),
            'is_used' => false,
        ]);


        Mail::to($user->email)->send(new VerificationMail($code));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'token' => $token,
            'next_step' => 'verify_email',
            'expires_at' => Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s'),
        ], 201);
    }





    public function verifyEmail(VerifyOtpRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 400);
        }

        $verification->is_used = true;
        $verification->save();

        $user->email_verified_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $user
        ]);
    }



    public function selectRole(RoleSelectionRequest $request)
    {
        $user = auth()->user();

        if ($user->role !== null) {
            return response()->json([
                'message' => 'Role has already been selected.'
            ], 400);
        }

        $user->update([
            'role' => $request->role
        ]);

        return response()->json([
            'message' => 'Role selected successfully.',
            'role' => $user->role,
            'next_step' => 'complete_' . $user->role . '_profile'
        ]);
    }



    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if ($user && Hash::check($validated['password'], $user->password)) {
            if ($user->email_verified_at === null) {
                return response()->json([
                    'message' => 'Please verify your email first.'
                ], 400);
            }

            $token = $user->createToken('YourAppName')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}
