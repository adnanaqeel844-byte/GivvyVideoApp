<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Earning; // Added for referral bonus earning record
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'referral_code_applied' => 'nullable|string|exists:users,referral_code',
        ]);

        $referredByUserId = null;
        if ($request->has('referral_code_applied')) {
            $referrer = User::where('referral_code', $request->referral_code_applied)->first();
            if ($referrer) {
                $referredByUserId = $referrer->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referral_code' => Str::random(10), // Generate unique referral code
            'referred_by' => $referredByUserId,
        ]);

        // If referred, give bonus to referrer (and maybe referree)
        if ($referredByUserId) {
            $referrerUser = User::find($referredByUserId);
            if ($referrerUser) {
                $bonusAmount = 0.10; // Example bonus for referrer
                $referrerUser->current_balance += $bonusAmount;
                $referrerUser->save();

                $referrerUser->earnings()->create([
                    'type' => 'referral_bonus',
                    'amount' => $bonusAmount,
                    'description' => 'Bonus for referring ' . $user->name,
                ]);

                // Optionally, give a bonus to the new user who used the referral code
                $referreeBonus = 0.05; // Example bonus for the new user
                $user->current_balance += $referreeBonus;
                $user->save();
                $user->earnings()->create([
                    'type' => 'referral_bonus_received',
                    'amount' => $referreeBonus,
                    'description' => 'Bonus for using referral code from ' . $referrerUser->name,
                ]);
            }
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Handle user login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Log the user out (revoke the current token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
