<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\AgentWalletTransaction;

class WalletController extends Controller
{
    /**
     * Get agent wallet balance and transaction history
     */
    public function transactions(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent not authenticated.'
            ], 401);
        }

        $transactions = $agent->walletTransactions()->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'status' => 'success',
            'wallet_balance' => $agent->wallet_balance,
            'transactions' => $transactions
        ]);
    }

    // POST /api/v1/agent/wallet/add-balance (testing only)
    public function addBalance(Request $request)
    {
        if (!app()->environment(['local', 'testing'])) {
            return response()->json(['status' => 'error', 'message' => 'Not allowed in production'], 403);
        }
        $agent = Auth::guard('agent')->user();
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
        $agent->wallet_balance += $request->amount;
        $agent->save();
        $transaction = \App\Models\AgentWalletTransaction::create([
            'agent_id' => $agent->id,
            'amount' => $request->amount,
            'type' => 'credit',
            'description' => 'Test top-up',
        ]);
        return response()->json([
            'status' => 'success',
            'wallet_balance' => $agent->wallet_balance,
            'transaction' => $transaction,
        ]);
    }
} 