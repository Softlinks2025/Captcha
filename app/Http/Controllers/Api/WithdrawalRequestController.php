<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // User: Create withdrawal request
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'upi_id' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            'bank_name' => 'required|string',
        ]);

        // Get min withdrawal from user's subscription plan
        $minWithdrawal = 1;
        if ($user->subscriptionPlan && $user->subscriptionPlan->min_withdrawal_limit) {
            $minWithdrawal = $user->subscriptionPlan->min_withdrawal_limit;
        }
        if ($user->wallet_balance < $request->amount) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient wallet balance.'], 400);
        }
        if ($request->amount < $minWithdrawal) {
            return response()->json(['status' => 'error', 'message' => 'Amount below minimum withdrawal limit (minimum: ' . $minWithdrawal . ').'], 400);
        }

        $fee = 0; // Set fee logic if needed
        $finalAmount = $request->amount - $fee;

        $withdrawal = WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'fee' => $fee,
            'final_withdrawal_amount' => $finalAmount,
            'upi_id' => $request->upi_id,
            'status' => 'pending',
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'bank_name' => $request->bank_name,
            'request_date' => now(),
        ]);

        return response()->json([
            'id' => $withdrawal->id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'phone_number' => $user->phone,
            'amount' => $withdrawal->amount,
            'fee' => $withdrawal->fee,
            'final_withdrawal_amount' => $withdrawal->final_withdrawal_amount,
            'upi_id' => $withdrawal->upi_id,
            'account_number' => $withdrawal->account_number,
            'ifsc_code' => $withdrawal->ifsc_code,
            'bank_name' => $withdrawal->bank_name,
            'status' => $withdrawal->status,
            'request_date' => $withdrawal->request_date,
            'wallet_balance' => $user->wallet_balance,
            'total_earning' => $user->wallet_balance, // Or another field if you track total earning
        ], 201);
    }

    // Admin: List all withdrawal requests
    public function index()
    {
        $this->authorizeAdmin();
        $requests = WithdrawalRequest::with('user')->orderByDesc('request_date')->paginate(20);
        return response()->json($requests);
    }

    // Admin: Approve withdrawal request
    public function approve($id, Request $request)
    {
        $this->authorizeAdmin();
        $withdrawal = WithdrawalRequest::with('user')->findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Request already processed.'], 400);
        }
        $user = $withdrawal->user;
        if ($user->wallet_balance < $withdrawal->amount) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient wallet balance.'], 400);
        }
        DB::transaction(function () use ($withdrawal, $user) {
            $user->wallet_balance -= $withdrawal->amount;
            $user->save();
            $withdrawal->status = 'approved';
            $withdrawal->approved_at = now();
            $withdrawal->admin_id = Auth::id();
            $withdrawal->save();
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => -$withdrawal->amount,
                'type' => 'withdrawal',
                'description' => 'Withdrawal approved',
            ]);
        });
        return response()->json(['status' => 'success', 'message' => 'Withdrawal approved.', 'wallet_balance' => $user->wallet_balance]);
    }

    // Admin: Decline withdrawal request
    public function decline($id, Request $request)
    {
        $this->authorizeAdmin();
        $withdrawal = WithdrawalRequest::findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Request already processed.'], 400);
        }
        $withdrawal->status = 'declined';
        $withdrawal->approved_at = now();
        $withdrawal->admin_id = Auth::id();
        $withdrawal->remarks = $request->remarks;
        $withdrawal->save();
        return response()->json(['status' => 'success', 'message' => 'Withdrawal declined.']);
    }

    // User: Withdrawal history
    public function history()
    {
        $user = Auth::user();
        $withdrawals = \App\Models\WithdrawalRequest::where('user_id', $user->id)
            ->orderByDesc('request_date')
            ->get();
        return response()->json($withdrawals);
    }

    private function authorizeAdmin()
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Forbidden');
        }
    }
}