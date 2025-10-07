<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        $user = Auth::user();
        // You might want to eager load relationships like 'referrer' if needed
        return response()->json($user);
    }

    /**
     * Get the authenticated user's earning history.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEarnings()
    {
        $user = Auth::user();
        $earnings = $user->earnings()->orderBy('created_at', 'desc')->get();
        return response()->json($earnings);
    }

    /**
     * Submit a withdrawal request for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01', // Example minimum withdrawal amount
            'method' => 'required|string|in:PayPal,Coinbase,Amazon,Payeer', // Allowed withdrawal methods
            'account_details' => 'required|string|max:255', // e.g., PayPal email, crypto address
        ]);

        $user = Auth::user();
        $minimumWithdrawal = 0.01; // Define your app's minimum withdrawal here

        if ($request->amount < $minimumWithdrawal) {
            throw ValidationException::withMessages([
                'amount' => ["Minimum withdrawal amount is $minimumWithdrawal USD."],
            ]);
        }

        if ($user->current_balance < $request->amount) {
            throw ValidationException::withMessages([
                'amount' => ['Insufficient balance. Your current balance is $' . $user->current_balance],
            ]);
        }

        // Deduct balance immediately upon request
        // Alternatively, you could deduct only after admin approval for security/fraud prevention
        $user->current_balance -= $request->amount;
        $user->save();

        $withdrawal = $user->withdrawals()->create([
            'amount' => $request->amount,
            'method' => $request->method,
            'account_details' => $request->account_details,
            'status' => 'pending', // Admin will review and approve/reject this
        ]);

        return response()->json([
            'message' => 'Withdrawal request submitted successfully. It will be reviewed shortly.',
            'withdrawal' => $withdrawal,
            'new_balance' => $user->current_balance
        ], 201);
    }

    /**
     * Get the authenticated user's withdrawal history.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithdrawalHistory()
    {
        $user = Auth::user();
        $withdrawals = $user->withdrawals()->orderBy('created_at', 'desc')->get();
        return response()->json($withdrawals);
    }
}
