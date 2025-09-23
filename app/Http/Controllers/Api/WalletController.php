<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // GET /api/v1/wallet
    public function show(Request $request)
    {
        $user = Auth::user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return response()->json([
            'wallet_balance' => $user->wallet_balance,
            'transactions' => $transactions,
        ]);
    }

    // POST /api/v1/wallet/by-user (admin only, user_id in body)
    public function showByUserId(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);
        $user = \App\Models\User::find($request->user_id);
        $transactions = $user->walletTransactions()->orderByDesc('created_at')->limit(50)->get();
        return response()->json([
            'wallet_balance' => $user->wallet_balance,
            'transactions' => $transactions,
        ]);
    }

    // POST /api/v1/wallet/add-balance (testing only)
    public function addBalance(Request $request)
    {
        if (!app()->environment(['local', 'testing'])) {
            return response()->json(['status' => 'error', 'message' => 'Not allowed in production'], 403);
        }
        $user = Auth::user();
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        $user->wallet_balance += $request->amount;
        $user->save();
        $transaction = \App\Models\WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'credit',
            'description' => 'Test top-up',
        ]);
        return response()->json([
            'status' => 'success',
            'wallet_balance' => $user->wallet_balance,
            'transaction' => $transaction,
        ]);
    }
} 