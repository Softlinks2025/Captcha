<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AgentWithdrawalRequest;
use App\Models\AgentWalletTransaction;

class WithdrawalController extends Controller
{
    /**
     * Agent creates a withdrawal request
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'upi_id' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            'bank_name' => 'required|string',
        ]);

        $agent = $request->user();
        $amount = $request->amount;
        $fee = 0; // Calculate fee if needed
        $finalAmount = $amount - $fee;

        // Check if agent has sufficient balance
        if ($agent->wallet_balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
                'wallet_balance' => $agent->wallet_balance
            ], 400);
        }

        try {
            $withdrawalRequest = AgentWithdrawalRequest::create([
                'agent_id' => $agent->id,
                'amount' => $amount,
                'fee' => $fee,
                'final_withdrawal_amount' => $finalAmount,
                'upi_id' => $request->upi_id,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
                'bank_name' => $request->bank_name,
                'status' => 'pending',
                'request_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully.',
                'data' => $withdrawalRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agent views their withdrawal requests
     */
    public function index(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        $requests = $agent->withdrawalRequests()->orderByDesc('created_at')->paginate(20);
        return response()->json([
            'status' => 'success',
            'withdrawal_requests' => $requests
        ]);
    }
} 