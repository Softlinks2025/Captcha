<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AgentWithdrawalRequest;
use App\Models\AgentWalletTransaction;
use App\Models\Agent;

class AgentWithdrawalRequestController extends Controller
{
    // Approve agent withdrawal request
    public function approve($id, Request $request)
    {
        $admin = Auth::user();
        $withdrawal = AgentWithdrawalRequest::with('agent')->findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Request already processed.');
        }
        $agent = $withdrawal->agent;
        if ($agent->wallet_balance < $withdrawal->amount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }
        DB::transaction(function () use ($withdrawal, $agent, $admin) {
            $agent->wallet_balance -= $withdrawal->amount;
            $agent->save();
            $withdrawal->status = 'approved';
            $withdrawal->approved_at = now();
            $withdrawal->admin_id = $admin->id;
            $withdrawal->save();
            \App\Models\AgentWalletTransaction::create([
                'agent_id' => $agent->id,
                'amount' => $withdrawal->amount,
                'type' => 'debit',
                'description' => 'Withdrawal approved',
            ]);
        });
        // Notify agent
        if ($agent) {
            $agent->notify(new \App\Notifications\AgentWithdrawalRequestStatusNotification('approved', $withdrawal));
        }
        return redirect()->back()->with('success', 'Agent withdrawal request approved and agent notified.');
    }

    // Decline agent withdrawal request
    public function decline($id, Request $request)
    {
        $admin = Auth::user();
        $withdrawal = AgentWithdrawalRequest::with('agent')->findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Request already processed.');
        }
        $withdrawal->status = 'declined';
        $withdrawal->approved_at = now();
        $withdrawal->admin_id = $admin->id;
        $withdrawal->remarks = $request->remarks;
        $withdrawal->save();
        // Notify agent
        if ($withdrawal->agent) {
            $withdrawal->agent->notify(new \App\Notifications\AgentWithdrawalRequestStatusNotification('declined', $withdrawal));
        }
        return redirect()->back()->with('success', 'Agent withdrawal request declined and agent notified.');
    }
} 